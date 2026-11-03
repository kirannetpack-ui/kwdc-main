<?php

namespace Tests\Feature;

use App\Models\DispatchOrder;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PdfAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_unrelated_users_cannot_download_warehouse_or_dispatch_pdfs(): void
    {
        $owner = User::factory()->create(['role' => 'property_owner']);
        $client = User::factory()->create(['role' => 'client']);
        $stranger = User::factory()->create(['role' => 'client']);

        $warehouse = Warehouse::create([
            'user_id' => $owner->id,
            'name' => 'Private Warehouse',
            'location' => 'Kathmandu',
            'address' => 'Private Road 1',
            'latitude' => 27.7172,
            'longitude' => 85.3240,
            'status' => 'approved',
        ]);

        $dispatch = DispatchOrder::create([
            'client_id' => $client->id,
            'warehouse_id' => $warehouse->id,
            'tracking_id' => 'PDF-SECURE-001',
            'pickup_address' => 'Pickup Point',
            'delivery_address' => 'Delivery Point',
            'status' => 'pending',
        ]);

        $this->actingAs($stranger)
            ->get(route('pdf.warehouse', $warehouse->id))
            ->assertForbidden();

        $this->actingAs($stranger)
            ->get(route('pdf.dispatch', $dispatch->id))
            ->assertForbidden();
    }

    public function test_dead_invoice_pdf_route_is_not_registered(): void
    {
        $this->assertFalse(Route::has('pdf.invoice'));
    }
}
