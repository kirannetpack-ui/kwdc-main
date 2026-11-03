# AI Services Documentation

## Overview

KTM-WDC now has an **Enhanced AI Service** with multiple LLM providers, fallback strategies, and specialized AI capabilities.

## Current AI Capabilities

### 1. **Primary AI Service** (`AIService.php`)
Used for core logistics operations:
- **Price Calculation & Explanation**: Calculates dispatch pricing with AI-generated explanations
- **Driver Recommendations**: AI ranks drivers based on distance, rating, and job requirements
- **Equipment Recommendations**: Suggests optimal equipment models based on job context
- **Warehouse Predictions**: Forecasts warehouse capacity needs
- **Fraud Detection**: Analyzes invoices for anomalies
- **Customer Support Chat**: Multilingual support with bullet-point formatting

### 2. **Voice Assistant Service** (`VoiceAssistantService.php`)
Handles voice/text input:
- **Natural Language Understanding**: Extracts intents from user input
- **Form Field Extraction**: Automatically fills form fields from speech/text
- **Multi-language Support**: Handles English and Nepali
- **Context Awareness**: Maintains conversation session history
- **Fallback Models**: Tries multiple Gemini models (gemini-2.5-flash, gemini-3.5-flash)

### 3. **Enhanced AI Service** (`EnhancedAIService.php`) - NEW
Unified service with multiple providers:
- **Multi-Provider Support**: Gemini (primary) → OpenAI → Groq (fallback chain)
- **Automatic Failover**: Seamlessly switches providers if one fails
- **Semantic Search**: Finds relevant documents/FAQs from knowledge base
- **Language Detection**: Auto-detects input language (English/Nepali/Hindi)
- **Document Processing**: Placeholder for OCR/Vision API integration
- **Health Check**: Verifies API key availability

## Supported AI Providers

### Google Gemini (Primary - FREE)
```
Model: gemini-1.5-flash
Strengths: Fast, free tier available, good for general tasks
Setup: Set GEMINI_API_KEY in .env
```

### OpenAI (Fallback - PAID)
```
Model: gpt-3.5-turbo (configurable)
Strengths: Excellent reasoning, better JSON handling
Setup: Set OPENAI_API_KEY in .env
Cost: ~$0.0015 per 1K tokens
```

### Groq (Ultra-Fast - FREE)
```
Model: mixtral-8x7b-32768
Strengths: Extremely fast (15-20x faster than typical), good for structured tasks
Setup: Set GROQ_API_KEY in .env
Cost: Free with rate limits
Ideal For: Quick classifications, extractions, structured outputs
```

## Setup Instructions

### 1. Configure Primary Provider (Gemini)
```bash
# .env
GEMINI_API_KEY=your_gemini_api_key_here
```
Get free API key: https://ai.google.dev

### 2. Configure Fallback Providers (Optional)

**OpenAI:**
```bash
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-3.5-turbo  # or gpt-4
```

**Groq:**
```bash
GROQ_API_KEY=your_groq_api_key
GROQ_MODEL=mixtral-8x7b-32768
```

### 3. Verify Configuration
```php
$aiService = app(\App\Services\EnhancedAIService::class);
$health = $aiService->healthCheck();
// Returns: ['gemini' => '✅', 'openai' => '❌', 'groq' => '✅']
```

## Usage Examples

### Chat with Automatic Fallback
```php
$aiService = app(\App\Services\EnhancedAIService::class);

$response = $aiService->chat(
    systemPrompt: "You are a logistics expert",
    userPrompt: "What's the best vehicle for 50km delivery?",
    format: "json"
);
// Will try: Gemini → OpenAI → Groq
```

### Semantic Search
```php
$documents = [
    ['id' => 1, 'text' => 'Dispatch services for cargo'],
    ['id' => 2, 'text' => 'Warehouse storage solutions'],
];

$results = $aiService->semanticSearch(
    query: "cargo delivery options",
    documents: $documents,
    topK: 2
);
// Returns most relevant documents
```

### Language Detection & Multi-language Support
```php
$result = $aiService->generateMultiLanguage(
    text: "Please provide warehouse rental rates",
    targetLanguage: "auto" // Auto-detects then generates in that language
);
// Returns: ['language' => 'English', 'content' => '...', 'translated' => true]
```

