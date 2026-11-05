<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Warehouse;
use App\Models\WarehouseRequest;
use Illuminate\Support\Facades\DB;

class WarehouseReservations
{
    public function reserve(int $id, array $terms, bool $renewal): void
    {
        DB::transaction(function () use ($id, $terms, $renewal) {
            $record = WarehouseRequest::lockForUpdate()->findOrFail($id);
            $warehouse = Warehouse::lockForUpdate()->findOrFail($record->assigned_warehouse_id ?? $record->warehouse_id);
            $user = auth()->user();
            abort_unless($user->isAdmin() || ($user->role === 'property_owner'
                && in_array($user->id, [$warehouse->user_id, $warehouse->owner_id], true)), 403);
            abort_unless($warehouse->status === 'approved', 409);

            $start = \Illuminate\Support\Carbon::parse($terms['start_date'])->startOfDay();
            $end = \Illuminate\Support\Carbon::parse($terms['end_date'])->startOfDay();
            $area = (float) ($record->space_required ?: $record->required_area);
            abort_unless($area > 0, 422, 'Set the requested floor area first.');

            if ($renewal) {
                abort_unless($record->status === 'assigned' && $record->renewalChoice() === 'renew'
                    && $record->agreementEnd()?->copy()->addDay()->eq($start), 409, 'Renewal must start the day after the current agreement ends.');
                abort_if(WarehouseRequest::where('renewal_of_id', $record->id)->where('status', 'reserved')->exists(), 409);
                $original = $record;
                $record = new WarehouseRequest([
                    'client_id' => $original->client_id,
                    'warehouse_id' => $warehouse->id,
                    'space_required' => $area,
                    'required_area' => $area,
                    'purpose' => $original->purpose,
                ]);
                $record->renewal_of_id = $original->id;
            } else {
                abort_unless(in_array($record->status, ['pending', 'approved'], true), 409);
                $space = app(WarehouseAvailability::class)->snapshot($warehouse, $start, $end);
                abort_if($area > $space['available'], 409, 'Insufficient warehouse capacity for those dates.');
            }

            $record->fill([
                'assigned_warehouse_id' => $warehouse->id,
                'preferred_start_date' => $start,
                'contract_end_date' => $end,
                'monthly_rent' => $terms['monthly_rent'],
                'status' => 'reserved',
            ])->save();

            $invoice = Invoice::create([
                'invoice_number' => 'KWDC-WH-'.$record->id,
                'warehouse_request_id' => $record->id,
                'order_type' => 'warehouse',
                'order_id' => $record->id,
                'client_id' => $record->client_id,
                'user_id' => $record->client_id,
                'amount' => $terms['monthly_rent'],
                'subtotal' => $terms['monthly_rent'],
                'grand_total' => $terms['monthly_rent'],
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'due_date' => $start,
                'payment_due_date' => $start,
                'notes' => 'Prepayment for the first month of warehouse storage.',
            ]);

            if ($record->client?->email) {
                \Illuminate\Support\Facades\Mail::to($record->client->email)
                    ->queue((new \App\Mail\InvoiceGenerated($invoice))->afterCommit());
            }
        }, 3);
    }

    public function activate(int $id): void
    {
        DB::transaction(function () use ($id) {
            $record = WarehouseRequest::lockForUpdate()->findOrFail($id);

            if ($record->status !== 'reserved' || $record->preferred_start_date?->gt(today())
                || $record->agreementEnd()?->lt(today())
                || !$record->invoice()->where('invoice_number', 'KWDC-WH-'.$record->id)->where('payment_status', 'paid')->exists()) {
                return;
            }

            $warehouse = Warehouse::lockForUpdate()->findOrFail($record->assigned_warehouse_id);
            if ($warehouse->status !== 'approved') {
                return;
            }

            if ($record->renewal_of_id) {
                $original = WarehouseRequest::lockForUpdate()->findOrFail($record->renewal_of_id);
                if ($original->status !== 'assigned' || $original->renewalChoice() !== 'renew'
                    || !$original->agreementEnd()?->copy()->addDay()->eq($record->preferred_start_date)) {
                    return;
                }
                $original->update([
                    'contract_end_date' => $record->agreementEnd(),
                    'monthly_rent' => $record->monthly_rent,
                    'last_invoice_date' => $record->preferred_start_date,
                ]);
                $record->update(['status' => 'completed', 'completed_at' => now()]);
                return;
            }

            $space = app(WarehouseAvailability::class)->snapshot($warehouse, today(), null, $record->id);
            if ((float) $record->space_required > $space['available']) {
                return;
            }

            $record->update(['status' => 'assigned', 'assigned_at' => now(), 'last_invoice_date' => $record->preferred_start_date]);
        }, 3);
    }
}
