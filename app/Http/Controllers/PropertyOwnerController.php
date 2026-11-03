<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseRequest;
use App\Models\DispatchOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PropertyOwnerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display pending warehouses for property owner
     */
    public function pending()
    {
        $pendingWarehouses = Warehouse::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('property.pending', compact('pendingWarehouses'));
    }

    /**
     * Display approved warehouses for property owner
     */
    public function approved()
    {
        $approvedWarehouses = Warehouse::where('user_id', Auth::id())
            ->where('status', 'approved')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('property.approved', compact('approvedWarehouses'));
    }

    /**
     * Display rejected warehouses for property owner
     */
    public function rejected()
    {
        $rejectedWarehouses = Warehouse::where('user_id', Auth::id())
            ->where('status', 'rejected')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('property.rejected', compact('rejectedWarehouses'));
    }

    /**
     * Display warehouse requests for property owner
     */
    public function requests()
    {
        $warehouseRequests = WarehouseRequest::whereHas('warehouse', function($q) {
            $q->where('user_id', Auth::id());
        })->with(['client', 'warehouse'])
          ->orderBy('created_at', 'desc')
          ->paginate(20);
        
        return view('property.requests.index', compact('warehouseRequests'));
    }

    /**
     * Approve a warehouse request
     */
    public function approveRequest($id)
    {
        $request = WarehouseRequest::whereHas('warehouse', function($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($id);
        
        $updated = WarehouseRequest::whereKey($request->id)->where('status', 'pending')->update(['status' => 'approved']);
        abort_unless($updated, 409, 'This request has already been reviewed.');
        
        return redirect()->back()->with('success', 'Warehouse request approved successfully!');
    }

    /**
     * Reject a warehouse request
     */
    public function rejectRequest($id)
    {
        $request = WarehouseRequest::whereHas('warehouse', function($q) {
            $q->where('user_id', Auth::id());
        })->findOrFail($id);
        
        $updated = WarehouseRequest::whereKey($request->id)->where('status', 'pending')->update(['status' => 'rejected']);
        abort_unless($updated, 409, 'This request has already been reviewed.');
        
        return redirect()->back()->with('success', 'Warehouse request rejected.');
    }

    /**
     * Display property analytics and reports
     */
    public function analytics()
    {
        $userId = Auth::id();
        
        // Get all warehouses for this owner
        $warehouses = Warehouse::where('user_id', $userId)->get();
        $warehouseIds = $warehouses->pluck('id');
        
        // Calculate statistics
        $stats = [
            'total_properties' => $warehouses->count(),
            'approved_properties' => $warehouses->where('status', 'approved')->count(),
            'pending_properties' => $warehouses->where('status', 'pending')->count(),
            'rejected_properties' => $warehouses->where('status', 'rejected')->count(),
            'total_requests' => WarehouseRequest::whereHas('warehouse', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count(),
            'pending_requests' => WarehouseRequest::whereHas('warehouse', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'pending')->count(),
            'approved_requests' => WarehouseRequest::whereHas('warehouse', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'approved')->count(),
            'total_revenue' => DispatchOrder::whereHas('warehouse', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'delivered')->sum('base_price') ?? 0,
        ];
        
        // Get recent activity
        $recentRequests = WarehouseRequest::whereHas('warehouse', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with(['client', 'warehouse'])
          ->orderBy('created_at', 'desc')
          ->limit(10)
          ->get();
        
        // Monthly revenue chart data
        $monthlyRevenue = $this->getMonthlyRevenue($userId);
        
        return view('property.analytics', compact('stats', 'recentRequests', 'monthlyRevenue'));
    }

    /**
     * Get monthly revenue data for chart
     */
    private function getMonthlyRevenue($userId)
    {
        $monthlyData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = DispatchOrder::whereHas('warehouse', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'delivered')
              ->whereYear('delivered_at', $month->year)
              ->whereMonth('delivered_at', $month->month)
              ->sum('base_price') ?? 0;
            
            $monthlyData[] = [
                'month' => $month->format('M'),
                'revenue' => $revenue,
            ];
        }
        
        return $monthlyData;
    }

    /**
     * Show specific warehouse details for property owner
     */
    public function showWarehouse($id)
    {
        $warehouse = Warehouse::where('user_id', Auth::id())
            ->with(['warehouseRequests.client'])
            ->findOrFail($id);
        
        // Get related dispatches
        $dispatches = DispatchOrder::where('warehouse_id', $id)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        return view('property.warehouses.show', compact('warehouse', 'dispatches'));
    }

    /**
     * Edit warehouse
     */
    public function editWarehouse($id)
    {
        $warehouse = Warehouse::where('user_id', Auth::id())->findOrFail($id);
        return view('property.warehouses.edit', compact('warehouse'));
    }

    /**
     * Update warehouse
     */
    public function updateWarehouse(Request $request, $id)
    {
        $warehouse = Warehouse::where('user_id', Auth::id())->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:500',
            'area_sqft' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'price_per_sqft' => 'nullable|numeric|min:0',
        ]);
        
        $warehouse->update($validated);
        
        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse updated successfully!');
    }

    /**
     * Delete warehouse
     */
    public function destroyWarehouse($id)
    {
        $warehouse = Warehouse::where('user_id', Auth::id())->findOrFail($id);
        
        // Delete associated files
        if ($warehouse->front_image) {
            Storage::disk('public')->delete($warehouse->front_image);
        }
        if ($warehouse->interior_image) {
            Storage::disk('public')->delete($warehouse->interior_image);
        }
        if ($warehouse->exterior_image) {
            Storage::disk('public')->delete($warehouse->exterior_image);
        }
        
        $warehouse->delete();
        
        return redirect()->route('warehouses.index')
            ->with('success', 'Warehouse deleted successfully!');
    }
}
