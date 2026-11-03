<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DispatchOrder;
use App\Events\LocationUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LocationUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_updates_location_and_broadcasts_event()
    {
        Event::fake(); // Prevent real broadcasting during test

        // 1. Create driver and dispatch
        $driver = User::factory()->create(['role' => 'driver']);
        $dispatch = DispatchOrder::factory()->create([
            'driver_id' => $driver->id,
            'status' => 'in_progress',
        ]);

        // 2. Send location update
        $response = $this->actingAs($driver)
            ->postJson(route('dispatch.update-location', $dispatch->id), [
                'latitude' => 27.7172,
                'longitude' => 85.3240,
            ]);

        // 3. Assert HTTP response
        $response->assertOk()
                 ->assertJson(['success' => true]);

        // 4. Assert database updated
        $this->assertDatabaseHas('dispatch_orders', [
            'id' => $dispatch->id,
            'current_latitude' => 27.7172,
            'current_longitude' => 85.3240,
        ]);

        // 5. Assert event was dispatched
        Event::assertDispatched(LocationUpdated::class, function ($event) use ($dispatch) {
            return $event->dispatchId === $dispatch->id
                && $event->latitude === 27.7172
                && $event->longitude === 85.3240;
        });
    }

    public function test_unauthenticated_user_cannot_update_location()
    {
        $dispatch = DispatchOrder::factory()->create();
        $response = $this->postJson(route('dispatch.update-location', $dispatch->id), [
            'latitude' => 0, 'longitude' => 0,
        ]);
        $response->assertUnauthorized();
    }

    public function test_driver_cannot_update_location_for_unassigned_dispatch()
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $dispatch = DispatchOrder::factory()->create(['driver_id' => null]); // not assigned

        $response = $this->actingAs($driver)
            ->postJson(route('dispatch.update-location', $dispatch->id), [
                'latitude' => 27.7,
                'longitude' => 85.3,
            ]);

        $response->assertForbidden(); // or 403
    }
}