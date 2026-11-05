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
            'area_sqft' => 500,
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

    public function test_warehouse_renewal_priority_and_future_space_follow_the_agreement(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-30 09:00'));
        \Illuminate\Support\Facades\Mail::fake();
        $tenant = User::factory()->create(['role' => 'client']);
        $applicant = User::factory()->create(['role' => 'client']);
        $warehouse = Warehouse::factory()->create(['status' => 'approved', 'area_sqft' => 500]);
        $agreement = \App\Models\WarehouseRequest::create([
            'warehouse_id' => $warehouse->id,
            'client_id' => $tenant->id,
            'space_required' => 400,
            'purpose' => 'Storage',
            'status' => 'assigned',
            'contract_end_date' => '2027-01-31',
        ]);
        $availability = app(\App\Services\WarehouseAvailability::class);
        $moveIn = \Illuminate\Support\Carbon::parse('2027-02-01');

        $this->artisan('warehouse:send-renewal-reminders')->assertSuccessful();
        $this->artisan('warehouse:send-renewal-reminders')->assertSuccessful();
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\ContractExpiryReminder::class, 1);
        $this->assertSame(100.0, $availability->snapshot($warehouse, $moveIn)['available']);
        $this->actingAs($tenant)->get(route('my-requests.show', $agreement))->assertOk()->assertSee('Request renewal');
        $this->actingAs($applicant)->post(route('my-requests.renewal', $agreement), ['decision' => 'renew'])->assertNotFound();

        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-12-31 09:00'));
        $this->actingAs($tenant)->post(route('my-requests.renewal', $agreement), ['decision' => 'renew'])->assertConflict();
        $this->assertSame(100.0, $availability->snapshot($warehouse)['available']);
        $this->assertSame(500.0, $availability->snapshot($warehouse, $moveIn)['available']);
        $this->actingAs($applicant)->get(route('my-requests.create'))->assertOk()->assertSee('open for pre-booking');
        $this->post(route('my-requests.store'), [
            'warehouse_id' => $warehouse->id,
            'required_area' => 400,
            'duration_months' => 2,
            'purpose' => 'Future storage',
            'preferred_start_date' => '2027-02-01',
        ])->assertRedirect(route('my-requests.index'));
        $this->assertDatabaseHas('warehouse_requests', ['client_id' => $applicant->id, 'status' => 'pending']);

        $this->travelTo($moveIn);
        $this->assertSame(100.0, $availability->snapshot($warehouse)['available']);
        $this->post(route('warehouse-requests.move-out', $agreement), ['space_cleared' => 1])->assertForbidden();
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $this->actingAs($admin)->post(route('warehouse-requests.move-out', $agreement), ['space_cleared' => 1])->assertRedirect();
        $this->assertSame(500.0, $availability->snapshot($warehouse)['available']);
    }

    public function test_warehouse_reservation_holds_capacity_and_activates_only_after_prepayment(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $tenant = User::factory()->create(['role' => 'client']);
        $warehouse = Warehouse::factory()->create(['status' => 'approved', 'area_sqft' => 500]);
        $request = \App\Models\WarehouseRequest::create([
            'warehouse_id' => $warehouse->id, 'client_id' => $tenant->id,
            'space_required' => 400, 'purpose' => 'Storage', 'status' => 'pending',
        ]);
        $other = $request->replicate();
        $other->save();
        $terms = ['start_date' => today()->toDateString(), 'end_date' => today()->addMonth()->toDateString(), 'monthly_rent' => 1000];
        $this->actingAs($admin)->post(route('warehouse-requests.reserve', $request), $terms)->assertRedirect();
        $this->post(route('warehouse-requests.reserve', $other), $terms)->assertConflict();

        $service = app(\App\Services\WarehouseReservations::class);
        $service->activate($request->id);
        $this->assertSame('reserved', $request->fresh()->status);
        $request->invoice()->sole()->markAsPaid('bank_transfer');
        $service->activate($request->id);
        $this->assertSame('assigned', $request->fresh()->status);
    }

    public function test_paid_warehouse_renewal_extends_the_existing_agreement(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
        $tenant = User::factory()->create(['role' => 'client']);
        $warehouse = Warehouse::factory()->create(['status' => 'approved', 'area_sqft' => 500]);
        $end = today()->addWeeks(6);
        $start = $end->copy()->addDay();
        $nextEnd = $start->copy()->addMonth();
        $agreement = \App\Models\WarehouseRequest::create([
            'warehouse_id' => $warehouse->id, 'client_id' => $tenant->id,
            'space_required' => 400, 'purpose' => 'Storage', 'status' => 'assigned',
            'contract_end_date' => $end,
        ]);
        $this->actingAs($tenant)->post(route('my-requests.renewal', $agreement), ['decision' => 'renew'])->assertRedirect();
        $this->actingAs($admin)->post(route('warehouse-requests.reserve', $agreement), [
            'renewal' => 1, 'start_date' => $start->toDateString(),
            'end_date' => $nextEnd->toDateString(), 'monthly_rent' => 2000,
        ])->assertRedirect();

        $renewal = \App\Models\WarehouseRequest::where('renewal_of_id', $agreement->id)->sole();
        $renewal->invoice()->sole()->markAsPaid('bank_transfer');
        $this->travelTo($start);
        app(\App\Services\WarehouseReservations::class)->activate($renewal->id);
        $this->assertSame($nextEnd->toDateString(), $agreement->fresh()->agreementEnd()->toDateString());
        $this->assertSame('completed', $renewal->fresh()->status);
    }
}
