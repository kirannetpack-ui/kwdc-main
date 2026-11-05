<?php

namespace App\Console\Commands;

use App\Mail\InvoiceGenerated;
use App\Models\Invoice;
use App\Models\WarehouseRequest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class WarehouseBilling extends Command
{
    protected $signature = 'warehouse:billing';

    protected $description = 'Issue warehouse prepayment invoices before the next billing period';

    public function handle(): int
    {
        WarehouseRequest::where('status', 'assigned')
            ->whereNotNull('last_invoice_date')
            ->where('monthly_rent', '>', 0)
            ->where(fn ($query) => $query->whereHas('invoice', fn ($invoice) => $invoice->where('order_type', 'warehouse'))
                ->orWhereIn('id', WarehouseRequest::where('status', 'completed')->whereNotNull('renewal_of_id')->select('renewal_of_id')))
            ->select('id')->chunkById(100, function ($records) {
                foreach ($records as $row) {
                    DB::transaction(function () use ($row) {
                        $record = WarehouseRequest::where('status', 'assigned')->lockForUpdate()->find($row->id);
                        if (!$record || !$record->last_invoice_date || !$record->agreementEnd()) {
                            return;
                        }

                        $due = $record->last_invoice_date->copy()->addMonthNoOverflow();
                        if ($due->gt(today()->addDays(7)) || $due->gt($record->agreementEnd())) {
                            return;
                        }

                        $invoice = Invoice::create([
                            'invoice_number' => "KWDC-WH-{$record->id}-".$due->format('Ymd'),
                            'warehouse_request_id' => $record->id,
                            'client_id' => $record->client_id,
                            'user_id' => $record->client_id,
                            'order_type' => 'warehouse',
                            'order_id' => $record->id,
                            'amount' => $record->monthly_rent,
                            'subtotal' => $record->monthly_rent,
                            'grand_total' => $record->monthly_rent,
                            'status' => 'pending',
                            'payment_status' => 'unpaid',
                            'due_date' => $due,
                            'payment_due_date' => $due,
                        ]);
                        $record->update(['last_invoice_date' => $due]);
                        if ($record->client?->email) {
                            Mail::to($record->client->email)->queue((new InvoiceGenerated($invoice))->afterCommit());
                        }
                    }, 3);
                }
            });

        return self::SUCCESS;
    }
}
