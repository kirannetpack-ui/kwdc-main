<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\DispatchOrder;
use App\Models\PickupRequest;
use App\Models\WarehouseRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InvoiceService
{
    // Generate invoice for dispatch order
    public function generateDispatchInvoice(DispatchOrder $dispatch): ?Invoice
    {
        try {
            // Check if invoice already exists
            $existingInvoice = Invoice::where('order_type', 'dispatch')
                                      ->where('order_id', $dispatch->id)
                                      ->first();
            
            if ($existingInvoice) {
                return $existingInvoice;
            }
            
            $items = [];
            
            // Line items for delivery stops
            if ($dispatch->stops && count($dispatch->stops) > 0) {
                foreach ($dispatch->stops as $index => $stop) {
                    $items[] = [
                        'description' => "Delivery Stop #" . ($index + 1) . " - " . ($stop->recipient_name ?? 'Unknown'),
                        'quantity' => 1,
                        'unit_price' => $stop->distance_price ?? 0,
                        'total' => $stop->distance_price ?? 0
                    ];
                }
            }
            
            // Add base fare
            $items[] = [
                'description' => "Base Delivery Fare",
                'quantity' => 1,
                'unit_price' => $dispatch->total_price ?? 0,
                'total' => $dispatch->total_price ?? 0
            ];
            
            $subtotal = $dispatch->total_price ?? 0;
            $subtotal = $subtotal ?: ($dispatch->grand_total ?: $dispatch->base_price ?: 0);
            $taxAmount = $subtotal * 0.13; // 13% VAT
            $grandTotal = $subtotal + $taxAmount;
            $warehouseRequest = $this->warehouseRequestFor($dispatch);
            
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'user_id' => $dispatch->client_id,
                'order_type' => 'dispatch',
                'order_id' => $dispatch->id,
                'client_id' => $dispatch->client_id,
                'warehouse_request_id' => $warehouseRequest->id,
                'amount' => $grandTotal,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax_rate' => 13,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'due_date' => now()->addDays(7),
                'payment_due_date' => now()->addDays(7),
                'billing_type' => $dispatch->bill_type ?? 'regular',
                'pan_number' => $dispatch->pan_number ?? null,
                'billing_address' => $this->getClientAddress($dispatch->client_id),
                'items' => $items,
                'notes' => "Thank you for choosing KTM-WDC. Payment due within 7 days."
            ]);
            
            // Generate QR code and PDF
            $this->generateInvoiceQR($invoice);
            $this->generateAndStorePDF($invoice);
            
            return $invoice;
            
        } catch (\Exception $e) {
            Log::error('Invoice generation failed: ' . $e->getMessage());
            return null;
        }
    }
    
    // Generate invoice for pickup request
    public function generatePickupInvoice(PickupRequest $pickup): ?Invoice
    {
        try {
            $existingInvoice = Invoice::where('order_type', 'pickup')
                                      ->where('order_id', $pickup->id)
                                      ->first();
            
            if ($existingInvoice) {
                return $existingInvoice;
            }
            
            $items = [];
            
            if ($pickup->stops && count($pickup->stops) > 0) {
                foreach ($pickup->stops as $index => $stop) {
                    $items[] = [
                        'description' => "Pickup Stop #" . ($index + 1) . " - " . ($stop->contact_name ?? 'Unknown'),
                        'quantity' => 1,
                        'unit_price' => $stop->distance_price ?? 0,
                        'total' => $stop->distance_price ?? 0
                    ];
                }
            }
            
            $items[] = [
                'description' => "Base Pickup Fare",
                'quantity' => 1,
                'unit_price' => $pickup->total_price ?? 0,
                'total' => $pickup->total_price ?? 0
            ];
            
            $subtotal = $pickup->total_price ?? 0;
            $taxAmount = $subtotal * 0.13;
            $grandTotal = $subtotal + $taxAmount;
            $warehouseRequest = $this->warehouseRequestFor($pickup);
            
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'user_id' => $pickup->client_id,
                'order_type' => 'pickup',
                'order_id' => $pickup->id,
                'client_id' => $pickup->client_id,
                'warehouse_request_id' => $warehouseRequest->id,
                'amount' => $grandTotal,
                'subtotal' => $subtotal,
                'discount' => 0,
                'tax_rate' => 13,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'due_date' => now()->addDays(7),
                'payment_due_date' => now()->addDays(7),
                'billing_type' => $pickup->bill_type ?? 'regular',
                'pan_number' => $pickup->pan_number ?? null,
                'billing_address' => $this->getClientAddress($pickup->client_id),
                'items' => $items,
                'notes' => "Pickup service invoice. Payment due within 7 days."
            ]);
            
            $this->generateInvoiceQR($invoice);
            $this->generateAndStorePDF($invoice);
            
            return $invoice;
            
        } catch (\Exception $e) {
            Log::error('Pickup invoice generation failed: ' . $e->getMessage());
            return null;
        }
    }
    
    // Get client address from profile
    private function getClientAddress($clientId)
    {
        $user = \App\Models\User::find($clientId);
        if ($user && $user->profile) {
            return $user->profile->address ?? '';
        }
        return '';
    }
    
    // Generate QR code for invoice verification
    private function generateInvoiceQR(Invoice $invoice): void
    {
        try {
            $verificationUrl = url('/invoice/verify/' . $invoice->invoice_number);
            
            $qrCode = QrCode::size(150)
                            ->format('png')
                            ->generate($verificationUrl);
            
            $qrPath = 'invoices/qrcodes/' . $invoice->invoice_number . '.png';
            Storage::disk('public')->put($qrPath, $qrCode);
            
            $invoice->qr_code = $qrPath;
            $invoice->save();
            
        } catch (\Exception $e) {
            Log::warning('QR generation failed: ' . $e->getMessage());
        }
    }
    
    // Generate and store PDF invoice
    public function generateAndStorePDF(Invoice $invoice): ?string
    {
        try {
            $pdf = PDF::loadView('pdf.invoice', [
                'invoice' => $invoice,
                'company' => $this->getCompanyDetails()
            ]);
            
            $pdfPath = 'invoices/pdfs/' . $invoice->invoice_number . '.pdf';
            Storage::disk('public')->put($pdfPath, $pdf->output());
            
            return $pdfPath;
            
        } catch (\Exception $e) {
            Log::error('PDF generation failed: ' . $e->getMessage());
            return null;
        }
    }

    private function warehouseRequestFor(DispatchOrder|PickupRequest $order): WarehouseRequest
    {
        if ($order instanceof DispatchOrder && $order->warehouse_request_id) {
            return WarehouseRequest::findOrFail($order->warehouse_request_id);
        }

        $warehouseId = $order->warehouse_id;

        if (!$warehouseId) {
            throw new \RuntimeException('Cannot generate invoice without a warehouse.');
        }

        return WarehouseRequest::firstOrCreate(
            [
                'client_id' => $order->client_id,
                'warehouse_id' => $warehouseId,
                'status' => 'approved',
            ],
            [
                'space_required' => 0,
                'purpose' => 'Generated for ' . class_basename($order) . ' invoice #' . $order->id,
                'approved_at' => now(),
            ]
        );
    }
    
    // Get company details for invoice
    private function getCompanyDetails(): array
    {
        return [
            'name' => 'KTM Warehouse & Distribution Center',
            'address' => 'Ring Road, Kathmandu, Nepal',
            'phone' => '+977-1-5551234',
            'email' => 'accounts@ktm-wdc.com',
            'pan' => '123456789',
            'logo' => asset('images/logo.png')
        ];
    }
    
    // Download invoice
    public function downloadInvoice(Invoice $invoice)
    {
        $pdf = PDF::loadView('pdf.invoice', [
            'invoice' => $invoice,
            'company' => $this->getCompanyDetails()
        ]);
        
        return $pdf->download('Invoice_' . $invoice->invoice_number . '.pdf');
    }
    
    // Send invoice email to client
    public function sendInvoiceEmail(Invoice $invoice)
    {
        try {
            $pdf = PDF::loadView('pdf.invoice', [
                'invoice' => $invoice,
                'company' => $this->getCompanyDetails()
            ]);
            
            \Mail::send('emails.invoice', ['invoice' => $invoice], function($message) use ($invoice, $pdf) {
                $message->to($invoice->client->email, $invoice->client->name)
                        ->subject('Invoice #' . $invoice->invoice_number . ' from KTM-WDC')
                        ->attachData($pdf->output(), 'Invoice_' . $invoice->invoice_number . '.pdf', [
                            'mime' => 'application/pdf',
                        ]);
            });
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Invoice email failed: ' . $e->getMessage());
            return false;
        }
    }
}
