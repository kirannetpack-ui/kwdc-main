<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DriverRate;
use App\Models\DispatchOrder;
use App\Models\WarehouseRequest;
use App\Models\Warehouse;

class ClientController extends Controller
{
    public function driverRates()
    {
        $driverRates = DriverRate::where('effective_from', '<=', now())
            ->where('effective_until', '>=', now())
            ->with('driver')
            ->orderBy('flat_rate_0_5')
            ->paginate(20);
        
        return view('client.driver-rates', compact('driverRates'));
    }
    
    public function reports()
    {
        $totalRequests = WarehouseRequest::where('client_id', auth()->id())->count();
        $completedOrders = DispatchOrder::whereHas('warehouseRequest', function($q) {
            $q->where('client_id', auth()->id());
        })->where('status', 'delivered')->count();
        
        $totalSpent = DispatchOrder::whereHas('warehouseRequest', function($q) {
            $q->where('client_id', auth()->id());
        })->where('status', 'delivered')->sum('base_price');
        
        $recentOrders = DispatchOrder::whereHas('warehouseRequest', function($q) {
            $q->where('client_id', auth()->id());
        })->latest()->take(10)->get();
        
        return view('client.reports', compact('totalRequests', 'completedOrders', 'totalSpent', 'recentOrders'));
    }
    
    public function dashboard()
    {
        $stats = [
            'total_requests' => WarehouseRequest::where('client_id', auth()->id())->count(),
            'active_requests' => WarehouseRequest::where('client_id', auth()->id())->where('status', 'pending')->count(),
            'total_orders' => DispatchOrder::whereHas('warehouseRequest', function($q) {
                $q->where('client_id', auth()->id());
            })->count(),
            'total_spent' => DispatchOrder::whereHas('warehouseRequest', function($q) {
                $q->where('client_id', auth()->id());
            })->where('status', 'delivered')->sum('base_price') ?? 0,
        ];
        
        $recentRequests = WarehouseRequest::where('client_id', auth()->id())
            ->with('warehouse')
            ->latest()
            ->take(5)
            ->get();
            
        $activeOrders = DispatchOrder::whereHas('warehouseRequest', function($q) {
            $q->where('client_id', auth()->id());
        })->whereNotIn('status', ['delivered', 'cancelled'])
        ->with('driver')
        ->latest()
        ->take(5)
        ->get();
        
        return view('client.dashboard', compact('stats', 'recentRequests', 'activeOrders'));
    }
    
    public function warehouses()
    {
        $warehouses = Warehouse::where('status', 'approved')->get();
        return view('client.warehouses', compact('warehouses'));
    }
    
    public function warehouseShow($id)
    {
        $warehouse = Warehouse::with('owner')->findOrFail($id);
        return view('client.warehouse-show', compact('warehouse'));
    }
}