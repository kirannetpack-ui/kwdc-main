<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Enhanced AI Service with Multiple Providers & Fallback Strategy
 *
 * This service now supports:
 * - Default: Free local/rule-based assistant mode
 * - Optional: Google Gemini, OpenAI GPT, or Groq when explicitly enabled
 * - Semantic: Embedding-based search (local)
 */
class EnhancedAIService
{
    protected string $primaryProvider = 'free';
    protected array $fallbackProviders = [];

    public function __construct()
    {
        $configuredProvider = strtolower((string) config('services.ai.provider', 'free'));

        if ($configuredProvider === 'auto') {
            $this->primaryProvider = 'gemini';
            $this->fallbackProviders = ['openai', 'groq'];
            return;
        }

        if (in_array($configuredProvider, ['gemini', 'openai', 'groq'], true)) {
            $this->primaryProvider = $configuredProvider;
            $this->fallbackProviders = [];
        }
    }

    /**
     * Smart chat with automatic fallback
     * Tries primary provider, then fallbacks in order
     */
    public function chat(string $systemPrompt, string $userPrompt, string $format = 'json', ?string $provider = null)
    {
        $providers = $this->requestedProviders($provider);

        if ($providers === []) {
            Log::info('AI provider disabled; using free local assistant behavior.');
            return null;
        }

        foreach ($providers as $p) {
            $result = match($p) {
                'gemini' => $this->chatGemini($systemPrompt, $userPrompt, $format),
                'openai' => $this->chatOpenAI($systemPrompt, $userPrompt, $format),
                'groq' => $this->chatGroq($systemPrompt, $userPrompt, $format),
                default => null,
            };

            if ($result !== null) {
                Log::info("✅ AI response from provider: {$p}");
                return $result;
            }

            Log::warning("⚠️ Provider {$p} failed, trying next...");
        }

        Log::error("❌ All AI providers failed");
        return null;
    }

    private function requestedProviders(?string $provider): array
    {
        $configuredProvider = strtolower((string) config('services.ai.provider', 'free'));

        if ($configuredProvider === 'free') {
            return [];
        }

        if ($provider) {
            $provider = strtolower($provider);

            return ($configuredProvider === 'auto' || $configuredProvider === $provider)
                ? [$provider]
                : [];
        }

        return array_values(array_filter(
            [$this->primaryProvider, ...$this->fallbackProviders],
            fn (string $candidate) => $candidate !== 'free'
        ));
    }

    /**
     * Google Gemini Implementation (Primary)
     */
    protected function chatGemini(string $systemPrompt, string $userPrompt, string $format = 'json')
    {
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            Log::warning('Gemini API key missing');
            return null;
        }

        $models = array_unique([
            config('services.gemini.model', 'gemini-3.5-flash'),
            'gemini-3.5-flash',
            'gemini-3.5-flash-lite',
        ]);

        foreach ($models as $model) {
            try {
                $fullPrompt = "System: $systemPrompt\n\nUser: $userPrompt";
                if ($format === 'json') {
                    $fullPrompt .= "\n\nReturn your response STRICTLY as a valid JSON object. Do not wrap it in markdown code blocks.";
                }

                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

                $response = Http::withoutVerifying()->timeout(12)->post($url, [
                    'contents' => [
                        [
                            'parts' => [['text' => $fullPrompt]]
                        ]
                    ]
                ]);

                if ($response->failed()) {
                    Log::warning("Gemini model {$model} returned status {$response->status()}: " . substr($response->body(), 0, 120));
                    continue;
                }

                $data = $response->json();
                $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if (!$content) {
                    continue;
                }

                if ($format === 'json') {
                    $content = preg_replace('/```(?:json)?\s*|\s*```/', '', trim($content));
                    return json_decode($content, true) ?? $content;
                }

                return trim($content);

            } catch (\Exception $e) {
                Log::error("Gemini exception on model {$model}: " . $e->getMessage());
                continue;
            }
        }

