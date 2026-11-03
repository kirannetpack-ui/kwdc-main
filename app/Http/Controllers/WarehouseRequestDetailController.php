<?php

namespace App\Http\Controllers;

use App\Models\WarehouseRequest;
use Illuminate\Http\Request;

class WarehouseRequestDetailController extends Controller
{
    private function accessible(int $id): WarehouseRequest
    {
        $record = WarehouseRequest::with(['client', 'warehouse', 'assignedWarehouse'])->findOrFail($id);
        $user = auth()->user();
        abort_unless($user->is_admin || $user->role === 'admin'
            || $record->client_id === $user->id
            || $record->warehouse?->user_id === $user->id, 403);
        return $record;
    }

    public function show(int $id)
    {
        $record = $this->accessible($id);
        $canReview = auth()->user()->is_admin || auth()->user()->role === 'admin'
            || $record->warehouse?->user_id === auth()->id();
        return view('warehouse-requests.show', compact('record', 'canReview'));
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
}
