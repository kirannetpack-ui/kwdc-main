<?php

namespace App\Http\Controllers;

use App\Models\WarehouseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseRequestDetailController extends Controller
{
    private function accessible(int $id): WarehouseRequest
    {
        $record = WarehouseRequest::with(['client', 'warehouse', 'assignedWarehouse'])->findOrFail($id);
        $user = auth()->user();
        abort_unless($user->is_admin || $user->role === 'admin'
            || $record->client_id === $user->id
            || $record->warehouse?->user_id === $user->id
            || ($record->assignedWarehouse?->user_id === $user->id), 403);
        return $record;
    }

    public function show(int $id)
    {
        $record = $this->accessible($id);
        $canReview = auth()->user()->is_admin || auth()->user()->role === 'admin'
            || $record->warehouse?->user_id === auth()->id();
        $canConfirmMoveOut = $this->canConfirmMoveOut($record);
        return view('warehouse-requests.show', compact('record', 'canReview', 'canConfirmMoveOut'));
    }

    public function decide(Request $request, int $id)
    {
        $record = $this->accessible($id);
        abort_unless(auth()->user()->is_admin || auth()->user()->role === 'admin'
            || $record->warehouse?->user_id === auth()->id(), 403);
        $data = $request->validate(['decision' => ['required', 'in:approved,rejected']]);
        $updated = WarehouseRequest::whereKey($record->id)->where('status', 'pending')
            ->update(['status' => $data['decision']]);
        abort_unless($updated, 409, 'This request has already been reviewed.');
        return back()->with('success', 'Request '.$data['decision'].'.');
    }

    public function reserve(Request $request, int $id, \App\Services\WarehouseReservations $reservations)
    {
        $terms = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'monthly_rent' => 'required|numeric|min:0.01|max:99999999.99',
            'renewal' => 'sometimes|boolean',
        ]);

        $reservations->reserve($id, $terms, $request->boolean('renewal'));

        return back()->with('success', 'Space reserved and prepaid invoice issued.');
    }

    public function confirmMoveOut(Request $request, int $id)
    {
        $request->validate(['space_cleared' => 'accepted']);

        DB::transaction(function () use ($id) {
            $record = WarehouseRequest::with(['warehouse', 'assignedWarehouse'])->lockForUpdate()->findOrFail($id);
            abort_unless($this->canConfirmMoveOut($record), 403);
            abort_unless($record->status === 'assigned' && $record->agreementEnd()?->lte(today())
                && $record->renewalChoice() !== 'renew', 409, 'This agreement cannot be closed for move-out.');

            $record->update(['status' => 'completed', 'completed_at' => now()]);
        });

        return back()->with('success', 'Move-out confirmed. The cleared space is available.');
    }

    private function canConfirmMoveOut(WarehouseRequest $record): bool
    {
        $user = auth()->user();
        $warehouse = $record->assignedWarehouse ?? $record->warehouse;

        return $user->isAdmin() || ($user->role === 'property_owner'
            && in_array($user->id, [$warehouse?->user_id, $warehouse?->owner_id], true));
    }
}
