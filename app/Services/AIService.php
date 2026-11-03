<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    public function chat(string $systemPrompt, string $userPrompt, string $format = 'json')
    {
        if (config('services.ai.provider', 'free') === 'free') {
            Log::info('External AI disabled; using built-in fallback response.');
            return null;
        }

        $apiKey = config('services.gemini.api_key');
        
        if (!$apiKey) {
            Log::warning('Gemini API key is missing in .env');
            return null;
        }

        try {
            $fullPrompt = "System: $systemPrompt\n\nUser: $userPrompt";

            if ($format === 'json') {
                $fullPrompt .= "\n\nReturn your response STRICTLY as a valid JSON object. Do not wrap it in markdown blocks.";
            }

            // Google Gemini API endpoint (FREE tier / gemini-1.5-flash)
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;

            $response = Http::timeout(10)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $fullPrompt]
                        ]
                    ]
                ]
            ]);

            if ($response->failed()) {
                Log::error('Gemini API Error: ' . $response->body());
                return null;
            }

            $data = $response->json();
            $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$content) {
                Log::error('Gemini returned empty response.');
                return null;
            }

            if ($format === 'json') {
                // Clean potential markdown code blocks
                $content = preg_replace('/```json\s*|\s*```/', '', $content);
                return json_decode($content, true) ?? $content;
            }

            return $content;

        } catch (\Exception $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate price using strict Admin rules, and let the AI explain it
     */
    public function calculateAndExplainPrice(float $distance, string $vehicleType, float $pricePerKm, float $minCharge, string $serviceType = 'dispatch'): array
    {
        // 1. Fetch TODAY'S active driver rates
        $activeRates = \App\Models\DriverRate::whereDate('date', today())
            ->where('is_active', true)
            ->pluck('base_price')
            ->toArray();
        $marketContext = "Today's driver submitted base prices are: [" . implode(', ', $activeRates) . "]";

        $rawPrice = $distance * $pricePerKm;
        $basePrice = max($minCharge, $rawPrice);

        $configService = app(\App\Services\AdminConfigService::class);
        $marginData = $configService->getAdminMargin($distance, $serviceType);
        
        $marginType = $marginData['type'];
        $marginValue = $marginData['value'];

        if ($marginType === 'flat') {
            $adminPrice = $basePrice + $marginValue;
            $marginExplanation = "A flat margin fee of रू {$marginValue} has been added to the base price.";
        } else {
            $adminPrice = $basePrice + ($basePrice * ($marginValue / 100));
            $marginExplanation = "A margin of {$marginValue}% has been applied to the base price.";
        }
        
        $prompt = "You are a professional logistics assistant. 
        We need to explain a price breakdown to a client.
        - Distance: {$distance} km
        - Vehicle Type: {$vehicleType}
        - Admin's Price per km: रू {$pricePerKm}
        - Base Price (before admin margin): रू " . round($basePrice, 2) . "
        - Margin Applied: {$marginExplanation}
        - Final Price to Client: रू " . round($adminPrice, 2) . "
        - Market Context: {$marketContext}
        Write a short, friendly message in English that explains how we calculated this price. 
        Reference the margin type (flat or percentage) naturally. Keep it professional and under 30 words.
        Return ONLY valid JSON with one key: 'explanation'.";

        $result = $this->chat(
            "You are a logistics pricing expert. Return ONLY valid JSON.",
            $prompt,
            'json'
        );

        return [
            'success' => true,
            'final_price' => round($adminPrice, 2),
            'base_price' => round($basePrice, 2),
            'margin_applied' => ($marginType === 'flat' ? 'रू ' . $marginValue : $marginValue . '%'),
            'explanation' => $result['explanation'] ?? "Calculated based on {$distance}km at रू {$pricePerKm}/km, plus {$marginExplanation}."
        ];
    }

    /**
     * Suggest a dynamic price based on distance and context (Legacy)
     */
    public function suggestDispatchPrice(float $distance, string $vehicleType, float $baseRate, ?float $driverRating = null): array
    {
        $ratingText = $driverRating ? "The driver's average rating is {$driverRating}/5.0." : "The driver's rating is unknown.";
        $prompt = "We need to calculate a fair dispatch price for a logistics job.
        - Distance: {$distance} km
        - Vehicle Type: {$vehicleType}
        - Base Rate (minimum): रू {$baseRate}
        - Context: {$ratingText}
        Provide a JSON response with 3 keys: 'suggested_price', 'min_price', 'max_price'
        Return ONLY valid JSON.";

        $result = $this->chat(
            "You are a logistics pricing expert. Return ONLY valid JSON.",
            $prompt,
            'json'
        );

        if (!$result || !isset($result['suggested_price'])) {
            $base = max($baseRate, $distance * 40);
            return [
                'suggested_price' => round($base * 1.1),
                'min_price' => round($base * 0.9),
                'max_price' => round($base * 1.25),
            ];
        }
        return $result;
    }

    /**
     * Rank drivers and recommend a vehicle based on trip requirements
     */
    public function recommendDrivers(array $drivers, string $pickupAddress, float $distance, string $clientVehicleSelection): array
    {
        $driverList = "";
        foreach ($drivers as $d) {
            $distanceText = is_numeric($d['distance_km'] ?? null) ? $d['distance_km'].'km' : 'not measured';
            $driverList .= "ID: {$d['id']}, Name: {$d['name']}, Distance: {$distanceText}, Rating: {$d['rating']}/5.0\n";
        }

        $prompt = 'We have a pickup request at: \'' . addslashes($pickupAddress) . '\'. Total trip distance: ' . $distance . ' km.
        The client has manually selected this vehicle type: \'' . addslashes($clientVehicleSelection) . '\'.
        Available drivers:
        ' . $driverList . '
        Analyze the route distance and the available driver list.
        1. Recommend the best vehicle type for this trip (Choose EXACTLY from: Standard Van, Pickup, Truck 3 Ton, Truck 5 Ton, Heavy Truck). If the client\'s selection is reasonable, keep their choice. If not, suggest the better fit.
        2. Rank the top 3 best drivers for this job based on measured proximity when present, then rating.
        Return a JSON object using this exact schema: {"recommended_vehicle": string, "drivers": [{"id": int, "reason": string}]}
        Return ONLY valid JSON.';

        $result = $this->chat(
            "You are a logistics dispatcher. Return ONLY valid JSON.",
            $prompt,
            'json'
        );

        if (!$result || !is_array($result) || !isset($result['drivers'])) {
            usort($drivers, function ($a, $b) {
                $aDistance = is_numeric($a['distance_km'] ?? null) ? (float) $a['distance_km'] : INF;
                $bDistance = is_numeric($b['distance_km'] ?? null) ? (float) $b['distance_km'] : INF;

                return [$aDistance, -((float) ($a['rating'] ?? 0))] <=> [$bDistance, -((float) ($b['rating'] ?? 0))];
            });
            return ['recommended_vehicle' => $clientVehicleSelection, 'drivers' => array_slice($drivers, 0, 3)];
        }

        $rankedDrivers = [];
        if (is_array($result['drivers'])) {
            foreach ($result['drivers'] as $aiRec) {
                $found = array_filter($drivers, fn($d) => $d['id'] == $aiRec['id']);
                if (!empty($found)) {
                    $driver = reset($found);
                    $driver['ai_reason'] = $aiRec['reason'];
                    $rankedDrivers[] = $driver;
                }
            }
        }
        return ['recommended_vehicle' => $result['recommended_vehicle'] ?? $clientVehicleSelection, 'drivers' => $rankedDrivers];
    }

    /**
     * Recommend specific equipment models based on job context
     */
    public function recommendEquipmentForJob(string $type, int $duration, string $location, string $description, ?float $budget = null, 
                                            ?string $commodity = null, ?float $weight = null, ?string $dimensions = null, 
                                            ?string $loading_site = null, ?string $unloading_site = null): array
    {
        $budgetContext = $budget ? "The client has a budget of रू {$budget} per day." : "The client has not specified a budget.";
        $prompt = 'A client is requesting equipment for a logistics job.
        - Equipment Type: ' . addslashes($type) . '
        - Duration: ' . $duration . ' days
        - Work Location: ' . addslashes($location) . '
        - Job Description: ' . addslashes($description) . '
        - Budget Context: ' . $budgetContext . '
        - Commodity Name: ' . addslashes($commodity ?? 'N/A') . '
        - Approximate Weight: ' . ($weight ? $weight . ' kg' : 'Not specified') . '
        - Cargo Dimensions: ' . addslashes($dimensions ?? 'N/A') . '
        - Loading Site: ' . addslashes($loading_site ?? 'N/A') . '
        - Unloading Site: ' . addslashes($unloading_site ?? 'N/A') . '
        Based on this complete information, recommend the best specific model for this equipment type available in Nepal.
        Consider the weight and dimensions to ensure the equipment has the right lifting capacity.
        Also, provide a fair estimated market price per hour and per day based on standard rental rates.
        Return a JSON object using this exact schema: {"recommended_model": string, "reason": string, "estimated_price_per_hour": int, "estimated_price_per_day": int}
        Return ONLY valid JSON.';

        $result = $this->chat(
            "You are an equipment rental and logistics expert in Nepal. Return ONLY valid JSON.",
            $prompt,
            'json'
        );

        if (!$result || !isset($result['recommended_model'])) {
            return [
                'recommended_model' => ucfirst($type) . ' (Standard Model)',
                'reason' => 'Our AI is temporarily offline. We recommend a standard model.',
                'estimated_price_per_hour' => 500,
                'estimated_price_per_day' => 5000
            ];
        }
        return $result;
    }

    /**
     * Predict warehouse utilization based on stats.
     */
    public function predictWarehouseUtilization(array $warehouseStats, int $daysAhead = 30): array
    {
        $statsStr = "";
        foreach ($warehouseStats as $w) {
            $statsStr .= "- Warehouse: {$w['name']}, Current Capacity: {$w['current_area_sqft']} sqft, Incoming Requests: {$w['incoming_requests']}, Avg Monthly Growth: {$w['avg_growth_percent']}%\n";
        }

        $prompt = "We need to predict warehouse space requirements for the next {$daysAhead} days. Here is the current data: {$statsStr}
        Analyze the 'Avg Monthly Growth' and 'Incoming Requests' to predict which warehouses are likely to run out of space.
        Return a JSON array of objects. Each object must have: 'warehouse_name', 'predicted_days_until_full', 'suggestion'.
        Return ONLY valid JSON.";

        $result = $this->chat(
            "You are a logistics and inventory analyst. Return ONLY valid JSON.",
            $prompt,
            'json'
        );

        if (is_array($result)) { return $result; }

        return [['warehouse_name' => 'Data Insufficient', 'predicted_days_until_full' => 365, 'suggestion' => 'Ensure historical data is logged.']];
    }

    /**
     * Invoice Fraud Detection
     */
    public function checkInvoiceAnomaly(float $distance, float $basePrice, float $adminMargin, float $totalAmount): array
    {
        $prompt = "You are a logistics fraud detection expert.
        We have an invoice with the following metrics: Distance: {$distance} km, Base Price: रू {$basePrice}, Admin Margin: {$adminMargin}%, Total: रू {$totalAmount}.
        Analyze these numbers. If math is correct and margin makes sense, respond with: {'anomaly': false, 'message': 'Invoice looks valid.'}
        If suspicious, respond with: {'anomaly': true, 'message': 'Explain the exact suspicious reason here.'}
        Return ONLY valid JSON.";

        $result = $this->chat(
            "You are a finance and logistics auditor. Return ONLY valid JSON.",
            $prompt,
            'json'
        );

        return is_array($result) ? $result : ['anomaly' => false, 'message' => 'AI offline, manual check required.'];
    }

    /**
     * Multi-language live chat support
     */
    public function chatSupport(string $userQuestion, string $userLanguage = 'English'): string
    {
        $prompt = "You are a professional customer support agent for KTM-WDC.
        The user's language is: {$userLanguage}.
        KTM-WDC Services: - **Warehouses:** Secure storage starting at Rs 45/sq ft. CCTV, cold storage. - **Pickups:** Door-to-door pickup services. - **Dispatches:** Real-time GPS tracking, Rs 45 per km plus admin margin. - **Equipment Rental:** JCBs, Excavators, Cranes.
        The user is asking: '{$userQuestion}'
        **CRITICAL INSTRUCTION:** DO NOT write long paragraphs. Use bullet points (`-`) or numbered lists (`1.`, `2.`). Keep each concise. Respond in their language ({$userLanguage}).";

        return $this->chat(
            "You are a highly knowledgeable logistics support agent. Always use bulleted lists.",
            $prompt,
            'text'
        ) ?? "Sorry, our AI assistant is currently offline. Please contact support.";
    }
}
