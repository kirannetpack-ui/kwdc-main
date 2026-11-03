<?php

namespace App\Console\Commands;

use App\Models\WarehouseRequest;
use App\Models\Invoice;
use App\Mail\InvoiceGenerated;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ProcessWarehouseInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'warehouse:invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly invoices for assigned warehouse requests';

    /**
     * Execute the console command.
     */
    public function handle()
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

        $this->info('Invoices generated successfully.');
    }
}