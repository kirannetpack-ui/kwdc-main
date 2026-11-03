<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ProductionOperationsAndDetailPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_driver_detail_and_edit_page(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true, 'is_active' => true]);
        $driver = User::factory()->create(['role' => 'driver', 'is_driver' => true, 'is_active' => true]);

        $showResp = $this->actingAs($admin)->get("/admin/drivers/{$driver->id}");
        $showResp->assertStatus(200);
        $showResp->assertSee($driver->name);

        $editResp = $this->actingAs($admin)->get("/admin/drivers/{$driver->id}/edit");
        $editResp->assertStatus(200);
        $editResp->assertSee('Edit Driver Information');
    }

    public function test_admin_can_view_client_detail_page(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true, 'is_active' => true]);
        $client = User::factory()->create(['role' => 'client', 'is_client' => true, 'is_active' => true]);

        $response = $this->actingAs($admin)->get("/admin/clients/{$client->id}");
        $response->assertStatus(200);
        $response->assertSee($client->name);
    }

    public function test_invoice_authorization_and_detail_view(): void
    {
        Mail::fake();
        $client1 = User::factory()->create(['role' => 'client', 'is_client' => true, 'is_active' => true]);
        $client2 = User::factory()->create(['role' => 'client', 'is_client' => true, 'is_active' => true]);
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true, 'is_active' => true]);
        $owner = User::factory()->create(['role' => 'property_owner', 'is_property_owner' => true, 'is_active' => true]);

        $warehouse = Warehouse::create([
            'user_id' => $owner->id,
            'name' => 'Birgunj Depot',
            'location' => 'Birgunj',
            'status' => 'approved',
            'total_capacity' => 20000,
        ]);

        $request = WarehouseRequest::create([
            'client_id' => $client1->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'approved',
            'duration_months' => 6,
            'space_required' => 500,
        ]);

        $invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-001',
            'client_id' => $client1->id,
            'warehouse_request_id' => $request->id,
            'amount' => 15000,
            'status' => 'pending',
            'due_date' => now()->addDays(14),
        ]);

        // Owner client can view
        $respOwner = $this->actingAs($client1)->get("/invoices/{$invoice->id}");
        $respOwner->assertStatus(200);
        $respOwner->assertSee('INV-TEST-001');

        // Admin can view
        $respAdmin = $this->actingAs($admin)->get("/invoices/{$invoice->id}");
        $respAdmin->assertStatus(200);

        // Other client receives 403 Forbidden
        $respOther = $this->actingAs($client2)->get("/invoices/{$invoice->id}");
        $respOther->assertStatus(403);
    }

    public function test_warehouse_detail_page_loads_with_leaflet_map(): void
    {
        Mail::fake();
        $owner = User::factory()->create(['role' => 'property_owner', 'is_property_owner' => true, 'is_active' => true]);
        $warehouse = Warehouse::create([
            'user_id' => $owner->id,
            'name' => 'Kathmandu Central Depo',
            'location' => 'Teku, Kathmandu',
            'address' => 'Teku, Kathmandu',
            'type' => 'building',
            'total_capacity' => 10000,
            'latitude' => 27.6980,
            'longitude' => 85.3120,
            'status' => 'approved',
            'is_approved' => true,
        ]);

        $client = User::factory()->create(['role' => 'client', 'is_client' => true, 'is_active' => true]);
        $response = $this->actingAs($client)->get("/warehouses/{$warehouse->id}");
        $response->assertStatus(200);
        $response->assertSee('Kathmandu Central Depo');
        $response->assertSee('warehouseLocationMap');
    }

    public function test_admin_cannot_repeat_warehouse_approval(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true, 'is_active' => true]);
        $owner = User::factory()->create(['role' => 'property_owner', 'is_property_owner' => true, 'is_active' => true]);
        $warehouse = Warehouse::create([
            'user_id' => $owner->id,
            'name' => 'Patan Buffer Warehouse',
            'location' => 'Patan, Lalitpur',
            'status' => 'pending',
            'total_capacity' => 5000,
        ]);

        // First approval succeeds
        $resp1 = $this->actingAs($admin)->post("/admin/approve/{$warehouse->id}");
        $resp1->assertRedirect();
        $this->assertSame('approved', $warehouse->fresh()->status);

        // Repeated approval returns 409 Conflict
        $resp2 = $this->actingAs($admin)->post("/admin/approve/{$warehouse->id}");
        $resp2->assertStatus(409);
    }

    public function test_admin_can_view_dispatch_orders_index(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true, 'is_active' => true]);
        $client = User::factory()->create(['role' => 'client', 'is_client' => true, 'is_active' => true]);
        $driver = User::factory()->create(['role' => 'driver', 'is_driver' => true, 'is_active' => true]);

        $order = \App\Models\DispatchOrder::create([
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'tracking_id' => 'TRK-TEST-999',
            'pickup_address' => 'Kathmandu Valley Hub',
            'delivery_address' => 'Pokhara Depot',
            'base_price' => 5000,
            'grand_total' => 5650,
            'status' => 'in_transit',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dispatch');
        $response->assertStatus(200);
        $response->assertSee('All Dispatch Orders');
        $response->assertSee('TRK-TEST-999');
        $response->assertSee($client->name);
    }
}
