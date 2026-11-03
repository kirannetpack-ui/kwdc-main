<?php

namespace Tests\Feature;

use App\Models\DispatchOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DriverDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_dashboard_renders_with_jobs_pickups_and_available_dispatches(): void
    {
        $driver = User::factory()->create([
            'role' => 'driver',
            'is_driver' => true,
        ]);

        $client = User::factory()->create([
            'role' => 'client',
            'is_client' => true,
        ]);

        DispatchOrder::factory()->create([
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'pickup_address' => 'Kalimati, Kathmandu',
            'delivery_address' => 'Boudha, Kathmandu',
            'status' => 'assigned',
            'base_price' => 3200,
            'driver_earning' => 2400,
        ]);

        DispatchOrder::factory()->create([
            'client_id' => $client->id,
            'driver_id' => null,
            'pickup_address' => 'Birgunj Dry Port',
            'delivery_address' => 'Hetauda Industrial Area',
            'status' => 'pending',
            'base_price' => 14500,
        ]);

        DB::table('pickup_requests')->insert([
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'tracking_id' => 'PUP-TEST-001',
            'total_distance' => 8.5,
            'total_price' => 1800,
            'status' => 'assigned',
            'notes' => 'Pickup route near Asan, Kathmandu',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($driver)
            ->get(route('driver.dashboard'))
            ->assertOk()
            ->assertSee('Driver Dashboard')
            ->assertSee('Kalimati, Kathmandu')
            ->assertSee('Birgunj Dry Port')
            ->assertSee('Pickup route near Asan, Kathmandu');
    }
}
