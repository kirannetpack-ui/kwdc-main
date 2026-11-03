<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DispatchOrder;
use App\Models\PickupRequest;
use App\Models\WarehouseRequest;
use Illuminate\Support\Facades\Auth;

class TrackingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ============================================================
    // UNIFIED LIVE TRACKING DASHBOARD (NEW)
    // ============================================================
    public function index()
{
    $user = auth()->user();
    $cacheKey = 'tracking_' . $user->role . '_' . $user->id;
    $dispatches = cache()->remember($cacheKey, 300, function () use ($user) {
        $query = DispatchOrder::with(['client', 'driver'])->orderBy('created_at', 'desc');
        if ($user->role === 'client') {
            $query->where('client_id', $user->id);
        } elseif ($user->role === 'driver') {
            $query->where('driver_id', $user->id);
        }
        return $query->paginate(20);
    });

    return view('tracking.index', compact('dispatches'));
}

    // ============================================================
    // LEGACY / DEDICATED PAGES (KEPT FOR BACKWARD COMPATIBILITY)
    // ============================================================

    public function incoming()
    {
        $user = auth()->user();
        
        if ($user->is_admin || $user->role == 'admin') {
            // Admin sees all incoming shipments
            $shipments = WarehouseRequest::with(['dispatchOrder', 'warehouse', 'client'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            // Client sees only their own shipments
            $shipments = WarehouseRequest::where('client_id', auth()->id())
                ->with(['dispatchOrder', 'warehouse'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }
        
        return view('tracking.incoming', compact('shipments'));
    }

    public function outgoing()
    {
        $user = auth()->user();
        
        if ($user->is_admin || $user->role == 'admin') {
            // Admin sees all outgoing shipments
            $shipments = DispatchOrder::with(['driver', 'vehicle', 'warehouseRequest', 'warehouseRequest.client'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            // Client sees only their own outgoing shipments
            $shipments = DispatchOrder::whereHas('warehouseRequest', function($q) {
                $q->where('client_id', auth()->id());
            })->with(['driver', 'vehicle', 'warehouseRequest'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        }
        
        return view('tracking.outgoing', compact('shipments'));
    }

    public function pickups()
    {
        $user = auth()->user();
        
        if ($user->is_admin || $user->role == 'admin') {
            // Admin sees all pickup requests
            $pickups = PickupRequest::with(['client', 'driver', 'vehicle'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            // Client sees only their own pickup requests
            $pickups = PickupRequest::where('client_id', auth()->id())
                ->with(['driver', 'vehicle'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }
        
        return view('tracking.pickups', compact('pickups'));
    }

    public function trackShipment($id)
    {
        $user = auth()->user();
        
        if ($user->is_admin || $user->role == 'admin') {
            // Admin can view any shipment
            $shipment = DispatchOrder::with(['driver', 'vehicle', 'warehouseRequest', 'warehouseRequest.client'])
                ->findOrFail($id);
        } else {
            // Client can only view their own shipments
            $shipment = DispatchOrder::whereHas('warehouseRequest', function($q) {
                $q->where('client_id', auth()->id());
            })->with(['driver', 'vehicle', 'warehouseRequest'])
            ->findOrFail($id);
        }
        
        return view('tracking.show', compact('shipment'));
    }
}