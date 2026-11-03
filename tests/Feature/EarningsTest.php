<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DispatchOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EarningsTest extends TestCase
{
    use RefreshDatabase;

    public function test_earning_is_created_when_dispatch_is_delivered()
    {
        $driver = User::factory()->create(['role' => 'driver']);
        $dispatch = DispatchOrder::factory()
            ->inProgress()
            ->create([
                'driver_id' => $driver->id,
                'base_price' => 1000,
            ]);

        $response = $this->actingAs($driver)
            ->postJson(route('dispatch.update-status', $dispatch->id), [
                'status' => 'delivered'
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('partner_earnings', [
            'partner_id' => $driver->id,
            'order_type' => 'dispatch',
            'order_id' => $dispatch->id,
            'amount' => 1000 * 0.75,
            'status' => 'pending',
        ]);
    }
}