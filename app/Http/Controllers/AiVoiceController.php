<?php

namespace App\Http\Controllers;

use App\Services\VoiceAssistantService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AiVoiceController extends Controller
{
    protected $assistant;

    public function __construct(VoiceAssistantService $assistant)
    {
        $this->assistant = $assistant;
    }

   public function voiceAssistant(Request $request)
{
    $request->validate([
        'message' => ['required', 'string', 'max:4000'],
        'language' => ['nullable', 'in:en,np'],
    ]);
    try {
        $user = Auth::user();
        $message = $request->input('message');
        $language = $request->input('language', 'en');

        Log::info('🎤 Voice request received', ['user' => $user->id, 'message' => $message, 'lang' => $language]);

        $result = $this->assistant->process($user->id, $message, $language);

        return response()->json($result);
    } catch (\Exception $e) {
        Log::error('❌ Voice controller error: ' . $e->getMessage());
        Log::error('❌ Controller trace: ' . $e->getTraceAsString());
        return response()->json([
            'message' => $request->input('language') === 'np' 
                ? 'क्षमा गर्नुहोस्, मैले एउटा त्रुटि भेटाएँ। कृपया फेरि प्रयास गर्नुहोस्।' 
                : 'Sorry, I encountered an error. Please try again.',
            'done' => false,
        ], 500);
    }
}

    public function transcribe(Request $request, \App\Services\EnhancedAIService $aiService)
    {
        $request->validate([
            'audio' => ['required', 'string'],
            'mime_type' => ['nullable', 'string', 'max:60'],
            'language' => ['nullable', 'in:en,np'],
        ]);

        try {
            $rawAudio = $request->input('audio');
            if (preg_match('/^data:([^;]+);base64,(.+)$/', $rawAudio, $matches)) {
                $mimeType = $matches[1];
                $base64 = $matches[2];
            } else {
                $mimeType = $request->input('mime_type', 'audio/webm');
                $base64 = $rawAudio;
            }

            $language = $request->input('language', 'en');
            $transcript = $aiService->transcribeAudio($base64, $mimeType, $language);

            Log::info('🎤 Audio transcribed via Gemini', [
                'user' => Auth::id(),
                'transcript' => $transcript,
                'lang' => $language,
            ]);

            return response()->json([
                'success' => true,
                'transcript' => $transcript ?? '',
            ]);
        } catch (\Throwable $e) {
            Log::error('❌ Audio transcription controller error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to transcribe audio.',
                'transcript' => '',
            ], 500);
        }
    }
}
