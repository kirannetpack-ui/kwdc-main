<?php

namespace Tests\Feature;

use App\Models\SecurityAgency;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseRequest;
use App\Models\Box;
use App\Models\DeliveryStop;
use App\Models\DispatchOrder;
use App\Models\Equipment;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateDocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouse_owner_can_download_their_private_document(): void
    {
        Storage::fake('private_uploads');

        $owner = User::factory()->create(['role' => 'property_owner']);
        $path = 'warehouses/documents/owner-proof.pdf';
        Storage::disk('private_uploads')->put($path, 'private warehouse document');

        Warehouse::factory()->create([
            'user_id' => $owner->id,
            'location' => 'Kathmandu',
            'ownership_document' => $path,
        ]);

        $this->actingAs($owner)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertOk();
    }

    public function test_other_users_cannot_download_private_documents(): void
    {
        Storage::fake('private_uploads');

        $owner = User::factory()->create(['role' => 'property_owner']);
        $otherUser = User::factory()->create(['role' => 'client']);
        $path = 'warehouses/documents/owner-proof.pdf';
        Storage::disk('private_uploads')->put($path, 'private warehouse document');

        Warehouse::factory()->create([
            'user_id' => $owner->id,
            'location' => 'Kathmandu',
            'ownership_document' => $path,
        ]);

        $this->actingAs($otherUser)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertForbidden();
    }

    public function test_admin_can_download_security_agency_private_document(): void
    {
        Storage::fake('private_uploads');

        $admin = User::factory()->create(['role' => 'admin']);
        $agencyUser = User::factory()->create(['role' => 'security_agency']);
        $path = 'security-documents/registration.pdf';
        Storage::disk('private_uploads')->put($path, 'private agency document');

        SecurityAgency::create([
            'user_id' => $agencyUser->id,
            'agency_name' => 'Private Docs Security',
            'registration_number' => 'PDS-001',
            'registration_certificate_path' => $path,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertOk();
    }

    public function test_private_document_route_rejects_path_traversal(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get('/private-documents/../.env')
            ->assertStatus(400);
    }

    public function test_box_client_can_download_their_private_document(): void
    {
        Storage::fake('private_uploads');

        $client = User::factory()->create(['role' => 'client']);
        $warehouse = Warehouse::factory()->create();
        $path = 'boxes/documents/invoices/client-invoice.pdf';
        Storage::disk('private_uploads')->put($path, 'private box invoice');

        Box::create([
            'batch_number' => 'BATCH-CLIENT-001',
            'box_number' => 1,
            'qr_code' => 'box-client-qr',
            'warehouse_id' => $warehouse->id,
            'client_id' => $client->id,
            'invoice_document' => $path,
        ]);

        $this->actingAs($client)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertOk();
    }

    public function test_warehouse_owner_can_download_box_private_document(): void
    {
        Storage::fake('private_uploads');

        $owner = User::factory()->create(['role' => 'property_owner']);
        $client = User::factory()->create(['role' => 'client']);
        $warehouse = Warehouse::factory()->create([
            'user_id' => $owner->id,
        ]);
        $path = 'boxes/documents/packing_lists/owner-access.pdf';
        Storage::disk('private_uploads')->put($path, 'private box packing list');

        Box::create([
            'batch_number' => 'BATCH-OWNER-001',
            'box_number' => 1,
            'qr_code' => 'box-owner-qr',
            'warehouse_id' => $warehouse->id,
            'client_id' => $client->id,
            'packing_list_document' => $path,
        ]);

        $this->actingAs($owner)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertOk();
    }

    public function test_other_users_cannot_download_box_private_documents(): void
    {
        Storage::fake('private_uploads');

        $client = User::factory()->create(['role' => 'client']);
        $otherUser = User::factory()->create(['role' => 'client']);
        $warehouse = Warehouse::factory()->create();
        $path = 'boxes/documents/insurance/private-insurance.pdf';
        Storage::disk('private_uploads')->put($path, 'private box insurance');

        Box::create([
            'batch_number' => 'BATCH-OTHER-001',
            'box_number' => 1,
            'qr_code' => 'box-other-qr',
            'warehouse_id' => $warehouse->id,
            'client_id' => $client->id,
            'insurance_document' => $path,
        ]);

        $this->actingAs($otherUser)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertForbidden();
    }

    public function test_equipment_owner_can_download_their_private_document(): void
    {
        Storage::fake('private_uploads');

        $owner = User::factory()->create(['role' => 'equipment_owner']);
        $path = 'equipment/documents/registration.pdf';
        Storage::disk('private_uploads')->put($path, 'private equipment document');

        Equipment::create([
            'owner_id' => $owner->id,
            'name' => 'Excavator',
            'type' => 'excavator',
            'location' => 'Kathmandu',
            'registration_doc' => $path,
        ]);

        $this->actingAs($owner)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertOk();
    }

    public function test_other_users_cannot_download_equipment_private_documents(): void
    {
        Storage::fake('private_uploads');

        $owner = User::factory()->create(['role' => 'equipment_owner']);
        $otherUser = User::factory()->create(['role' => 'client']);
        $path = 'equipment/documents/insurance.pdf';
        Storage::disk('private_uploads')->put($path, 'private equipment insurance');

        Equipment::create([
            'owner_id' => $owner->id,
            'name' => 'Loader',
            'type' => 'loader',
            'location' => 'Lalitpur',
            'insurance_doc' => $path,
        ]);

        $this->actingAs($otherUser)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertForbidden();
    }

    public function test_client_can_download_their_warehouse_request_private_document(): void
    {
        Storage::fake('private_uploads');

        $client = User::factory()->create(['role' => 'client']);
        $warehouse = Warehouse::factory()->create();
        $path = 'warehouse-requests/documents/invoices/request-invoice.pdf';
        Storage::disk('private_uploads')->put($path, 'private warehouse request invoice');

        WarehouseRequest::create([
            'client_id' => $client->id,
            'warehouse_id' => $warehouse->id,
            'required_area' => 100,
            'duration_months' => 2,
            'purpose' => 'Consumer goods',
            'invoice_path' => $path,
            'status' => 'pending',
        ]);

        $this->actingAs($client)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertOk();
    }

    public function test_other_users_cannot_download_warehouse_request_private_documents(): void
    {
        Storage::fake('private_uploads');

        $client = User::factory()->create(['role' => 'client']);
        $otherUser = User::factory()->create(['role' => 'client']);
        $warehouse = Warehouse::factory()->create();
        $path = 'warehouse-requests/documents/packing-lists/request-packing-list.pdf';
        Storage::disk('private_uploads')->put($path, 'private warehouse request packing list');

        WarehouseRequest::create([
            'client_id' => $client->id,
            'warehouse_id' => $warehouse->id,
            'required_area' => 100,
            'duration_months' => 2,
            'purpose' => 'Consumer goods',
            'packing_list_path' => $path,
            'status' => 'pending',
        ]);

        $this->actingAs($otherUser)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertForbidden();
    }

    public function test_box_documents_endpoint_returns_private_urls_only_to_authorized_users(): void
    {
        $client = User::factory()->create(['role' => 'client']);
        $otherUser = User::factory()->create(['role' => 'client']);
        $warehouse = Warehouse::factory()->create();

        $box = Box::create([
            'batch_number' => 'BATCH-DOCS-001',
            'box_number' => 1,
            'qr_code' => 'box-docs-qr',
            'warehouse_id' => $warehouse->id,
            'client_id' => $client->id,
            'invoice_document' => 'boxes/documents/invoices/private-invoice.pdf',
            'other_documents' => ['boxes/documents/others/private-extra.pdf'],
        ]);

        $response = $this->actingAs($client)
            ->getJson(route('boxes.documents', $box->id))
            ->assertOk();

        $response->assertJsonPath('invoice_document', route('documents.private.show', ['path' => 'boxes/documents/invoices/private-invoice.pdf']));
        $response->assertJsonPath('other_documents.0', route('documents.private.show', ['path' => 'boxes/documents/others/private-extra.pdf']));
        $this->assertStringNotContainsString('/storage/', $response->getContent());

        $this->actingAs($otherUser)
            ->getJson(route('boxes.documents', $box->id))
            ->assertForbidden();
    }

    public function test_dispatch_client_driver_and_admin_can_download_delivery_stop_invoice_document(): void
    {
        Storage::fake('private_uploads');

        $client = User::factory()->create(['role' => 'client']);
        $driver = User::factory()->create(['role' => 'driver']);
        $admin = User::factory()->create(['role' => 'admin']);
        $path = 'dispatch-stops/documents/invoices/stop-invoice.pdf';
        Storage::disk('private_uploads')->put($path, 'private dispatch invoice');

        $dispatch = DispatchOrder::create([
            'client_id' => $client->id,
            'driver_id' => $driver->id,
            'tracking_id' => 'STOP-DOC-001',
            'pickup_address' => 'Pickup Point',
            'delivery_address' => 'Delivery Point',
            'status' => 'pending',
        ]);

        DeliveryStop::create([
            'dispatch_order_id' => $dispatch->id,
            'stop_number' => 1,
            'recipient_name' => 'Receiver',
            'recipient_phone' => '9800000010',
            'address' => 'Delivery Point',
            'status' => 'pending',
            'invoice_document' => $path,
        ]);

        $this->actingAs($client)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertOk();

        $this->actingAs($driver)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertOk();
    }

    public function test_other_users_cannot_download_delivery_stop_invoice_documents(): void
    {
        Storage::fake('private_uploads');

        $client = User::factory()->create(['role' => 'client']);
        $otherUser = User::factory()->create(['role' => 'client']);
        $path = 'dispatch-stops/documents/invoices/private-stop-invoice.pdf';
        Storage::disk('private_uploads')->put($path, 'private dispatch invoice');

        $dispatch = DispatchOrder::create([
            'client_id' => $client->id,
            'tracking_id' => 'STOP-DOC-002',
            'pickup_address' => 'Pickup Point',
            'delivery_address' => 'Delivery Point',
            'status' => 'pending',
        ]);

        DeliveryStop::create([
            'dispatch_order_id' => $dispatch->id,
            'stop_number' => 1,
            'recipient_name' => 'Receiver',
            'recipient_phone' => '9800000011',
            'address' => 'Delivery Point',
            'status' => 'pending',
            'invoice_document' => $path,
        ]);

        $this->actingAs($otherUser)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertForbidden();
    }

    public function test_driver_and_admin_can_download_vehicle_private_documents(): void
    {
        Storage::fake('private_uploads');

        $driver = User::factory()->create(['role' => 'driver']);
        $admin = User::factory()->create(['role' => 'admin']);
        $path = 'vehicle-documents/insurance/driver-insurance.pdf';
        Storage::disk('private_uploads')->put($path, 'private vehicle insurance');

        Vehicle::create([
            'driver_id' => $driver->id,
            'vehicle_number' => 'BA 1 KHA 1001',
            'vehicle_type' => 'Truck',
            'capacity' => 1000,
            'capacity_unit' => 'kg',
            'registration_number' => 'VEH-TEST-001',
            'insurance_file_path' => $path,
        ]);

        $this->actingAs($driver)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertOk();
    }

    public function test_other_users_cannot_download_vehicle_private_documents(): void
    {
        Storage::fake('private_uploads');

        $driver = User::factory()->create(['role' => 'driver']);
        $otherUser = User::factory()->create(['role' => 'client']);
        $path = 'vehicle-documents/blue-book/private-blue-book.pdf';
        Storage::disk('private_uploads')->put($path, 'private vehicle blue book');

        Vehicle::create([
            'driver_id' => $driver->id,
            'vehicle_number' => 'BA 1 KHA 1002',
            'vehicle_type' => 'Pickup',
            'capacity' => 500,
            'capacity_unit' => 'kg',
            'registration_number' => 'VEH-TEST-002',
            'blue_book_file_path' => $path,
        ]);

        $this->actingAs($otherUser)
            ->get(route('documents.private.show', ['path' => $path]))
            ->assertForbidden();
    }
}
