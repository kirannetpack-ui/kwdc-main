<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\DispatchOrder;
use App\Models\User;
use App\Models\Invoice;
use App\Models\EquipmentJob;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index()
    {
        $data = [
            'summary' => $this->getSummary(),
            'revenue_chart' => $this->getRevenueChart(),
            'dispatch_chart' => $this->getDispatchChart(),
            'top_warehouses' => $this->getTopWarehouses(),
            'top_drivers' => $this->getTopDrivers(),
            'recent_activity' => $this->getRecentActivity(),
            'status_distribution' => $this->getStatusDistribution(),
        ];

        return view('reports.index', $data);
    }

    private function getSummary()
    {
        return [
            'total_warehouses' => Warehouse::count(),
            'total_dispatches' => DispatchOrder::count(),
            'total_users' => User::count(),
            'total_revenue' => DispatchOrder::where('status', 'delivered')->sum('base_price') ?? 0,
            'pending_warehouses' => Warehouse::where('status', 'pending')->count(),
            'active_drivers' => User::where('role', 'driver')->where('is_active', true)->count(),
            'completed_jobs' => DispatchOrder::where('status', 'delivered')->count(),
            'this_month_revenue' => DispatchOrder::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('status', 'delivered')
                ->sum('base_price') ?? 0,
        ];
    }

    private function getRevenueChart()
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenue = DispatchOrder::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->where('status', 'delivered')
                ->sum('base_price') ?? 0;
            
            $data[] = [
                'month' => $date->format('M'),
                'revenue' => $revenue,
            ];
        }
        return $data;
    }

    private function getDispatchChart()
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = DispatchOrder::whereDate('created_at', $date)->count();
            
            $data[] = [
                'date' => $date->format('d M'),
                'count' => $count,
            ];
        }
        return $data;
    }

    private function getTopWarehouses()
    {
        return Warehouse::withCount('warehouseRequests')
            ->orderBy('warehouse_requests_count', 'desc')
            ->limit(10)
            ->get();
    }

    private function getTopDrivers()
    {
        return User::where('role', 'driver')
            ->withCount('dispatchOrders')
            ->orderBy('dispatch_orders_count', 'desc')
            ->limit(10)
            ->get();
    }

    private function getRecentActivity()
    {
        $activities = collect();

        // Recent dispatches
        $dispatches = DispatchOrder::with('client')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'dispatch',
                    'title' => 'New Dispatch #' . $item->id,
                    'description' => 'By ' . ($item->client->name ?? 'Unknown'),
                    'time' => $item->created_at->diffForHumans(),
                    'icon' => 'fa-truck',
                    'color' => 'primary',
                ];
            });

        // New warehouses
        $warehouses = Warehouse::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'warehouse',
                    'title' => 'New Warehouse',
                    'description' => $item->name . ' by ' . ($item->user->name ?? 'Unknown'),
                    'time' => $item->created_at->diffForHumans(),
                    'icon' => 'fa-warehouse',
                    'color' => 'success',
                ];
            });

        // New users
        $users = User::orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($item) {
                return [
                    'type' => 'user',
                    'title' => 'New User',
                    'description' => $item->name . ' (' . $item->role . ')',
                    'time' => $item->created_at->diffForHumans(),
                    'icon' => 'fa-user',
                    'color' => 'info',
                ];
            });

        $activities = $dispatches->concat($warehouses)->concat($users)
            ->sortByDesc('time')
            ->take(15);

        return $activities;
    }

    private function getStatusDistribution()
    {
        return [
            'dispatches' => DB::table('dispatch_orders')
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'warehouses' => DB::table('warehouses')
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
        ];
    }

    public function export(Request $request)
    {
        $type = $request->type;
        $format = $request->format ?? 'excel';

        // Export logic
        return redirect()->back()->with('success', 'Report exported successfully!');
    }
}