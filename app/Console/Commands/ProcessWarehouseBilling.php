<?php

namespace App\Console\Commands;

use App\Models\WarehouseRequest;
use App\Models\Invoice;
use App\Mail\InvoiceGenerated;
use App\Mail\ContractExpiryReminder;
use App\Mail\ExtendedStorageReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ProcessWarehouseBilling extends Command
{
    protected $signature = 'warehouse:billing';
    protected $description = 'Generate monthly invoices, send reminders, and handle expired contracts';

    public function handle()
    {
        $this->generateInvoices();
        $this->sendExpiryReminders();
        $this->handleExtendedStorage();

        $this->info('Warehouse billing process completed.');
    }

    private function generateInvoices()
    {
        $activeRequests = WarehouseRequest::where('status', 'assigned')
            ->whereNull('goods_auctioned')
            ->where(function ($q) {
                $q->whereNull('last_invoice_date')
                  ->orWhere('last_invoice_date', '<=', Carbon::now()->subMonth());
            })
            ->get();

        foreach ($activeRequests as $req) {
            $invoice = Invoice::create([
                'warehouse_request_id' => $req->id,
                'invoice_number' => 'INV-' . $req->id . '-' . now()->format('Ymd'),
                'amount' => $req->monthly_rent,
                'due_date' => Carbon::now()->addDays(7),
                'status' => 'pending',
                'description' => 'Monthly rent for ' . ($req->assignedWarehouse->name ?? 'warehouse'),
            ]);

            Mail::to($req->client->email)->send(new InvoiceGenerated($invoice));

            $req->last_invoice_date = Carbon::now();
            $req->save();
        }
    }

    private function sendExpiryReminders()
    {
        $requests = WarehouseRequest::where('status', 'assigned')
            ->where('contract_end_date', '>', Carbon::now())
            ->get();

        foreach ($requests as $req) {
            $daysLeft = Carbon::now()->diffInDays($req->contract_end_date, false);

            if ($daysLeft == 30 || $daysLeft == 15 || $daysLeft == 5) {
                Mail::to($req->client->email)->send(new ContractExpiryReminder($req, $daysLeft));
            }
        }
    }

    private function handleExtendedStorage()
    {
        $expiredRequests = WarehouseRequest::where('status', 'assigned')
            ->where('contract_end_date', '<', Carbon::now())
            ->whereNull('goods_auctioned')
            ->get();

        foreach ($expiredRequests as $req) {
            $daysOverdue = Carbon::now()->diffInDays($req->contract_end_date);

            if ($daysOverdue % 7 == 0) {
                Mail::to($req->client->email)->send(new ExtendedStorageReminder($req, $daysOverdue));
            }

            if ($daysOverdue > 60 && !$req->goods_auctioned) {
                $req->goods_auctioned = true;
                $req->save();
                $this->info("Request #{$req->id} marked for auction.");
            }
        }
    }
}