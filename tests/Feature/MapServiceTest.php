<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MapServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_returns_real_coordinates_and_reuses_cached_results(): void
    {
        Http::fake(['*' => Http::response(['features' => [['geometry' => ['coordinates' => [85.32, 27.71]], 'properties' => ['name' => 'Kathmandu', 'country' => 'Nepal']]]])]);
        $this->actingAs(User::factory()->create(['is_active' => true]));
        $this->getJson('/maps/search?q=Kathmandu')->assertOk()->assertJsonPath('0.lat', 27.71)->assertJsonPath('0.lon', 85.32);
        $this->getJson('/maps/search?q=Kathmandu')->assertOk();
        Http::assertSentCount(1);
    }

    public function test_missing_location_is_not_replaced_with_fake_coordinates(): void
    {
        Http::fake(['*' => Http::response(['features' => []])]);
        $this->actingAs(User::factory()->create(['is_active' => true]));
        $this->getJson('/maps/search?q=Missing')->assertOk()->assertExactJson([]);
    }

    public function test_provider_failure_is_recoverable(): void
    {
        Http::fake(['*' => Http::response([], 503)]);
        $this->actingAs(User::factory()->create(['is_active' => true]));
        $this->getJson('/maps/search?q=Kathmandu')->assertStatus(503);
    }

    public function test_road_distance_is_derived_from_provider_and_invalid_points_are_rejected(): void
    {
        Http::fake(['*' => Http::response(['code' => 'Ok', 'routes' => [['distance' => 12340, 'duration' => 1200]]])]);
        $this->actingAs(User::factory()->create(['is_active' => true]));
        $this->postJson('/maps/route', ['points' => [['lat' => 27.71, 'lng' => 85.32], ['lat' => 27.67, 'lng' => 85.43]]])
            ->assertOk()->assertJsonPath('distance_km', 12.34);
        $this->postJson('/maps/route', ['points' => [['lat' => 999, 'lng' => 85.32], ['lat' => 27.67, 'lng' => 85.43]]])->assertUnprocessable();
    }

    public function test_anonymous_visitors_cannot_use_geocoder_proxy(): void
    {
        Http::fake();
        $this->getJson('/maps/search?q=Kathmandu')->assertUnauthorized();
        Http::assertNothingSent();
    }
}
