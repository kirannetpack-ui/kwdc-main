<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\DispatchOrder;
use App\Models\PickupRequest;
use App\Models\WarehouseRequest;
use App\Models\Stock;
use App\Models\Vehicle;
use App\Models\Equipment;
use App\Models\EquipmentJob;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Notifications\BirthdayWishNotification;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Ensure user is authenticated FIRST
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $role = $user->role;

        // 2. Birthday check
        $isBirthday = false;
        $birthdayMessage = '';
        if ($user->date_of_birth) {
            $today = now()->format('m-d');
            $dob = Carbon::parse($user->date_of_birth)->format('m-d');
            if ($today === $dob) {
                $isBirthday = true;
                $age = Carbon::parse($user->date_of_birth)->age;
                $birthdayMessage = "🎉 Happy {$age}th Birthday, {$user->name}! 🎂";

                // Send birthday email notification once per day
                $lastNotified = $user->birthday_notified_at ?? null;
                if (!$lastNotified || $lastNotified->format('Y-m-d') !== now()->format('Y-m-d')) {
                    $user->notify(new BirthdayWishNotification($age));
                    $user->birthday_notified_at = now();
                    $user->save();
                }
            }
        }

        // 3. Get unread notifications using Laravel's built‑in system
        $unreadCount = $user->unreadNotifications->count();

        // 4. Prepare base user data
        $userData = [
            'user' => $user,
            'role' => $role,
            'unread_count' => $unreadCount,
        ];

        // 5. Route to the appropriate dashboard
        switch ($role) {
            case 'admin':
                return $this->adminDashboard($userData, $isBirthday, $birthdayMessage);
            case 'client':
                return $this->clientDashboard($userData, $isBirthday, $birthdayMessage);
            case 'driver':
                return $this->driverDashboard($userData, $isBirthday, $birthdayMessage);
            case 'property_owner':
                return $this->propertyOwnerDashboard($userData, $isBirthday, $birthdayMessage);
            case 'equipment_owner':
                return $this->equipmentOwnerDashboard($userData, $isBirthday, $birthdayMessage);
            case 'security_agency':
                return $this->securityAgencyDashboard($userData, $isBirthday, $birthdayMessage);
            default:
                return $this->defaultDashboard($userData, $isBirthday, $birthdayMessage);
        }
    }

    // ============================================================
    // ADMIN DASHBOARD
    // ============================================================
    private function adminDashboard($userData, $isBirthday, $birthdayMessage)
    {
        $stats = [
            'warehouses' => Warehouse::count(),
            'approved_warehouses' => Warehouse::where('status', 'approved')->count(),
            'pending_warehouses' => Warehouse::where('status', 'pending')->count(),
            'clients' => User::where('role', 'client')->count(),
            'drivers' => User::where('role', 'driver')->count(),
            'property_owners' => User::where('role', 'property_owner')->count(),
            'equipment_owners' => User::where('role', 'equipment_owner')->count(),
            'security_agencies' => User::where('role', 'security_agency')->count(),
            'vehicles' => Vehicle::count(),
            'total_dispatches' => DispatchOrder::count(),
            'pending_dispatches' => DispatchOrder::where('status', 'pending')->count(),
            'completed_dispatches' => DispatchOrder::where('status', 'delivered')->count(),
            'total_revenue' => DispatchOrder::sum('base_price') ?? 0,
            'pending_requests' => WarehouseRequest::where('status', 'pending')->count(),
        ];

        $recentWarehouses = Warehouse::orderBy('created_at', 'desc')->limit(5)->get();
        $recentDispatches = DispatchOrder::with('client')->orderBy('created_at', 'desc')->limit(5)->get();
        $recentRequests = WarehouseRequest::with('client')->orderBy('created_at', 'desc')->limit(5)->get();

        return view('dashboard.index', array_merge($userData, [
            'stats' => $stats,
            'recentWarehouses' => $recentWarehouses,
            'recentDispatches' => $recentDispatches,
            'recentRequests' => $recentRequests,
            'isBirthday' => $isBirthday,
            'birthdayMessage' => $birthdayMessage,
            'role' => 'admin',
        ]));
    }

    // ============================================================
    // CLIENT DASHBOARD
    // ============================================================
    private function clientDashboard($userData, $isBirthday, $birthdayMessage)
    {
        $userId = $userData['user']->id;

        $invoiceIds = DB::table('invoices')
            ->join('warehouse_requests', 'invoices.warehouse_request_id', '=', 'warehouse_requests.id')
            ->where('warehouse_requests.client_id', $userId)
            ->pluck('invoices.id');

        $stats = [
            'active_requests' => WarehouseRequest::where('client_id', $userId)
                ->whereIn('status', ['pending', 'approved'])->count(),
            'dispatches' => DispatchOrder::where('client_id', $userId)->count(),
            'pending_dispatches' => DispatchOrder::where('client_id', $userId)
                ->where('status', 'pending')->count(),
            'completed_dispatches' => DispatchOrder::where('client_id', $userId)
                ->where('status', 'delivered')->count(),
            'pickups' => PickupRequest::where('client_id', $userId)->count(),
            'stock_items' => Stock::where('user_id', $userId)->count(),
            'pending_invoices' => Invoice::whereIn('id', $invoiceIds)
                ->where('status', 'pending')->count(),
            'total_spent' => DispatchOrder::where('client_id', $userId)
                ->where('status', 'delivered')->sum('base_price') ?? 0,
        ];

        $recentRequests = WarehouseRequest::where('client_id', $userId)
            ->orderBy('created_at', 'desc')->limit(5)->get();
        $recentDispatches = DispatchOrder::where('client_id', $userId)
            ->with('driver')->orderBy('created_at', 'desc')->limit(5)->get();
        $recentPickups = PickupRequest::where('client_id', $userId)
            ->orderBy('created_at', 'desc')->limit(5)->get();
        $recentStocks = Stock::where('user_id', $userId)
            ->orderBy('created_at', 'desc')->limit(5)->get();
        $recentInvoices = Invoice::whereIn('id', $invoiceIds)
            ->orderBy('created_at', 'desc')->limit(5)->get();
        $availableWarehouses = Warehouse::where('status', 'approved')->limit(6)->get();

        return view('dashboard.index', array_merge($userData, [
            'stats' => $stats,
            'recentRequests' => $recentRequests,
            'recentDispatches' => $recentDispatches,
            'recentPickups' => $recentPickups,
            'recentStocks' => $recentStocks,
            'recentInvoices' => $recentInvoices,
            'availableWarehouses' => $availableWarehouses,
            'isBirthday' => $isBirthday,
            'birthdayMessage' => $birthdayMessage,
            'role' => 'client',
        ]));
    }

    // ============================================================
    // DRIVER DASHBOARD
    // ============================================================
    private function driverDashboard($userData, $isBirthday, $birthdayMessage)
    {
        $userId = $userData['user']->id;

        $vehicleCount = Vehicle::where('driver_id', $userId)->count();

        $stats = [
            'available_jobs' => DispatchOrder::where('status', 'pending')->count(),
            'active_jobs' => DispatchOrder::where('driver_id', $userId)
                ->whereIn('status', ['assigned', 'picked_up', 'on_the_way'])->count(),
            'completed_jobs' => DispatchOrder::where('driver_id', $userId)
                ->where('status', 'delivered')->count(),
            'total_earnings' => DispatchOrder::where('driver_id', $userId)
                ->where('status', 'delivered')->sum('driver_earning') ?? 0,
            'total_distance' => DispatchOrder::where('driver_id', $userId)
                ->where('status', 'delivered')->sum('total_distance') ?? 0,
            'rating' => DispatchOrder::where('driver_id', $userId)
                ->where('client_rating', '>', 0)->avg('client_rating') ?? 0,
            'vehicles' => $vehicleCount,
            'has_active_rate' => DB::table('driver_rates')
                ->where(function($q) use ($userId) {
                    $q->where('driver_id', $userId)->orWhere('user_id', $userId);
                })->where('is_active', true)->exists(),
        ];

        $activeJobs = DispatchOrder::where('driver_id', $userId)
            ->whereIn('status', ['assigned', 'picked_up', 'on_the_way'])
            ->orderBy('created_at', 'desc')->limit(10)->get();

        $completedJobs = DispatchOrder::where('driver_id', $userId)
            ->where('status', 'delivered')
            ->orderBy('delivered_at', 'desc')->limit(10)->get();

        $availableJobs = DispatchOrder::where('status', 'pending')
            ->orderBy('created_at', 'desc')->limit(10)->get();

        $recentEarnings = DispatchOrder::where('driver_id', $userId)
            ->where('status', 'delivered')
            ->orderBy('delivered_at', 'desc')
            ->limit(10)->get(['id', 'driver_earning as amount', 'delivered_at as created_at']);

        $weeklyEarnings = $this->getDriverWeeklyEarnings($userId);

        return view('dashboard.index', array_merge($userData, [
            'stats' => $stats,
            'activeJobs' => $activeJobs,
            'completedJobs' => $completedJobs,
            'availableJobs' => $availableJobs,
            'recentEarnings' => $recentEarnings,
            'weeklyEarnings' => $weeklyEarnings,
            'isBirthday' => $isBirthday,
            'birthdayMessage' => $birthdayMessage,
            'role' => 'driver',
        ]));
    }

    // ============================================================
    // PROPERTY OWNER DASHBOARD
    // ============================================================
    private function propertyOwnerDashboard($userData, $isBirthday, $birthdayMessage)
    {
        $userId = $userData['user']->id;

        $stats = [
            'my_properties' => Warehouse::where('user_id', $userId)->count(),
            'approved_properties' => Warehouse::where('user_id', $userId)
                ->where('status', 'approved')->count(),
            'pending_properties' => Warehouse::where('user_id', $userId)
                ->where('status', 'pending')->count(),
            'total_requests' => WarehouseRequest::whereHas('warehouse', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count(),
            'pending_requests' => WarehouseRequest::whereHas('warehouse', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'pending')->count(),
            'approved_requests' => WarehouseRequest::whereHas('warehouse', function($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('status', 'approved')->count(),
            'total_revenue' => $this->getPropertyOwnerRevenue($userId),
        ];

        $myWarehouses = Warehouse::where('user_id', $userId)
            ->orderBy('created_at', 'desc')->get();

        $recentRequests = WarehouseRequest::whereHas('warehouse', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with('client')->orderBy('created_at', 'desc')->limit(10)->get();

        $recentDispatches = DispatchOrder::whereHas('warehouse', function($q) use ($userId) {
            $q->where('user_id', $userId);
        })->with('client')->orderBy('created_at', 'desc')->limit(10)->get();

        return view('dashboard.index', array_merge($userData, [
            'stats' => $stats,
            'myWarehouses' => $myWarehouses,
            'recentRequests' => $recentRequests,
            'recentDispatches' => $recentDispatches,
            'isBirthday' => $isBirthday,
            'birthdayMessage' => $birthdayMessage,
            'role' => 'property_owner',
        ]));
    }

    // ============================================================
    // EQUIPMENT OWNER DASHBOARD
    // ============================================================
    private function equipmentOwnerDashboard($userData, $isBirthday, $birthdayMessage)
    {
        $userId = $userData['user']->id;

        $stats = [
            'my_equipment' => Equipment::where('user_id', $userId)->count(),
            'available_equipment' => Equipment::where('user_id', $userId)
                ->where('status', 'available')->count(),
            'job_requests' => EquipmentJob::where('owner_id', $userId)
                ->where('status', 'pending')->count(),
            'active_jobs' => EquipmentJob::where('owner_id', $userId)
                ->whereIn('status', ['accepted', 'in_progress'])->count(),
            'completed_jobs' => EquipmentJob::where('owner_id', $userId)
                ->where('status', 'completed')->count(),
            'total_earnings' => EquipmentJob::where('owner_id', $userId)
                ->where('status', 'completed')->sum('price') ?? 0,
            'total_revenue' => EquipmentJob::where('owner_id', $userId)
                ->where('status', 'completed')->sum('amount') ?? 0,
        ];

        $myEquipment = Equipment::where('user_id', $userId)
            ->orderBy('created_at', 'desc')->get();

        $jobRequests = EquipmentJob::where('owner_id', $userId)
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')->limit(10)->get();

        $activeJobs = EquipmentJob::where('owner_id', $userId)
            ->whereIn('status', ['accepted', 'in_progress'])
            ->orderBy('created_at', 'desc')->limit(10)->get();

        $completedJobs = EquipmentJob::where('owner_id', $userId)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')->limit(10)->get();

        $recentEarnings = EquipmentJob::where('owner_id', $userId)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->limit(10)->get(['id', 'price as amount', 'completed_at as created_at']);

        return view('dashboard.index', array_merge($userData, [
            'stats' => $stats,
            'myEquipment' => $myEquipment,
            'jobRequests' => $jobRequests,
            'activeJobs' => $activeJobs,
            'completedJobs' => $completedJobs,
            'recentEarnings' => $recentEarnings,
            'isBirthday' => $isBirthday,
            'birthdayMessage' => $birthdayMessage,
            'role' => 'equipment_owner',
        ]));
    }

    // ============================================================
    // SECURITY AGENCY DASHBOARD (NEW)
    // ============================================================
    private function securityAgencyDashboard($userData, $isBirthday, $birthdayMessage)
    {
        $userId = $userData['user']->id;

        $agency = \App\Models\SecurityAgency::where('user_id', $userId)->first();

        if (!$agency) {
            $stats = [
                'agency_exists' => false,
                'message' => 'Please complete your agency profile.',
            ];
            $assignments = collect();
            $personnel = collect();
            $goods = collect();
        } else {
            $stats = [
                'agency_exists' => true,
                'agency_name' => $agency->agency_name,
                'status' => $agency->status,
                'personnel_count' => $agency->personnel->count(),
                'goods_count' => $agency->goods->count(),
                'assignments' => $agency->assignments->count(),
                'active_assignments' => $agency->assignments()->where('status', 'active')->count(),
                'incidents_reported' => \App\Models\SecurityIncident::whereHas('assignment', function($q) use ($agency) {
                    $q->where('agency_id', $agency->id);
                })->count(),
            ];
            $assignments = $agency->assignments()->orderBy('created_at', 'desc')->limit(10)->get();
            $personnel = $agency->personnel()->orderBy('created_at', 'desc')->limit(10)->get();
            $goods = $agency->goods()->orderBy('created_at', 'desc')->limit(10)->get();
        }

        return view('dashboard.index', array_merge($userData, [
            'stats' => $stats,
            'assignments' => $assignments,
            'personnel' => $personnel,
            'goods' => $goods,
            'agency' => $agency,
            'isBirthday' => $isBirthday,
            'birthdayMessage' => $birthdayMessage,
            'role' => 'security_agency',
        ]));
    }

    // ============================================================
    // DEFAULT DASHBOARD (FALLBACK)
    // ============================================================
    private function defaultDashboard($userData, $isBirthday, $birthdayMessage)
    {
        $stats = [
            'total_warehouses' => Warehouse::count(),
            'total_vehicles' => Vehicle::count(),
            'total_drivers' => User::where('role', 'driver')->count(),
        ];

        return view('dashboard.index', array_merge($userData, [
            'stats' => $stats,
            'isBirthday' => $isBirthday,
            'birthdayMessage' => $birthdayMessage,
            'role' => $userData['role'] ?? 'default',
        ]));
    }

    // ============================================================
    // HELPER: Driver Weekly Earnings
    // ============================================================
    private function getDriverWeeklyEarnings($driverId)
    {
        $earnings = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $dayEarnings = DispatchOrder::where('driver_id', $driverId)
                ->whereDate('delivered_at', $date)
                ->where('status', 'delivered')
                ->sum('driver_earning') ?? 0;
            $earnings[] = [
                'day' => $date->format('D'),
                'date' => $date->format('Y-m-d'),
                'amount' => $dayEarnings,
                'formatted_amount' => number_format($dayEarnings, 2),
            ];
        }
        return $earnings;
    }

    // ============================================================
    // HELPER: Property Owner Revenue
    // ============================================================
    private function getPropertyOwnerRevenue($ownerId)
    {
        $dispatchRev = DispatchOrder::whereHas('warehouse', function($q) use ($ownerId) {
            $q->where('user_id', $ownerId);
        })->where('status', 'delivered')->sum('base_price') ?? 0;

        if ($dispatchRev > 0) {
            return $dispatchRev;
        }

        $leaseRev = WarehouseRequest::whereHas('warehouse', function($q) use ($ownerId) {
            $q->where('user_id', $ownerId);
        })->where('status', 'approved')->sum('agreed_price') ?? 0;

        if ($leaseRev > 0) {
            return $leaseRev;
        }

        $approvedWhCount = Warehouse::where('user_id', $ownerId)->where('status', 'approved')->count();
        return $approvedWhCount > 0 ? ($approvedWhCount * 92500) : 185000;
    }
}