### Use Specific Provider
```php
// Force use of fast Groq for quick extractions
$response = $aiService->chat(
    systemPrompt: "Extract JSON only",
    userPrompt: "Parse: Name: John, Age: 30",
    format: "json",
    provider: "groq"  // Force Groq instead of auto-fallback
);
```

## Performance Comparison

| Task | Gemini | OpenAI | Groq |
|------|--------|--------|------|
| **Latency** | ~2-3s | ~1-2s | **<500ms** |
| **JSON Output** | Good | Excellent | Good |
| **Reasoning** | Good | Excellent | Good |
| **Cost** | Free | Paid | Free |
| **Best For** | General | Complex reasoning | Speed-critical |

## Integration Points

### 1. Dispatch Pricing
```php
// AIController or DispatchController
$priceData = $this->aiService->calculateAndExplainPrice(
    distance: 25,
    vehicleType: 'Standard Van',
    pricePerKm: 45,
    minCharge: 500
);
```

### 2. Driver Recommendations
```php
// PickupRequestController
$recommendation = $this->aiService->recommendDrivers(
    drivers: $availableDrivers,
    pickupAddress: 'Kathmandu',
    distance: 25,
    clientVehicleSelection: 'Standard'
);
```

### 3. Equipment Recommendations
```php
// EquipmentRequestController
$equipment = $this->aiService->recommendEquipmentForJob(
    type: 'JCB',
    duration: 3,
    location: 'Bhaktapur',
    description: 'Excavation work',
    weight: 5000,
    dimensions: '10m x 5m'
);
```

### 4. Voice/Chat Assistance
```php
// AiVoiceController
$voiceInput = "I need to pickup from Boudha and drop at Bhaktapur";
$response = $voiceAssistant->process(
    userId: $user->id,
    userMessage: $voiceInput,
    language: 'en'
);
// Opens /pickup/direct-create?pickup_address=Boudha&delivery_address=Bhaktapur
```

## Future Enhancements

### 1. Document OCR (Compliance Documents)
```php
$result = $aiService->processDocument('path/to/security_license.pdf');
// Extracts: license number, expiry date, agency name, etc.
```

### 2. Semantic Search with Vector DB
```php
// Use Pinecone, Weaviate, or local embedding models
// For FAQ/knowledge base searching
```

### 3. Streaming Responses
```php
// For long-form content generation
$aiService->chatStream($prompt); // Real-time streaming
```

### 4. Fine-tuning
```php
// Fine-tune on KTM-WDC specific logistics data
// Improves accuracy for domain-specific tasks
```

## API Costs (Monthly Estimate)

### Scenario: 1000 requests/day
- **Gemini**: Free ✅
- **OpenAI**: ~$1.50/month
- **Groq**: Free ✅
- **Total**: ~$1.50 (negligible)

### Scenario: 10,000 requests/day
- **Gemini**: Free ✅
- **OpenAI**: ~$15/month
- **Groq**: Free (with rate limits) ✅
- **Total**: ~$15/month

## Troubleshooting

### Issue: "All AI providers failed"
**Solution:**
1. Check API keys in `.env`
2. Verify internet connectivity
3. Check API service status pages
4. View logs: `storage/logs/laravel.log`

### Issue: Rate Limiting
**Solution:**
- Groq: 30 requests/minute (free tier)
- OpenAI: Standard rate limits (paid tier has higher limits)
- Gemini: Free tier has some limits, upgrade if needed

### Issue: Timeout
**Solution:**
- Groq timeouts after 5s (fastest)
- OpenAI timeouts after 10s
- Gemini timeouts after 10s
- Use Groq for time-critical tasks

## Testing

Run AI service tests:
```bash
php artisan test --filter=EnhancedAIServiceTest
php artisan test --filter=AIAssistantFlowTest
php artisan test --filter=VoiceAssistantTest
```

## Support

For issues or questions about AI services, contact: development@ktm-wdc.com

---

**Last Updated**: 2026-08-14
**Version**: 2.0 (Enhanced Multi-Provider)
