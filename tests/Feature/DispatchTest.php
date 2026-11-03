<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DispatchOrder;
use App\Models\DeliveryStop;
use App\Models\Warehouse;
use App\Models\WarehouseRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_create_dispatch()
    {
        // 1. Create a client user
        $client = User::factory()->create(['role' => 'client']);

        // 2. Send POST request (the controller likely ignores delivery_address if not stored)
        $response = $this->actingAs($client)->post(route('dispatch.store'), [
            'pickup_address' => '123 Pickup St, Kathmandu',
            'delivery_address' => '456 Delivery Ave, Kathmandu', // sent, but not stored in this table
            'total_distance' => 15,   // use the correct field name expected by your controller
            'base_price' => 500,
        ]);

        // 3. Assertions
        $response->assertRedirect(route('dispatch.index'));
        $this->assertDatabaseHas('dispatch_orders', [
            'client_id' => $client->id,
            'pickup_address' => '123 Pickup St, Kathmandu',
            'status' => 'pending',
            'total_distance' => 15,
            'base_price' => 500,
        ]);

        $dispatch = DispatchOrder::first();
        $this->assertNotNull($dispatch->tracking_id);
    }

    public function test_unrelated_authenticated_users_cannot_view_or_mutate_dispatches()
    {
        $client = User::factory()->create(['role' => 'client']);
        $driver = User::factory()->create(['role' => 'driver']);
        $otherUser = User::factory()->create(['role' => 'client']);

        $dispatch = DispatchOrder::factory()->create([
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'status' => 'pending',
            'client_rating' => null,
        ]);

        $stop = DeliveryStop::create([
            'dispatch_order_id' => $dispatch->id,
            'stop_number' => 1,
            'recipient_name' => 'Receiver',
            'recipient_phone' => '9800000012',
            'address' => 'Delivery Point',
            'status' => 'pending',
        ]);

        $this->actingAs($otherUser)
            ->get(route('dispatch.show', $dispatch->id))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->postJson(route('dispatch.update-status', $dispatch->id), ['status' => 'delivered'])
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->postJson(route('dispatch.rate', $dispatch->id), ['rating' => 5])
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->postJson(route('dispatch.enable-tracking', $dispatch->id))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->postJson(route('dispatch.stop-status', $stop->id), ['status' => 'delivered'])
            ->assertForbidden();

        $this->assertDatabaseHas('dispatch_orders', [
            'id' => $dispatch->id,
            'status' => 'pending',
            'client_rating' => null,
        ]);

        $this->assertDatabaseHas('delivery_stops', [
            'id' => $stop->id,
            'status' => 'pending',
        ]);
    }

    public function test_client_and_assigned_driver_keep_expected_dispatch_access()
    {
        $client = User::factory()->create(['role' => 'client']);
        $driver = User::factory()->create(['role' => 'driver']);

        $dispatch = DispatchOrder::factory()->create([
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'status' => 'pending',
        ]);

        $stop = DeliveryStop::create([
            'dispatch_order_id' => $dispatch->id,
            'stop_number' => 1,
            'recipient_name' => 'Receiver',
            'recipient_phone' => '9800000013',
            'address' => 'Delivery Point',
            'status' => 'pending',
        ]);

        $this->actingAs($client)
            ->get(route('dispatch.show', $dispatch->id))
            ->assertOk();

        $this->actingAs($client)
            ->postJson(route('dispatch.rate', $dispatch->id), ['rating' => 4])
            ->assertOk();

        $this->actingAs($driver)
            ->postJson(route('dispatch.update-status', $dispatch->id), ['status' => 'delivered'])
            ->assertOk();

        $this->actingAs($driver)
            ->postJson(route('dispatch.stop-status', $stop->id), ['status' => 'delivered'])
            ->assertOk();

        $this->assertDatabaseHas('dispatch_orders', [
            'id' => $dispatch->id,
            'status' => 'delivered',
            'client_rating' => 4,
        ]);

        $this->assertDatabaseHas('delivery_stops', [
            'id' => $stop->id,
            'status' => 'delivered',
        ]);
    }

    public function test_dispatch_tracking_requires_participant_access_or_valid_token()
    {
        $client = User::factory()->create(['role' => 'client']);
        $driver = User::factory()->create(['role' => 'driver']);
        $otherUser = User::factory()->create(['role' => 'client']);

        $dispatch = DispatchOrder::factory()->create([
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'tracking_enabled' => true,
            'tracking_token' => 'secure-tracking-token',
        ]);

        $this->actingAs($client)
            ->get(route('dispatch.track', $dispatch->id))
            ->assertOk()
            ->assertSee($dispatch->tracking_id);

        $this->actingAs($otherUser)
            ->get(route('dispatch.track', $dispatch->id))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->get(route('dispatch.track', ['id' => $dispatch->id, 'token' => 'wrong-token']))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->get(route('dispatch.track', ['id' => $dispatch->id, 'token' => 'secure-tracking-token']))
            ->assertOk()
            ->assertSee($dispatch->tracking_id);
    }

    public function test_enable_tracking_returns_tokenized_tracking_url()
    {
        $client = User::factory()->create(['role' => 'client']);
        $dispatch = DispatchOrder::factory()->create([
            'client_id' => $client->id,
            'tracking_enabled' => false,
            'tracking_token' => null,
        ]);

        $response = $this->actingAs($client)
            ->postJson(route('dispatch.enable-tracking', $dispatch->id))
            ->assertOk()
            ->assertJsonPath('success', true);

        $dispatch->refresh();

        $this->assertTrue((bool) $dispatch->tracking_enabled);
        $this->assertNotEmpty($dispatch->tracking_token);
        $this->assertStringContainsString('token=' . $dispatch->tracking_token, $response->json('tracking_url'));
    }

    public function test_client_cannot_create_dispatch_for_another_client()
    {
        $client = User::factory()->create(['role' => 'client']);
        $otherClient = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($client)->post(route('dispatch.store'), [
            'client_id' => $otherClient->id,
            'pickup_address' => '123 Pickup St, Kathmandu',
            'delivery_address' => '456 Delivery Ave, Kathmandu',
            'total_distance' => 15,
            'base_price' => 500,
        ]);

        $response->assertRedirect(route('dispatch.index'));

        $this->assertDatabaseHas('dispatch_orders', [
            'client_id' => $client->id,
            'pickup_address' => '123 Pickup St, Kathmandu',
        ]);

        $this->assertDatabaseMissing('dispatch_orders', [
            'client_id' => $otherClient->id,
            'pickup_address' => '123 Pickup St, Kathmandu',
        ]);
    }

    public function test_admin_can_create_dispatch_for_selected_client()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = User::factory()->create(['role' => 'client']);

        $response = $this->actingAs($admin)->post(route('dispatch.store'), [
            'client_id' => $client->id,
            'pickup_address' => 'Admin Pickup St, Kathmandu',
            'delivery_address' => 'Client Delivery Ave, Kathmandu',
            'total_distance' => 10,
            'base_price' => 400,
        ]);

        $response->assertRedirect(route('dispatch.index'));

        $this->assertDatabaseHas('dispatch_orders', [
            'client_id' => $client->id,
            'pickup_address' => 'Admin Pickup St, Kathmandu',
        ]);
    }

    public function test_client_cannot_open_dispatch_create_for_another_clients_warehouse_request()
    {
        $client = User::factory()->create(['role' => 'client']);
        $otherClient = User::factory()->create(['role' => 'client']);
        $warehouse = Warehouse::factory()->create();

        $otherRequest = WarehouseRequest::create([
            'client_id' => $otherClient->id,
            'warehouse_id' => $warehouse->id,
            'required_area' => 100,
            'duration_months' => 2,
            'purpose' => 'Consumer goods',
            'status' => 'assigned',
        ]);

        $this->actingAs($client)
            ->get(route('dispatch.create', $otherRequest->id))
            ->assertNotFound();
    }
}
