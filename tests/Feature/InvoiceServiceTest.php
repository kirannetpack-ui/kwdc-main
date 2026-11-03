<?php

namespace Tests\Feature;

use App\Models\DispatchOrder;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseRequest;
use App\Services\InvoiceService;
use App\Services\ProfessionalEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class InvoiceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dispatch_invoice_generation_populates_required_payable_fields_and_pdf(): void
    {
        Storage::fake('public');

        $client = User::factory()->create(['role' => 'client']);
        $warehouse = Warehouse::factory()->create();
        $warehouseRequest = WarehouseRequest::create([
            'client_id' => $client->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'approved',
            'space_required' => 100,
            'purpose' => 'Consumer goods storage',
        ]);

        $dispatch = DispatchOrder::factory()->create([
            'client_id' => $client->id,
            'warehouse_id' => $warehouse->id,
            'warehouse_request_id' => $warehouseRequest->id,
            'base_price' => 1000,
            'grand_total' => 1000,
            'bill_type' => 'regular',
        ]);

        $invoice = app(InvoiceService::class)->generateDispatchInvoice($dispatch);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertSame($client->id, $invoice->user_id);
        $this->assertSame($client->id, $invoice->client_id);
        $this->assertSame($warehouseRequest->id, $invoice->warehouse_request_id);
        $this->assertSame('dispatch', $invoice->order_type);
        $this->assertSame('1130.00', $invoice->amount);
        $this->assertSame('unpaid', $invoice->payment_status);

        Storage::disk('public')->assertExists('invoices/pdfs/' . $invoice->invoice_number . '.pdf');
    }

    public function test_invoice_pdf_view_renders_for_download_and_email_flows(): void
    {
        Storage::fake('public');

        $invoice = $this->invoiceFor(User::factory()->create(['role' => 'client']), 2500);

        $pdfPath = app(InvoiceService::class)->generateAndStorePDF($invoice);

        $this->assertSame('invoices/pdfs/' . $invoice->invoice_number . '.pdf', $pdfPath);
        Storage::disk('public')->assertExists($pdfPath);
        $this->assertGreaterThan(1000, strlen(Storage::disk('public')->get($pdfPath)));
    }

    public function test_professional_payment_receipt_attaches_transaction_pdf(): void
    {
        $user = User::factory()->create(['role' => 'client']);
        $invoice = $this->invoiceFor($user, 1500);
        $transaction = Transaction::create([
            'invoice_id' => $invoice->id,
            'user_id' => $user->id,
            'amount' => 1500,
            'payment_method' => 'khalti',
            'transaction_id' => 'receipt-test-pidx',
            'receipt_no' => 'RCP-TEST-0001',
            'status' => 'completed',
        ]);

        $pdf = new class {
            public function output(): string
            {
                return '%PDF test receipt';
            }
        };

        Mail::shouldReceive('send')
            ->once()
            ->withArgs(function ($view, $data, $callback) use ($user, $transaction) {
                $this->assertSame('emails.professional.payment_receipt', $view);
                $this->assertSame($transaction->id, $data['transaction']->id);

                $message = Mockery::mock();
                $message->shouldReceive('to')
                    ->once()
                    ->with($user->email, $user->name)
                    ->andReturnSelf();
                $message->shouldReceive('subject')
                    ->once()
                    ->with('Payment Receipt from KTM-WDC')
                    ->andReturnSelf();
                $message->shouldReceive('attachData')
                    ->once()
                    ->with('%PDF test receipt', 'receipt_' . $transaction->id . '.pdf', ['mime' => 'application/pdf'])
                    ->andReturnSelf();

                $callback($message);

                return true;
            });

        app(ProfessionalEmailService::class)->sendPaymentReceipt($user, $transaction, $pdf);
    }

    private function invoiceFor(User $user, float $amount): Invoice
    {
        $warehouse = Warehouse::factory()->create([
            'user_id' => $user->id,
            'location' => 'Kathmandu',
        ]);
        $warehouseRequest = WarehouseRequest::create([
            'client_id' => $user->id,
            'warehouse_id' => $warehouse->id,
            'status' => 'approved',
            'space_required' => 100,
            'purpose' => 'Test storage',
        ]);

        return Invoice::create([
            'user_id' => $user->id,
            'client_id' => $user->id,
            'warehouse_request_id' => $warehouseRequest->id,
            'invoice_number' => 'INV-TEST-' . $user->id . '-' . random_int(1000, 9999),
            'amount' => $amount,
            'subtotal' => $amount,
            'grand_total' => $amount,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'due_date' => now()->addDays(7)->toDateString(),
            'payment_due_date' => now()->addDays(7)->toDateString(),
        ]);
    }
}
