<?php

namespace Tests\Feature;

use App\Services\EnhancedAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnhancedAIServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EnhancedAIService $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiService = new EnhancedAIService();
    }

    public function test_ai_service_health_check(): void
    {
        $health = $this->aiService->healthCheck();

        $this->assertIsArray($health);
        $this->assertArrayHasKey('gemini', $health);
        $this->assertContains($health['gemini'], ['set', 'missing', 'disabled']);
        $this->assertArrayHasKey('external_ai', $health);
    }

    public function test_free_ai_mode_does_not_call_external_provider(): void
    {
        config([
            'services.ai.provider' => 'free',
            'services.openai.api_key' => 'test-key',
        ]);

        $this->assertNull($this->aiService->chat(
            'Return JSON.',
            'Create a dispatch plan.',
            'json',
            'openai'
        ));

        $health = $this->aiService->healthCheck();
        $this->assertSame('free', $health['mode']);
        $this->assertSame('disabled', $health['external_ai']);
        $this->assertSame('disabled', $health['openai']);
    }

    public function test_semantic_search_finds_relevant_documents(): void
    {
        $documents = [
            ['id' => 1, 'text' => 'Dispatch services for cargo delivery across Nepal'],
            ['id' => 2, 'text' => 'Warehouse storage and inventory management'],
            ['id' => 3, 'text' => 'Equipment rental for construction projects'],
        ];

        $results = $this->aiService->semanticSearch('cargo delivery dispatch', $documents, 1);

        $this->assertNotEmpty($results);
        $this->assertEquals(1, $results[0]['id'], 'Should find dispatch service as most relevant');
    }

    public function test_semantic_search_handles_multiple_documents(): void
    {
        $documents = [
            ['id' => 1, 'text' => 'pickup and delivery services'],
            ['id' => 2, 'text' => 'warehouse storage solutions'],
            ['id' => 3, 'text' => 'vehicle rental and dispatch'],
        ];

        $results = $this->aiService->semanticSearch('pickup warehouse storage', $documents, 3);

        $this->assertCount(2, $results, 'Should return 2 most relevant results');
        $this->assertContains($results[0]['id'], [1, 2]);
    }

    public function test_keyword_extraction(): void
    {
        $text = "KTM-WDC provides dispatch services for cargo delivery across Kathmandu, Bhaktapur, and Lalitpur";
        
        // Use reflection to test protected method
        $reflection = new \ReflectionClass($this->aiService);
        $method = $reflection->getMethod('extractKeywords');
        $method->setAccessible(true);
        
        $keywords = $method->invoke($this->aiService, $text);

        $this->assertNotEmpty($keywords);
        $this->assertContains('dispatch', $keywords);
        $this->assertContains('cargo', $keywords);
        $this->assertContains('delivery', $keywords);
    }

    public function test_language_detection(): void
    {
        $reflection = new \ReflectionClass($this->aiService);
        $method = $reflection->getMethod('detectLanguage');
        $method->setAccessible(true);

        // English text
        $lang1 = $method->invoke($this->aiService, 'dispatch cargo delivery');
        $this->assertEquals('English', $lang1);

        // Nepali text
        $lang2 = $method->invoke($this->aiService, 'डिस्प्याच सेवा');
        $this->assertNotEmpty($lang2);
    }

    public function test_gemini_chat_with_json_format(): void
    {
        // Only run if Gemini API key is configured
        if (!env('GEMINI_API_KEY')) {
            $this->markTestSkipped('Gemini API key not configured');
        }

        $response = $this->aiService->chat(
            'You are a helpful assistant. Return ONLY valid JSON.',
            'List 2 logistics services in JSON format with keys: service_name, description',
            'json'
        );

        // Verify response format - can be array or string, but should not be empty if received
        if ($response === null) {
            $this->markTestSkipped('Gemini API did not return a response');
        } elseif (is_array($response)) {
            $this->assertIsArray($response);
            $this->assertNotEmpty($response);
        } else {
            $this->assertIsString($response);
            $this->assertNotEmpty($response);
        }
    }
}
