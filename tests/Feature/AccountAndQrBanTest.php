<?php

namespace Tests\Feature;

use App\Models\Box;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountAndQrBanTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $client;
    protected Warehouse $warehouse;
    protected WarehouseRequest $warehouseRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        $this->client = User::factory()->create([
            'role' => 'client',
            'is_client' => true,
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => Hash::make('secret123'),
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'KTM Main Hub',
            'location' => 'Kathmandu Ring Road',
            'user_id' => $this->admin->id,
            'status' => 'approved',
        ]);

        $this->warehouseRequest = WarehouseRequest::create([
            'client_id' => $this->client->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'approved',
            'space_required' => 100,
            'purpose' => 'Storage',
        ]);
    }

    public function test_admin_can_ban_a_user_account_with_a_reason(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.ban', $this->client->id), [
            'ban_reason' => 'Fraudulent documentation submitted for high-value clearance.',
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $this->client->refresh();

        $this->assertTrue($this->client->isBanned());
        $this->assertEquals('Fraudulent documentation submitted for high-value clearance.', $this->client->ban_reason);
        $this->assertNotNull($this->client->banned_at);
    }

    public function test_banned_user_cannot_log_in_and_sees_ban_reason(): void
    {
        $this->client->ban('Suspected contraband logistics activity.');

        $response = $this->post(route('login'), [
            'email' => $this->client->email,
            'password' => 'secret123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('Suspected contraband logistics activity.', session('errors')->first('email'));
    }

    public function test_active_session_of_banned_user_is_aborted_by_middleware(): void
    {
        $this->client->ban('Compliance violation.');

        $response = $this->actingAs($this->client)->get('/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_cannot_ban_themselves(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.users.ban', $this->admin->id), [
            'ban_reason' => 'Accidental self-ban',
        ]);

        $response->assertRedirect(route('admin.roles.index'));
        $response->assertSessionHas('error');
        $this->admin->refresh();
        $this->assertFalse($this->admin->isBanned());
    }

    public function test_admin_can_unban_user_account(): void
    {
        $this->client->ban('Temporary security hold.');
        $this->assertTrue($this->client->isBanned());

        $response = $this->actingAs($this->admin)->post(route('admin.users.unban', $this->client->id));

        $response->assertRedirect(route('admin.roles.index'));
        $this->client->refresh();

        $this->assertFalse($this->client->isBanned());
        $this->assertNull($this->client->ban_reason);
        $this->assertNull($this->client->banned_at);
    }

    public function test_admin_can_ban_invoice_qr_code(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-202611-9901',
            'client_id' => $this->client->id,
            'warehouse_request_id' => $this->warehouseRequest->id,
            'amount' => 50000.00,
            'grand_total' => 50000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'due_date' => now()->addDays(7),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.invoices.ban-qr', $invoice->id), [
            'ban_reason' => 'Unpaid customs surcharge under investigation.',
        ]);

        $response->assertRedirect();
        $invoice->refresh();

        $this->assertTrue($invoice->isBanned());
        $this->assertEquals('Unpaid customs surcharge under investigation.', $invoice->ban_reason);
        $this->assertNotNull($invoice->banned_at);
    }

    public function test_verify_page_displays_revoked_warning_for_banned_invoice(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-202611-9902',
            'client_id' => $this->client->id,
            'warehouse_request_id' => $this->warehouseRequest->id,
            'amount' => 12000.00,
            'grand_total' => 12000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'due_date' => now()->addDays(7),
            'is_banned' => true,
            'ban_reason' => 'Court order cargo attachment.',
            'banned_at' => now(),
        ]);

        $response = $this->get(route('invoice.verify', $invoice->invoice_number));

        $response->assertStatus(200);
        $response->assertSee('QR CODE BANNED');
        $response->assertSee('CARGO RELEASE');
        $response->assertSee('Court order cargo attachment.');
        $response->assertSee('Muluki Civil Code 2074');
    }

    public function test_admin_can_unban_invoice_qr(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => 'INV-202611-9903',
            'client_id' => $this->client->id,
            'warehouse_request_id' => $this->warehouseRequest->id,
            'amount' => 15000.00,
            'grand_total' => 15000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'due_date' => now()->addDays(7),
            'is_banned' => true,
            'ban_reason' => 'Audit completed.',
            'banned_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.invoices.unban-qr', $invoice->id));

        $response->assertRedirect();
        $invoice->refresh();

        $this->assertFalse($invoice->isBanned());
        $this->assertNull($invoice->ban_reason);
    }

    public function test_admin_can_ban_box_qr_code_and_api_reflects_it(): void
    {
        $box = Box::create([
            'batch_number' => 'BATCH-TEST-001',
            'qr_code' => 'QR-TEST-001',
            'box_number' => 1,
            'total_boxes' => 5,
            'invoice_number' => 'INV-998877',
            'shipper_name' => 'Kathmandu Importers Pvt Ltd',
            'warehouse_id' => $this->warehouse->id,
            'client_id' => $this->client->id,
            'entry_date' => now(),
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.boxes.ban-qr', $box->id), [
            'ban_reason' => 'Suspected undeclared hazardous materials in parcel.',
        ]);

        $response->assertRedirect();
        $box->refresh();

        $this->assertTrue($box->isBanned());
        $this->assertEquals('Suspected undeclared hazardous materials in parcel.', $box->ban_reason);

        // Test getQR API returns is_banned => true and nullifies qr_data
        $qrResponse = $this->actingAs($this->client)->getJson(route('boxes.qr', $box->id));
        $qrResponse->assertStatus(200);
        $qrResponse->assertJson([
            'is_banned' => true,
            'ban_reason' => 'Suspected undeclared hazardous materials in parcel.',
            'qr_data' => null,
            'status' => 'banned',
        ]);
    }

    public function test_box_tracking_page_shows_banned_cargo_warning(): void
    {
        $box = Box::create([
            'batch_number' => 'BATCH-TEST-002',
            'qr_code' => 'QR-TEST-002',
            'box_number' => 2,
            'total_boxes' => 5,
            'invoice_number' => 'INV-998878',
            'shipper_name' => 'Himalayan Cargo',
            'warehouse_id' => $this->warehouse->id,
            'client_id' => $this->client->id,
            'entry_date' => now(),
            'status' => 'pending',
            'is_banned' => true,
            'ban_reason' => 'Security impoundment order.',
            'banned_at' => now(),
        ]);

        $response = $this->actingAs($this->client)->get(route('boxes.track', $box->id));

        $response->assertStatus(200);
        $response->assertSee('CARGO QR CODE BANNED');
        $response->assertSee('Security impoundment order.');
        $response->assertSee('Banned / Revoked');
    }

    public function test_admin_can_unban_box_qr(): void
    {
        $box = Box::create([
            'batch_number' => 'BATCH-TEST-003',
            'qr_code' => 'QR-TEST-003',
            'box_number' => 3,
            'total_boxes' => 5,
            'invoice_number' => 'INV-998879',
            'shipper_name' => 'Everest Logistics',
            'warehouse_id' => $this->warehouse->id,
            'client_id' => $this->client->id,
            'entry_date' => now(),
            'status' => 'pending',
            'is_banned' => true,
            'ban_reason' => 'Inspection hold.',
            'banned_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.boxes.unban-qr', $box->id));

        $response->assertRedirect();
        $box->refresh();

        $this->assertFalse($box->isBanned());
        $this->assertNull($box->ban_reason);

        // QR API should now return valid qr_data
        $qrResponse = $this->actingAs($this->client)->getJson(route('boxes.qr', $box->id));
        $qrResponse->assertStatus(200);
        $qrResponse->assertJson([
            'is_banned' => false,
            'status' => 'pending',
        ]);
        $this->assertNotNull($qrResponse->json('qr_data'));
    }
}
