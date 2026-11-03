<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoiceAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_voice_assistant_recognizes_pickup_request_from_speech(): void
    {
        $user = User::factory()->create(['role' => 'client', 'email' => 'voice@example.com']);
        $this->actingAs($user);

        $response = $this->postJson('/ai/voice-assistant', [
            'message' => 'I need a pickup from Boudha to Bhaktapur',
            'language' => 'en',
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertSame('open_page', $data['action']);
        $this->assertSame('/pickup/direct-create', $data['url']);
        $this->assertArrayHasKey('pickup_address', $data['data']);
    }

    public function test_voice_assistant_recognizes_dispatch_request(): void
    {
        $user = User::factory()->create(['role' => 'client', 'email' => 'dispatch@example.com']);
        $this->actingAs($user);

        $response = $this->postJson('/ai/voice-assistant', [
            'message' => 'Create a dispatch from Kathmandu to Pokhara with cargo',
            'language' => 'en',
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertSame('open_page', $data['action']);
        $this->assertSame('/dispatch/direct-create', $data['url']);
        $this->assertArrayHasKey('pickup_address', $data['data']);
    }

    public function test_voice_assistant_extracts_distance_and_price(): void
    {
        $user = User::factory()->create(['role' => 'client', 'email' => 'extract@example.com']);
        $this->actingAs($user);

        $response = $this->postJson('/ai/voice-assistant', [
            'message' => 'Dispatch from Thamel to Bhaktapur, 15 km, total amount 500',
            'language' => 'en',
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertSame('open_page', $data['action']);
        $this->assertSame('/dispatch/direct-create', $data['url']);
        $this->assertArrayHasKey('pickup_address', $data['data']);
        // Distance extraction
        if (isset($data['data']['total_distance'])) {
            $this->assertStringContainsString('15', $data['data']['total_distance']);
        }
    }

    public function test_voice_assistant_handles_tracking_request(): void
    {
        $user = User::factory()->create(['role' => 'client', 'email' => 'track@example.com']);
        $this->actingAs($user);

        $response = $this->postJson('/ai/voice-assistant', [
            'message' => 'Where is my dispatch? I want to track it.',
            'language' => 'en',
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertSame('open_page', $data['action']);
        $this->assertSame('/dispatch', $data['url']);
    }

    public function test_voice_assistant_can_fill_pickup_contact_person_on_current_form(): void
    {
        $user = User::factory()->create(['role' => 'client', 'email' => 'fill-contact@example.com']);
        $this->actingAs($user);

        $response = $this->postJson('/ai/voice-assistant', [
            'message' => 'put the contact person name as srijan gautam on pickup request that u opened',
            'language' => 'en',
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertSame('open_page', $data['action']);
        $this->assertSame('/pickup/direct-create', $data['url']);
        $this->assertSame('srijan gautam', $data['data']['pickup_contact_person'] ?? null);
    }

    public function test_voice_assistant_answers_local_distance_questions(): void
    {
        $user = User::factory()->create(['role' => 'client', 'email' => 'distance@example.com']);
        $this->actingAs($user);

        $response = $this->postJson('/ai/voice-assistant', [
            'message' => 'how far is ateshor to bhatapur',
            'language' => 'en',
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertSame('answer', $data['action']);
        $this->assertSame('distance_answer', $data['intent']);
        $this->assertStringContainsString('Koteshwor', $data['message']);
        $this->assertStringContainsString('Bhaktapur', $data['message']);
        $this->assertStringContainsString('km', $data['message']);
    }

    public function test_voice_assistant_responds_with_message(): void
    {
        $user = User::factory()->create(['role' => 'client', 'email' => 'msg@example.com']);
        $this->actingAs($user);

        $response = $this->postJson('/ai/voice-assistant', [
            'message' => 'pickup from my location to warehouse at 15 km distance',
            'language' => 'en',
        ]);

        $response->assertOk();
        $data = $response->json();

        // Should have a meaningful message
        $this->assertNotEmpty($data['message']);
        $this->assertSame('open_page', $data['action']);
    }

    public function test_voice_assistant_returns_richer_guidance_for_prefilled_pickup(): void
    {
        $user = User::factory()->create(['role' => 'client', 'email' => 'rich-guidance@example.com']);

        $response = $this->actingAs($user)->postJson('/ai/voice-assistant', [
            'message' => 'pickup from Boudha to Bhaktapur with 2 boxes 500 kg tomorrow at 10 AM',
            'language' => 'en',
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertSame('open_page', $data['action']);
        $this->assertSame('pickup_request', $data['intent']);
        $this->assertArrayHasKey('guidance', $data);
        $this->assertArrayHasKey('missing_fields', $data);
        $this->assertArrayHasKey('summary', $data);
        $this->assertStringContainsString('500 kg', $data['data']['items_description'] ?? '');
        $this->assertSame(now()->addDay()->format('Y-m-d'), $data['data']['scheduled_date'] ?? null);
    }

    public function test_voice_assistant_answers_price_estimate_locally(): void
    {
        $user = User::factory()->create(['role' => 'client', 'email' => 'price-ai@example.com']);

        $response = $this->actingAs($user)->postJson('/ai/voice-assistant', [
            'message' => 'estimate price for dispatch 20 km by truck',
            'language' => 'en',
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertSame('answer', $data['action']);
        $this->assertSame('price_answer', $data['intent']);
        $this->assertSame(1125, $data['data']['estimated_price'] ?? null);
        $this->assertStringContainsString('Rough estimate', $data['message']);
    }

    public function test_voice_assistant_explains_local_ai_mode(): void
    {
        $user = User::factory()->create(['role' => 'client', 'email' => 'ai-status@example.com']);

        $response = $this->actingAs($user)->postJson('/ai/voice-assistant', [
            'message' => 'is the OpenAI API key connected or is this local ai',
            'language' => 'en',
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertSame('answer', $data['action']);
        $this->assertSame('ai_status', $data['intent']);
        $this->assertStringContainsString('free local mode', $data['message']);
    }

    public function test_voice_assistant_understands_typo_correction_commands(): void
    {
        $user = User::factory()->create(['role' => 'client', 'email' => 'correction-ai@example.com']);

        $response = $this->actingAs($user)->postJson('/ai/voice-assistant', [
            'message' => 'chnage boudha to lalitpur',
            'language' => 'en',
        ]);

        $response->assertOk();
        $data = $response->json();

        $this->assertSame('answer', $data['action']);
        $this->assertSame('correction_update', $data['intent']);
        $this->assertSame('boudha', $data['data']['old_value'] ?? null);
        $this->assertSame('lalitpur', $data['data']['new_value'] ?? null);
        $this->assertStringContainsString('correction', $data['message']);
    }

    public function test_voice_assistant_widget_has_single_message_area_and_text_fallback(): void
    {
        $user = User::factory()->create(['role' => 'client', 'email' => 'voice-widget@example.com']);

        $response = $this->actingAs($user)->get('/pickup');

        $response->assertOk();
        $html = $response->getContent();

        $this->assertSame(1, substr_count($html, 'id="voiceMessages"'));
        $this->assertStringContainsString('id="voiceTextInput"', $html);
        $this->assertStringContainsString('id="voiceSendBtn"', $html);
        $this->assertStringContainsString('id="assistantClearBtn"', $html);
        $this->assertStringContainsString('KWDC Assistant', $html);
        $this->assertStringContainsString('id="assistantQuickActions"', $html);
        $this->assertStringContainsString('kwdc-assistant-panel', $html);
        $this->assertStringNotContainsString('id="ai-chat-btn"', $html);
        $this->assertStringNotContainsString('id="voice-btn"', $html);

        $script = file_get_contents(public_path('js/voice-assistant.js'));
        $this->assertStringContainsString('kwdcAssistantContext', $script);
        $this->assertStringContainsString('chnage', $script);
    }
}
