<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DispatchOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_all_dispatches_on_tracking_page()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        DispatchOrder::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get(route('tracking.index'));

        $response->assertOk();
        $response->assertViewHas('dispatches', function ($dispatches) {
            return $dispatches->total() === 3;
        });
    }

    public function test_client_sees_only_own_dispatches()
    {
        $client = User::factory()->create(['role' => 'client']);
        $otherClient = User::factory()->create(['role' => 'client']);

        $own = DispatchOrder::factory()->create(['client_id' => $client->id]);
        $other = DispatchOrder::factory()->create(['client_id' => $otherClient->id]);

        $response = $this->actingAs($client)->get(route('tracking.index'));

        $response->assertOk();
        $response->assertViewHas('dispatches', function ($dispatches) use ($own, $other) {
            return $dispatches->contains($own) && !$dispatches->contains($other);
        });
    }

    public function test_driver_sees_only_assigned_jobs()
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $assigned = DispatchOrder::factory()->create(['driver_id' => $driver->id]);
        $notAssigned = DispatchOrder::factory()->create(['driver_id' => null]);

        $response = $this->actingAs($driver)->get(route('tracking.index'));

        $response->assertOk();
        $response->assertViewHas('dispatches', function ($dispatches) use ($assigned, $notAssigned) {
            return $dispatches->contains($assigned) && !$dispatches->contains($notAssigned);
        });
    }
}