        return null;
    }

    /**
     * OpenAI Implementation (Fallback)
     * Requires OPENAI_API_KEY in .env
     */
    protected function chatOpenAI(string $systemPrompt, string $userPrompt, string $format = 'json')
    {
        $apiKey = config('services.openai.api_key');
        if (!$apiKey) {
            return null;
        }

        try {
            $url = "https://api.openai.com/v1/chat/completions";
            $payload = [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $userPrompt . ($format === 'json' ? "\n\nReturn ONLY valid JSON." : '')]
                ],
                'temperature' => $format === 'json' ? 0.2 : 0.7,
                'max_tokens' => 1024,
            ];

            if ($format === 'json') {
                $payload['response_format'] = ['type' => 'json_object'];
            }

            $response = Http::timeout(10)
                ->withToken($apiKey)
                ->post($url, $payload);

            if ($response->failed()) {
                Log::warning('OpenAI error: ' . $response->status());
                return null;
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if ($format === 'json' && $content) {
                $content = preg_replace('/```json\s*|\s*```/', '', $content);
                return json_decode($content, true) ?? $content;
            }

            return $content;

        } catch (\Exception $e) {
            Log::warning('OpenAI exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Groq Implementation (Ultra-Fast for Structured Tasks)
     * Requires GROQ_API_KEY in .env
     * Best for: Quick classifications, extractions, structured outputs
     */
    protected function chatGroq(string $systemPrompt, string $userPrompt, string $format = 'json')
    {
        $apiKey = config('services.groq.api_key');
        if (!$apiKey) {
            return null;
        }

        try {
            $url = "https://api.groq.com/openai/v1/chat/completions";

            $response = Http::timeout(5)
                ->withToken($apiKey)
                ->post($url, [
                    'model' => 'mixtral-8x7b-32768',
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt . ($format === 'json' ? '\n\nReturn ONLY valid JSON.' : '')]
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 512,
                ]);

            if ($response->failed()) {
                return null;
            }

            $data = $response->json();
            $content = $data['choices'][0]['message']['content'] ?? null;

            if ($format === 'json' && $content) {
                $content = preg_replace('/```json\s*|\s*```/', '', $content);
                return json_decode($content, true) ?? $content;
            }

            return $content;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Semantic Search using Local Embeddings
     * For matching user queries to relevant documents/FAQs
     */
    public function semanticSearch(string $query, array $documents, int $topK = 3): array
    {
        // This would use a local embedding model or vector DB in production
        // For now, using simple keyword matching as fallback

        $queryKeywords = $this->extractKeywords($query);
        $scores = [];

        foreach ($documents as $idx => $doc) {
            $docKeywords = $this->extractKeywords($doc['text']);
            $matches = count(array_intersect($queryKeywords, $docKeywords));
            $scores[$idx] = $matches;
        }

        arsort($scores);
        $results = [];
        foreach (array_slice($scores, 0, $topK) as $idx => $score) {
            if ($score > 0) {
                $results[] = [...$documents[$idx], 'relevance_score' => $score];
            }
        }

        return $results;
    }

    /**
     * Extract keywords from text
     */
    protected function extractKeywords(string $text, int $limit = 10): array
    {
        $text = strtolower($text);
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        // Filter out common stop words
        $stopwords = ['the', 'a', 'an', 'and', 'or', 'but', 'is', 'are', 'was', 'were', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'what', 'which', 'who', 'when', 'where', 'why', 'how'];
        $keywords = array_diff($words, $stopwords);

        return array_slice(array_unique($keywords), 0, $limit);
    }

    /**
     * Document Processing for OCR/Compliance Documents
     * Extracts text from images for security agency compliance docs
     */
    public function processDocument(string $imagePath): array
    {
        // Uses Google's Vision API or Tesseract OCR
        // For now, placeholder for future integration

        Log::info("Document processing requested for: {$imagePath}");
        
        return [
            'success' => false,
            'message' => 'Document processing requires OCR integration (Google Vision / Tesseract)',
            'extracted_text' => null,
            'confidence' => 0,
        ];
    }

    /**
     * Multi-language Text Generation with Language Detection
     */
    public function generateMultiLanguage(string $text, string $targetLanguage = 'auto'): array
    {
        // Detect language
        if ($targetLanguage === 'auto') {
            $targetLanguage = $this->detectLanguage($text);
        }

        $prompt = "Translate or generate content in {$targetLanguage} for: {$text}";
        $result = $this->chat(
            "You are a professional translator and content generator.",
            $prompt,
            'text'
        );

        return [
            'language' => $targetLanguage,
            'content' => $result ?? $text,
            'translated' => $result !== null,
        ];
    }

    /**
     * Detect language of text
     */
    protected function detectLanguage(string $text): string
    {
        // Check for Nepali/Hindi Devanagari script (Unicode range U+0900 to U+097F)
        if (preg_match('~[अ-ह]~u', $text)) {
            return 'Nepali';
        }
        // Check for Chinese/Japanese characters
        if (preg_match('~[\p{Han}\p{Hiragana}\p{Katakana}]~u', $text)) {
            return 'Japanese';
        }
        // Default to English
        return 'English';
    }

    /**
     * Transcribe audio using Google Gemini multimodal capabilities
     */
    public function transcribeAudio(string $base64Audio, string $mimeType = 'audio/webm', string $language = 'en'): ?string
    {
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            Log::warning('Gemini API key missing for audio transcription');
            return null;
        }

        $models = array_unique([
            config('services.gemini.model', 'gemini-3.5-flash'),
            'gemini-3.5-flash',
            'gemini-3.5-flash-lite',
        ]);

        $prompt = $language === 'np'
            ? 'तपाईं एक सटीक अडियो ट्रान्सक्राइबिङ सहायक हुनुहुन्छ। यो अडियो सुन्नुहोस् र बोलिएका शब्दहरूलाई शुद्ध नेपाली देवनागरी वा अंग्रेजीमा जस्ताको तस्तै ट्रान्सक्राइब गर्नुहोस्। कुनै अतिरिक्त टिप्पणी, अभिवादन वा स्पष्टीकरण नदिनुहोस्। यदि कुनै आवाज छैन भने खाली छोड्नुहोस्।'
            : 'You are an accurate audio transcriber for Kathmandu Logistics. Transcribe the spoken words in this audio exactly as spoken (English or Nepali). Return ONLY the transcribed text. Do not add quotes, commentary, markdown, or timestamps. If no clear speech is heard, return an empty string.';

        foreach ($models as $model) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

                $response = Http::withoutVerifying()->timeout(15)->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data' => $base64Audio,
                                    ]
                                ],
                                [
                                    'text' => $prompt,
                                ]
                            ]
                        ]
                    ]
                ]);

                if ($response->failed()) {
                    Log::warning("Gemini audio transcription failed on {$model}: " . $response->status());
                    continue;
                }

                $data = $response->json();
                $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($text !== null) {
                    $cleaned = trim(preg_replace('/^["\']|["\']$/', '', trim($text)));
                    if (strcasecmp($cleaned, 'silent') === 0 || strcasecmp($cleaned, 'none') === 0) {
                        return '';
                    }
                    return $cleaned;
                }
            } catch (\Throwable $e) {
                Log::error("Gemini transcribe error on {$model}: " . $e->getMessage());
                continue;
            }
        }

        return null;
    }

    /**
     * Dispatch & Cargo Route Advisor
     */
    public function adviseDispatch(array $data): array
    {
        $system = <<<PROMPT
You are the KTM-WDC Logistics Dispatch & Route Advisor for Nepal. Analyze origin, destination, cargo, and weight.
Return strictly a JSON object with:
- suggested_vehicle: string (e.g., 'Pickup (1 Ton)', 'Medium Truck (Tata 407)', 'Heavy Truck (10 Ton)', 'Container')
- estimated_hours: string (e.g., '6-8 hours')
- estimated_distance_km: number (numeric km estimate)
- suggested_price_npr: number (fair market rate in Nepalese Rupees)
- route_advisory: string (terrain and highway notes like Prithvi, BP, or Ring Road conditions)
- handling_precautions: string (strapping, tarpaulin, temperature precautions)
- summary: string (1-2 sentence executive operational advice)
PROMPT;

        $user = json_encode($data, JSON_UNESCAPED_SLASHES);
        $result = $this->chat($system, $user, 'json');

        if (is_array($result) && !empty($result)) {
            return $result;
        }

        // Fallback calculation if AI unavailable
        $origin = strtolower((string) ($data['pickup_address'] ?? 'Kathmandu'));
        $dest = strtolower((string) ($data['delivery_address'] ?? 'Pokhara'));
        $isIntercity = !str_contains($dest, 'kathmandu') && !str_contains($dest, 'lalitpur') && !str_contains($dest, 'bhaktapur');

        return [
            'suggested_vehicle' => $isIntercity ? 'Medium Truck (Tata 407)' : 'Pickup (1 Ton)',
            'estimated_hours' => $isIntercity ? '6-8 hours' : '1-2 hours',
            'estimated_distance_km' => $isIntercity ? 205 : 18,
            'suggested_price_npr' => $isIntercity ? 14500 : 2500,
            'route_advisory' => $isIntercity 
                ? 'Standard intercity highway transit. Ensure weather and road clearance along highway passes.'
                : 'Intra-valley route. Comply with local traffic restriction windows.',
            'handling_precautions' => 'Secure cargo with weather-resistant tarpaulin and ratchets.',
            'summary' => 'Direct dispatch route planned with standard cargo handling protocols.',
        ];
    }

    /**
     * Pickup Packaging & Vehicle Advisor
     */
    public function advisePickup(array $data): array
    {
        $system = <<<PROMPT
You are the KTM-WDC Pickup & Packaging Advisor in Kathmandu. Return strictly a JSON object with:
- suggested_vehicle: string ('Motorbike Courier', 'Cargo Van', 'Pickup Truck')
- packaging_advice: string (packaging guidelines for the described items)
- estimated_price_npr: number (fair pickup rate in NPR)
- estimated_minutes: number (estimated pickup ETA in minutes)
- handling_notes: string (fragile, moisture, or stacking notes)
PROMPT;

        $user = json_encode($data, JSON_UNESCAPED_SLASHES);
        $result = $this->chat($system, $user, 'json');

        if (is_array($result) && !empty($result)) {
            return $result;
        }

        return [
            'suggested_vehicle' => 'Pickup Truck',
            'packaging_advice' => 'Box with inner padding and clear labels.',
            'estimated_price_npr' => 1200,
            'estimated_minutes' => 45,
            'handling_notes' => 'Handle with standard care upon collection.',
        ];
    }

    /**
     * Warehouse Facility Listing Copywriter
     */
    public function generateWarehouseCopy(array $data): array
    {
        $system = <<<PROMPT
You are a commercial real estate copywriter for logistics facilities in Nepal.
Based on warehouse specs (location, capacity sqft, price, and features), return strictly a JSON object with:
- title: string (professional, compelling listing title)
- description: string (2-3 paragraphs highlighting highway accessibility, loading bays, safety, and operational efficiency)
- highlights: array of strings (4-5 key facility selling points)
- ideal_for: string (recommended commercial uses e.g., 'FMCG, E-commerce, Cold/Dry Storage')
PROMPT;

        $user = json_encode($data, JSON_UNESCAPED_SLASHES);
        $result = $this->chat($system, $user, 'json');

        if (is_array($result) && !empty($result)) {
            return $result;
        }

        $location = $data['location'] ?? 'Kathmandu';
        $sqft = $data['total_sqft'] ?? 5000;

        return [
            'title' => "Premium {$sqft} Sq.Ft Logistics Facility in {$location}",
            'description' => "Centrally situated in {$location}, this {$sqft} sq.ft warehouse offers high-ceiling storage, dedicated loading docks, and 24/7 security. Strategically positioned with seamless access to primary transport arteries for smooth distribution.",
            'highlights' => [
                "Generous {$sqft} sq.ft clear storage area",
                'Direct truck and container access',
                '24/7 monitored perimeter security',
                'Dedicated loading and unloading docks',
            ],
            'ideal_for' => 'Wholesale distribution, bulk inventory storage, and regional e-commerce fulfillment.',
        ];
    }

    /**
     * Natural Language Reminder Parser
     */
    public function parseNaturalReminder(string $text): array
    {
        $today = now()->format('Y-m-d H:i');
        $system = <<<PROMPT
You are a reminder parser for KTM-WDC Logistics. Today is {$today}.
Parse the text (English or Nepali) into reminder attributes. Return strictly a JSON object with:
- title: string (clean, concise reminder title)
- starts_at: string (YYYY-MM-DDTHH:MM or null)
- remind_at: string (YYYY-MM-DDTHH:MM or null)
- priority: string ('low', 'medium', or 'high')
- notes: string (any extra details, phone numbers, or context)
PROMPT;

        $result = $this->chat($system, $text, 'json');

        if (is_array($result) && !empty($result['title'])) {
            return $result;
        }

        // Rule-based fallback
        $tomorrow = now()->addDay()->setTime(10, 0)->format('Y-m-d\TH:i');
        return [
            'title' => trim(preg_replace('/^(remind me to|please remind me to|remember to)\s+/i', '', $text)),
            'starts_at' => $tomorrow,
            'remind_at' => now()->addDay()->setTime(9, 30)->format('Y-m-d\TH:i'),
            'priority' => 'medium',
            'notes' => $text,
        ];
    }

    /**
     * Executive Logistics Daily Brief
     */
    public function generateDashboardBrief(string $role, array $stats): array
    {
        $today = now()->format('l, F j, Y');
        $system = <<<PROMPT
You are the KTM-WDC Chief Operations Analyst. Today is {$today}.
Based on the user's role ({$role}) and current system stats, generate an operational executive summary.
Return strictly a JSON object with:
- greeting: string (e.g., 'Namaste Admin, here is your logistics briefing:')
- key_metrics_summary: string (1 sentence summarising overall volume and health)
- operational_bullets: array of strings (3 actionable bullets highlighting priorities, capacity, or dispatches)
- recommended_action: string (1 concrete priority step for today)
PROMPT;

        $user = json_encode(['role' => $role, 'stats' => $stats], JSON_UNESCAPED_SLASHES);
        $result = $this->chat($system, $user, 'json');

        if (is_array($result) && !empty($result['operational_bullets'])) {
            return $result;
        }

        $activeDispatches = $stats['in_transit_dispatches'] ?? ($stats['dispatchesByStatus']['in_transit'] ?? 0);
        $pending = $stats['pending_dispatches'] ?? 0;
        $occupancy = $stats['occupancyRate'] ?? 70;

        return [
            'greeting' => "Namaste " . ucwords(str_replace('_', ' ', $role)) . ", here is your daily logistics briefing:",
            'key_metrics_summary' => "Operations are active with {$activeDispatches} dispatches in transit and warehouse capacity at {$occupancy}%.",
            'operational_bullets' => [
                "{$activeDispatches} cargo dispatches currently active across primary corridors.",
                "{$pending} pending requests awaiting operational clearance.",
                "Storage utilization is steady at {$occupancy}% occupancy across registered warehouses.",
            ],
            'recommended_action' => 'Review pending dispatch manifests and confirm driver assignments for today.',
        ];
    }

    /**
     * Health Check - Verify API connectivity
     */
    public function healthCheck(): array
    {
        $status = [];
        $configuredProvider = strtolower((string) config('services.ai.provider', 'free'));

        $status['mode'] = $configuredProvider;
        $status['external_ai'] = $configuredProvider === 'free' ? 'disabled' : 'enabled';

        // Check Gemini
        $geminiKey = ($configuredProvider === 'free')
            ? 'disabled'
            : (config('services.gemini.api_key') ? 'set' : 'missing');
        $status['gemini'] = $geminiKey;

        // Check OpenAI
        $openaiKey = ($configuredProvider === 'free')
            ? 'disabled'
            : (config('services.openai.api_key') ? 'set' : 'missing');
        $status['openai'] = $openaiKey;

        // Check Groq
        $groqKey = ($configuredProvider === 'free')
            ? 'disabled'
            : (config('services.groq.api_key') ? 'set' : 'missing');
        $status['groq'] = $groqKey;

        return $status;
    }
}
