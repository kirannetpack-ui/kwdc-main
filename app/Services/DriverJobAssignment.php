<?php

namespace App\Services;

use App\Mail\InvoiceGenerated;
use App\Models\DispatchOrder;
use App\Models\Notification;
use App\Models\PickupRequest;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class DriverJobAssignment
{
    public const ACTIVE_STATUSES = ['assigned', 'in_progress', 'picked_up', 'on_the_way'];

    public function isBusy(int $driverId): bool
    {
        foreach ([DispatchOrder::class, PickupRequest::class] as $model) {
            if ($model::where('driver_id', $driverId)->whereIn('status', self::ACTIVE_STATUSES)->exists()) {
                return true;
            }
        }

        return false;
    }

    public function offers(string $model, int $driverId)
    {
        return $this->pendingOffers($model, $driverId)
            ->when($this->isBusy($driverId), fn ($query) => $query->whereRaw('1 = 0'));
    }

    private function pendingOffers(string $model, int $driverId)
    {
        $table = (new $model)->getTable();

        return $model::where('status', 'pending')
            ->whereNotIn("$table.id", DB::table('driver_job_rejections')
                ->select('job_id')
                ->where('driver_id', $driverId)
                ->where('job_table', $table));
    }

    public function reject(string $model, int $id, int $driverId): void
    {
        DB::transaction(function () use ($model, $id, $driverId) {
            User::whereKey($driverId)->lockForUpdate()->firstOrFail();
            $job = $this->pendingOffers($model, $driverId)->lockForUpdate()->findOrFail($id);

            DB::table('driver_job_rejections')->insert([
                'driver_id' => $driverId,
                'job_table' => $job->getTable(),
                'job_id' => $job->id,
            ]);

            if ($job->driver_id === $driverId) {
                $job->update(['driver_id' => null]);
            }
        }, 3);
    }

    public function acceptQuote(Proposal $proposal): void
    {
        $this->claim(DispatchOrder::class, $proposal->job_id, $proposal->driver_id, $proposal);

        $proposal->accept();
    }

    public function claim(string $model, int $id, int $driverId, ?Proposal $quote = null): DispatchOrder|PickupRequest
    {
        return DB::transaction(function () use ($model, $id, $driverId, $quote) {
            User::whereKey($driverId)->lockForUpdate()->firstOrFail();
            abort_if($this->isBusy($driverId), 409, 'Complete your current job before accepting another.');

            $job = $this->pendingOffers($model, $driverId)
                ->lockForUpdate()
                ->findOrFail($id);

            $job->fill([
                'driver_id' => $driverId,
                'status' => 'assigned',
                'assigned_at' => now(),
            ]);

            if ($job instanceof DispatchOrder) {
                $job->forceFill([
                    'tracking_enabled' => true,
                    'tracking_token' => $job->tracking_token ?: Str::random(40),
                ]);
            }

            if ($quote) {
                abort_unless($job->client_id === $quote->client_id, 409);
                $job->fill([
                    'base_price' => $quote->proposed_price,
                    'driver_earning' => (float) $quote->proposed_price * 0.75,
                ]);
            }

            $job->save();

            if ($job instanceof DispatchOrder) {
                $this->issueInvoice($job);
            }

            return $job;
        }, 3);
    }

    private function issueInvoice(DispatchOrder $job): void
    {
        $invoice = app(InvoiceService::class)->generateDispatchInvoice($job);
        abort_unless($invoice, 500, 'The dispatch invoice could not be issued.');

        $job->update(['invoice_no' => $invoice->invoice_number]);

        Notification::createNotification($job->client_id, 'info', 'Delivery invoice available',
            'Invoice ' . $invoice->invoice_number . ' is available under Invoices.', $invoice->id, $invoice::class);

        if ($job->client?->email) {
            Mail::to($job->client->email)->queue((new InvoiceGenerated($invoice))->afterCommit());
        }
    }
}
