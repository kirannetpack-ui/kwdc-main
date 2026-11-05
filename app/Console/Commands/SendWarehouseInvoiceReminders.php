<?php

namespace App\Console\Commands;

use App\Mail\InvoiceDueReminder;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class SendWarehouseInvoiceReminders extends Command
{
    protected $signature = 'invoices:send-reminders';

    protected $description = 'Remind warehouse clients of prepayments due in seven days';

    public function handle(): int
    {
        $dueDate = today()->addDays(7)->toDateString();
        $failed = false;

        Invoice::whereNotNull('warehouse_request_id')
            ->where(fn ($query) => $query->whereNull('order_type')->orWhere('order_type', 'warehouse'))
            ->whereIn('status', ['pending', 'unpaid', 'issued'])
            ->where('payment_status', 'unpaid')
            ->where(fn ($query) => $query->whereDate('payment_due_date', $dueDate)
                ->orWhere(fn ($legacy) => $legacy->whereNull('payment_due_date')->whereDate('due_date', $dueDate)))
            ->where(fn ($query) => $query->whereNull('warehouse_reminder_due_date')
                ->orWhereDate('warehouse_reminder_due_date', '!=', $dueDate))
            ->chunkById(100, function ($invoices) use ($dueDate, &$failed) {
                foreach ($invoices as $invoice) {
                    try {
                        Cache::lock("warehouse-invoice-reminder:{$invoice->id}", 300)
                            ->get(function () use ($invoice, $dueDate, &$failed) {
                                $invoice = $invoice->fresh(['client', 'warehouseRequest.client']);
                                if (!$invoice || $invoice->payment_status !== 'unpaid'
                                    || !in_array($invoice->status, ['pending', 'unpaid', 'issued'], true)
                                    || ($invoice->payment_due_date ?? $invoice->due_date)?->toDateString() !== $dueDate
                                    || $invoice->warehouse_reminder_due_date === $dueDate) {
                                    return;
                                }

                                $client = $invoice->client ?? $invoice->warehouseRequest?->client;
                                if (!$client?->email) {
                                    $this->warn("Invoice {$invoice->id} has no client email.");
                                    $failed = true;
                                    return;
                                }

                                Mail::to($client->email)->send(new InvoiceDueReminder($invoice));
                                $invoice->forceFill(['warehouse_reminder_due_date' => $dueDate])->save();
                            });
                    } catch (\Throwable $exception) {
                        report($exception);
                        $this->error("Could not send reminder for invoice {$invoice->id}.");
                        $failed = true;
                    }
                }
            });

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
