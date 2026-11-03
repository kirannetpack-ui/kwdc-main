<?php

namespace App\Services;

use App\Models\ConversationSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VoiceAssistantService
{
    protected $apiKey;
    protected $smartAIService;

    public function __construct(SmartAIService $smartAIService)
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->smartAIService = $smartAIService;
    }

    public function process($userId, $userMessage, $language = 'en')
    {
        try {
            $session = ConversationSession::firstOrCreate(
                ['user_id' => $userId],
                ['status' => 'idle', 'context' => [], 'collected_data' => []]
            );

            // Use comprehensive SmartAIService for intelligent processing
            $smartResponse = $this->smartAIService->processQuery($userMessage, $userId);
            
            // Ensure response has action key
            if (!isset($smartResponse['action'])) {
                Log::warning('SmartAIService returned response without action key', ['response' => $smartResponse]);
                return [
                    'action' => 'general_help',
                    'message' => 'I did not understand your request. How can I help?',
                    'done' => false,
                ];
            }

            // Convert guidance response to voice assistant format
            if ($smartResponse['action'] === 'guidance') {
                return $this->formatGuidanceForVoice($smartResponse);
            }

            // For open_page actions, ensure it's in voice format
            if ($smartResponse['action'] === 'open_page') {
                $this->updateSession($session, $smartResponse, $userMessage);
                return [
                    'message' => $smartResponse['message'] ?? 'Opening page...',
                    'action' => 'open_page',
                    'url' => $smartResponse['url'],
                    'intent' => $smartResponse['intent'] ?? null,
                    'data' => $smartResponse['data'] ?? [],
                    'guidance' => $smartResponse['guidance'] ?? null,
                    'recommendations' => $smartResponse['recommendations'] ?? [],
                    'requires_confirmation' => $smartResponse['requires_confirmation'] ?? true,
                    'missing_fields' => $smartResponse['missing_fields'] ?? [],
                    'summary' => $smartResponse['summary'] ?? null,
                    'confidence' => $smartResponse['confidence'] ?? null,
                    'done' => false,
                ];
            }

            // For general help, format as voice message
            if ($smartResponse['action'] === 'general_help') {
                return $this->formatHelpForVoice($smartResponse);
            }

            // For logout and other actions, pass through as-is
            return $smartResponse;

        } catch (\Exception $e) {
            Log::error('VoiceAssistant error: ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());
            return [
                'action' => 'error',
                'message' => 'The assistant could not complete this request. Please try again.',
                'done' => false,
            ];
        }
    }

    /**
     * Format guidance response for voice assistant
     */
    private function formatGuidanceForVoice(array $response): array
    {
        $message = $response['guidance']['title'] . "\n\n" . 
                   $response['guidance']['description'] . "\n\n";

        if (isset($response['guidance']['fields_guidance'])) {
            $message .= "Fields to fill:\n";
            foreach ($response['guidance']['fields_guidance'] as $field => $info) {
                $message .= "• " . $info['label'] . ": " . $info['hint'] . "\n";
            }
        }

        return [
            'message' => $message,
            'action' => 'guidance',
            'intent' => $response['intent'],
            'guidance' => $response['guidance'],
            'recommendations' => $response['recommendations'] ?? [],
            'done' => false,
        ];
    }

    /**
     * Format help response for voice assistant
     */
    private function formatHelpForVoice(array $response): array
    {
        $message = $response['title'] . "\n\n" . $response['message'] . "\n\n";

        if (isset($response['services'])) {
            foreach ($response['services'] as $service => $description) {
                $message .= "• " . $service . ": " . $description . "\n";
            }
        }

        if (isset($response['example_queries'])) {
            $message .= "\nExample queries:\n";
            foreach ($response['example_queries'] as $example) {
                $message .= "• " . $example . "\n";
            }
        }

        return [
            'message' => $message,
            'action' => 'general_help',
            'done' => false,
        ];
    }

    protected function updateSession($session, $parsed, $userMessage)
    {
        $context = $session->context ?? [];
        $context[] = ['role' => 'user', 'parts' => [['text' => $userMessage]]];
        $context[] = ['role' => 'model', 'parts' => [['text' => $parsed['message'] ?? '']]];
        $session->context = array_slice($context, -10);
        
        if (isset($parsed['intent'])) {
            $session->intent = $parsed['intent'];
        }
        
        if (!empty($parsed['data'])) {
            $session->collected_data = array_merge($session->collected_data ?? [], $parsed['data']);
        }
        
        $session->status = ($parsed['done'] ?? false) ? 'completed' : 'waiting_for_field';
        $session->save();
    }
}
