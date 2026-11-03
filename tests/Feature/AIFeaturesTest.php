<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatch_advisor_endpoint_returns_operational_data(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $this->actingAs($user);

        $response = $this->postJson('/ai/dispatch-advisor', [
            'pickup_address' => 'Kathmandu',
            'delivery_address' => 'Pokhara',
            'cargo_type' => 'Medical Supplies',
            'weight_kg' => 450,
            'urgency' => 'high',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'advice' => [
                'suggested_vehicle',
                'estimated_hours',
                'estimated_distance_km',
                'suggested_price_npr',
                'route_advisory',
                'handling_precautions',
                'summary',
            ],
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_pickup_advisor_endpoint_returns_packaging_and_vehicle(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $this->actingAs($user);

        $response = $this->postJson('/ai/pickup-advisor', [
            'pickup_address' => 'Lalitpur',
            'cargo_description' => 'Fragile glassware and electronics',
            'estimated_weight_kg' => 45,
            'fragile' => true,
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'advice' => [
                'suggested_vehicle',
                'packaging_advice',
                'estimated_price_npr',
                'estimated_minutes',
                'handling_notes',
            ],
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_warehouse_copy_endpoint_generates_marketing_copy(): void
    {
        $user = User::factory()->create(['role' => 'property_owner']);
        $this->actingAs($user);

        $response = $this->postJson('/ai/warehouse-copy', [
            'name' => 'Kathmandu Central Hub',
            'location' => 'Balkumari, Lalitpur',
            'total_sqft' => 8000,
            'price_per_sqft' => 45,
            'features' => ['CCTV', 'Loading Dock', 'Fire Sprinklers'],
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'copy' => [
                'title',
                'description',
                'highlights',
                'ideal_for',
            ],
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertIsArray($response->json('copy.highlights'));
    }

    public function test_parse_reminder_endpoint_extracts_structured_attributes(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $this->actingAs($user);

        $response = $this->postJson('/ai/parse-reminder', [
            'text' => 'Remind me to inspect cargo truck BA 2 KHA 4455 tomorrow at 2 PM',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'parsed' => [
                'title',
                'starts_at',
                'remind_at',
                'priority',
                'notes',
            ],
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('parsed.title'));
    }

    public function test_dashboard_brief_endpoint_provides_executive_summary(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        $response = $this->postJson('/ai/dashboard-brief', [
            'stats' => [
                'in_transit_dispatches' => 7,
                'pending_dispatches' => 2,
                'occupancyRate' => 78,
            ],
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'brief' => [
                'greeting',
                'key_metrics_summary',
                'operational_bullets',
                'recommended_action',
            ],
        ]);
        $this->assertTrue($response->json('success'));
        $this->assertCount(3, $response->json('brief.operational_bullets'));
    }
}
