<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use App\Services\SmartAIService;
use App\Services\EnhancedAIService;
use Illuminate\Http\Request;

class AIController extends Controller
{
    protected $aiService;
    protected $smartAIService;
    protected $enhancedAIService;

    public function __construct(AIService $aiService, SmartAIService $smartAIService, EnhancedAIService $enhancedAIService)
    {
        $this->aiService = $aiService;
        $this->smartAIService = $smartAIService;
        $this->enhancedAIService = $enhancedAIService;
    }

    public function handleVoice(Request $request)
    {
        $transcript = $request->command;
        $language = $request->input('language', 'English');
        $userId = auth()->id();

        // Use comprehensive SmartAIService for intelligent voice processing
        $response = $this->smartAIService->processQuery($transcript, $userId);

        return response()->json($response);
    }

    public function chatSupport(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'language' => 'required|string'
        ]);

        $text = $request->message;
        $language = $request->language;
        $userId = auth()->id();

        // Use comprehensive SmartAIService for comprehensive AI assistance
        $response = $this->smartAIService->processQuery($text, $userId);

        return response()->json($response);
    }

    /**
     * AI Route & Cargo Advisor for Direct Dispatch
     */
    public function dispatchAdvisor(Request $request)
    {
        $data = $request->validate([
            'pickup_address' => 'nullable|string',
            'delivery_address' => 'nullable|string',
            'cargo_type' => 'nullable|string',
            'weight_kg' => 'nullable|numeric',
            'urgency' => 'nullable|string',
            'special_instructions' => 'nullable|string',
        ]);

        $advice = $this->enhancedAIService->adviseDispatch($data);

        return response()->json([
            'success' => true,
            'advice' => $advice,
        ]);
    }

    /**
     * AI Packaging & Vehicle Advisor for Pickup Requests
     */
    public function pickupAdvisor(Request $request)
    {
        $data = $request->validate([
            'pickup_address' => 'nullable|string',
            'cargo_description' => 'nullable|string',
            'estimated_weight_kg' => 'nullable|numeric',
            'fragile' => 'nullable|boolean',
            'urgency' => 'nullable|string',
        ]);

        $advice = $this->enhancedAIService->advisePickup($data);

        return response()->json([
            'success' => true,
            'advice' => $advice,
        ]);
    }

    /**
     * AI Warehouse Commercial Copywriter
     */
    public function warehouseCopy(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string',
            'location' => 'nullable|string',
            'total_sqft' => 'nullable|numeric',
            'price_per_sqft' => 'nullable|numeric',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'security_level' => 'nullable|string',
        ]);

        $copy = $this->enhancedAIService->generateWarehouseCopy($data);

        return response()->json([
            'success' => true,
            'copy' => $copy,
        ]);
    }

    /**
     * AI Natural Language Reminder Quick-Add Parser
     */
    public function parseReminder(Request $request)
    {
        $request->validate([
            'text' => 'required|string',
        ]);

        $parsed = $this->enhancedAIService->parseNaturalReminder($request->input('text'));

        return response()->json([
            'success' => true,
            'parsed' => $parsed,
        ]);
    }

    /**
     * AI Daily Operational Logistics Brief for Dashboard
     */
    public function dashboardBrief(Request $request)
    {
        $user = auth()->user();
        $role = $user ? $user->role : 'client';
        $stats = $request->input('stats', []);

        $brief = $this->enhancedAIService->generateDashboardBrief($role, is_array($stats) ? $stats : []);

        return response()->json([
            'success' => true,
            'brief' => $brief,
        ]);
    }
}