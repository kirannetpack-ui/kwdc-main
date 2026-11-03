<?php

namespace Tests\Feature;

use App\Models\DispatchOrder;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClientLogisticsFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_dispatch_and_pickup_pages_render(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
            'is_client' => true,
        ]);

        DispatchOrder::factory()->create([
            'client_id' => $client->id,
            'pickup_address' => 'Kalimati Tarkari Bazaar, Kathmandu',
            'delivery_address' => 'Patan Industrial Estate, Lalitpur',
            'base_price' => 2500,
            'total_distance' => 9.5,
            'status' => 'pending',
        ]);

        $this->actingAs($client)
            ->get(route('dispatch.index'))
            ->assertOk()
            ->assertSee('Dispatch Orders')
            ->assertSee('Kalimati Tarkari Bazaar');

        $this->actingAs($client)
            ->get(route('pickup.index'))
            ->assertOk()
            ->assertSee('Pickup Requests');
    }

    public function test_client_can_create_pickup_without_driver_assignment(): void
    {
        $client = User::factory()->create([
            'role' => 'client',
            'is_client' => true,
            'phone' => '9800000000',
        ]);

        $response = $this->actingAs($client)->post(route('pickup.store'), [
            'pickup_address' => 'Asan Bazaar, Kathmandu',
            'destination_address' => 'Boudha, Kathmandu',
            'contact_person' => 'Sita Shrestha',
            'contact_phone' => '9811111111',
            'description' => 'Local garment cartons',
            'estimated_boxes' => 6,
            'total_distance' => 7.5,
            'total_price' => 1500,
            'bill_type' => 'regular',
        ]);

        $pickup = \App\Models\PickupRequest::first();

        $response->assertRedirect(route('pickup.show', $pickup->id));
        $this->assertDatabaseHas('pickup_requests', [
            'client_id' => $client->id,
            'pickup_address' => 'Asan Bazaar, Kathmandu',
            'destination_address' => 'Boudha, Kathmandu',
            'driver_id' => null,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('pickup_stops', [
            'pickup_request_id' => $pickup->id,
            'address' => 'Asan Bazaar, Kathmandu',
            'contact_name' => 'Sita Shrestha',
        ]);

        $this->actingAs($client)
            ->get(route('pickup.show', $pickup->id))
            ->assertOk()
            ->assertSee('Asan Bazaar')
            ->assertSee('Awaiting assignment');
    }

    public function test_client_warehouse_request_documents_are_stored_privately(): void
    {
        Storage::fake('private_uploads');

        $client = User::factory()->create([
            'role' => 'client',
            'is_client' => true,
            'phone' => '9800000000',
        ]);
        $warehouse = Warehouse::factory()->create([
            'status' => 'approved',
        ]);

        $response = $this->actingAs($client)->post(route('my-requests.store'), [
            'warehouse_id' => $warehouse->id,
            'required_area' => 120,
            'duration_months' => 3,
            'purpose' => 'Store electronics inventory',
            'contact_person' => 'Sita Shrestha',
            'contact_phone' => '9811111111',
            'invoice' => UploadedFile::fake()->create('invoice.pdf', 12, 'application/pdf'),
            'packing_list' => UploadedFile::fake()->create('packing-list.pdf', 12, 'application/pdf'),
            'insurance' => UploadedFile::fake()->create('insurance.pdf', 12, 'application/pdf'),
        ]);

        $request = \App\Models\WarehouseRequest::first();

        $response->assertRedirect(route('my-requests.index'));
        $this->assertNotNull($request->invoice_path);
        $this->assertNotNull($request->packing_list_path);
        $this->assertNotNull($request->insurance_path);
        $this->assertEquals(120.0, (float) $request->space_required);
        Storage::disk('private_uploads')->assertExists($request->invoice_path);
        Storage::disk('private_uploads')->assertExists($request->packing_list_path);
        Storage::disk('private_uploads')->assertExists($request->insurance_path);
        Storage::disk('public')->assertMissing($request->invoice_path);
    }
}
