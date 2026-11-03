<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\DispatchOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class PdfController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function downloadWarehouse($id)
    {
        $warehouse = Warehouse::with('user')->findOrFail($id);

        abort_unless($this->canDownloadWarehouse($warehouse), 403);

        $pdf = Pdf::loadView('pdf.warehouse', compact('warehouse'));
        return $pdf->download("Warehouse_{$warehouse->id}.pdf");
    }

    public function downloadDispatch($id)
    {
        $dispatch = DispatchOrder::with(['client', 'driver', 'stops'])->findOrFail($id);

        abort_unless($this->canDownloadDispatch($dispatch), 403);

        $pdf = Pdf::loadView('pdf.dispatch', compact('dispatch'));
        return $pdf->download("Dispatch_{$dispatch->tracking_id}.pdf");
    }

    protected function canDownloadWarehouse(Warehouse $warehouse): bool
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return true;
        }

        return $warehouse->user_id === $user->id
            || $warehouse->owner_id === $user->id;
    }

    protected function canDownloadDispatch(DispatchOrder $dispatch): bool
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return true;
        }

        return $dispatch->client_id === $user->id
            || $dispatch->driver_id === $user->id;
    }
}
