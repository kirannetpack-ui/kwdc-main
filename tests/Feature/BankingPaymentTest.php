<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BankingPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $client;
    protected Warehouse $warehouse;
    protected WarehouseRequest $warehouseRequest;
    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_admin' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->client = User::factory()->create([
            'role' => 'client',
            'is_client' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->warehouse = Warehouse::create([
            'name' => 'KTM Main Depot',
            'location' => 'Kathmandu Ring Road',
            'user_id' => $this->admin->id,
            'status' => 'approved',
        ]);

        $this->warehouseRequest = WarehouseRequest::create([
            'client_id' => $this->client->id,
            'warehouse_id' => $this->warehouse->id,
            'status' => 'approved',
            'space_required' => 150,
            'purpose' => 'Commercial pallet storage',
        ]);

        $this->invoice = Invoice::create([
            'invoice_number' => 'INV-202611-BANK01',
            'client_id' => $this->client->id,
            'warehouse_request_id' => $this->warehouseRequest->id,
            'amount' => 45000.00,
            'grand_total' => 45000.00,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'due_date' => now()->addDays(7),
        ]);
    }

    public function test_client_can_submit_direct_bank_transfer(): void
    {
        $slip = UploadedFile::fake()->create('bank_slip.jpg', 500, 'image/jpeg');

        $response = $this->actingAs($this->client)->post(route('payment.bank.submit'), [
            'invoice_id' => $this->invoice->id,
            'bank_name' => 'Nabil Bank Ltd.',
            'depositor_name' => 'Kathmandu Logistics Corp',
            'reference_number' => 'NBL-TXN-9840124',
            'transfer_date' => '2026-11-01',
            'notes' => 'Bulk warehousing monthly rental advance',
            'voucher_file' => $slip,
        ]);

        $response->assertRedirect(route('payment.history'));
        $response->assertSessionHas('success');

        $transaction = Transaction::where('invoice_id', $this->invoice->id)
            ->where('payment_method', 'bank_transfer')
            ->first();

        $this->assertNotNull($transaction);
        $this->assertEquals('pending', $transaction->status);
        $this->assertEquals(45000.00, (float) $transaction->amount);
        $this->assertStringContainsString('NBL-TXN-9840124', $transaction->transaction_id);
        $this->assertEquals('Nabil Bank Ltd.', $transaction->payment_details['bank_name']);
        $this->assertEquals('NBL-TXN-9840124', $transaction->payment_details['reference_number']);
        $this->assertNotNull($transaction->payment_details['voucher_path']);
    }

    public function test_client_can_submit_banking_qr_payment(): void
    {
        $response = $this->actingAs($this->client)->post(route('payment.banking-qr.submit'), [
            'invoice_id' => $this->invoice->id,
            'rrn_code' => '981240182947',
            'sender_bank' => 'Global Smart Plus',
        ]);

        $response->assertRedirect(route('payment.history'));
        $response->assertSessionHas('success');

        $transaction = Transaction::where('invoice_id', $this->invoice->id)
            ->where('payment_method', 'banking_qr')
            ->first();

        $this->assertNotNull($transaction);
        $this->assertEquals('pending', $transaction->status);
        $this->assertEquals('981240182947', $transaction->payment_details['rrn_code']);
        $this->assertEquals('Global Smart Plus', $transaction->payment_details['sender_bank']);
    }

    public function test_admin_can_view_bank_transfers_reconciliation_page(): void
    {
        Transaction::create([
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->client->id,
            'amount' => 45000.00,
            'payment_method' => 'bank_transfer',
            'transaction_id' => 'BANK-TEST-001',
            'status' => 'pending',
            'payment_details' => [
                'bank_name' => 'Nabil Bank Ltd.',
                'reference_number' => 'REF-001',
            ],
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.payments.bank-transfers'));

        $response->assertStatus(200);
        $response->assertSee('Direct Bank & QR Reconciliation', false);
        $response->assertSee('BANK-TEST-001');
        $response->assertSee('Nabil Bank Ltd.');
    }

    public function test_non_admin_cannot_access_bank_transfers_reconciliation(): void
    {
        $response = $this->actingAs($this->client)->get(route('admin.payments.bank-transfers'));
        $response->assertStatus(403);
    }

    public function test_admin_can_approve_bank_transfer_and_mark_invoice_as_paid(): void
    {
        $transaction = Transaction::create([
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->client->id,
            'amount' => 45000.00,
            'payment_method' => 'bank_transfer',
            'transaction_id' => 'BANK-APPROVE-001',
            'status' => 'pending',
            'payment_details' => [
                'bank_name' => 'NIC Asia Bank Ltd.',
                'reference_number' => 'NIC-998811',
            ],
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.payments.bank-transfers.approve', $transaction->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $transaction->refresh();
        $this->assertEquals('completed', $transaction->status);
        $this->assertNotNull($transaction->receipt_no);

        $this->invoice->refresh();
        $this->assertEquals('paid', $this->invoice->status);
        $this->assertEquals('paid', $this->invoice->payment_status);
        $this->assertEquals('bank_transfer', $this->invoice->payment_method);
    }

    public function test_admin_can_reject_bank_transfer_with_a_reason(): void
    {
        $transaction = Transaction::create([
            'invoice_id' => $this->invoice->id,
            'user_id' => $this->client->id,
            'amount' => 45000.00,
            'payment_method' => 'bank_transfer',
            'transaction_id' => 'BANK-REJECT-001',
            'status' => 'pending',
            'payment_details' => [
                'bank_name' => 'Global IME Bank Ltd.',
                'reference_number' => 'GLB-INVALID',
            ],
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.payments.bank-transfers.reject', $transaction->id), [
            'rejection_reason' => 'Deposit voucher unreadable and UTR not found on bank statement.',
        ]);

        $response->assertRedirect();
        $transaction->refresh();

        $this->assertEquals('failed', $transaction->status);
        $this->assertEquals('Deposit voucher unreadable and UTR not found on bank statement.', $transaction->notes);

        $this->invoice->refresh();
        $this->assertEquals('unpaid', $this->invoice->payment_status);
    }
}
