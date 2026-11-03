<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DispatchOrder;
use App\Models\PickupRequest;
use App\Models\Notification;
use App\Models\Vehicle;
use App\Models\DriverRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Driver Dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $userId = $user->id;

        $stats = [
            'total_jobs' => DispatchOrder::where('driver_id', $userId)->count(),
            'active_jobs' => DispatchOrder::where('driver_id', $userId)
                ->whereIn('status', ['assigned', 'picked_up', 'on_the_way'])
                ->count(),
            'completed_jobs' => DispatchOrder::where('driver_id', $userId)
                ->where('status', 'delivered')
                ->count(),
            'total_earnings' => DispatchOrder::where('driver_id', $userId)
                ->where('status', 'delivered')
                ->sum('driver_earning') ?? 0,
            'total_pickups' => PickupRequest::where('driver_id', $userId)->count(),
            'active_pickups' => PickupRequest::where('driver_id', $userId)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->count(),
            'completed_pickups' => PickupRequest::where('driver_id', $userId)
                ->where('status', 'completed')
                ->count(),
            'vehicles' => Vehicle::where('driver_id', $userId)->count(),
        ];

        $recentJobs = DispatchOrder::where('driver_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentPickups = PickupRequest::where('driver_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $availableJobs = DispatchOrder::whereNull('driver_id')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('driver.dashboard', compact('stats', 'recentJobs', 'recentPickups', 'availableJobs'));
    }

    /**
     * Display pickup jobs for the driver
     */
    public function pickupJobs()
{
    $user = Auth::user();
    $userId = $user->id;

    // Get pickup requests assigned to this driver
    $pickups = PickupRequest::where('driver_id', $userId)
        ->whereIn('status', ['pending', 'assigned', 'in_progress'])
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    // Get completed pickups - use created_at if completed_at doesn't exist
    $completedPickups = PickupRequest::where('driver_id', $userId)
        ->where('status', 'completed')
        ->orderBy('completed_at', 'desc')
        ->limit(5)
        ->get();

    // If completed_at column doesn't exist, order by updated_at
    if ($completedPickups->isEmpty()) {
        $completedPickups = PickupRequest::where('driver_id', $userId)
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
    }

    // Get available pickup requests
    $availablePickups = PickupRequest::whereNull('driver_id')
        ->where('status', 'pending')
        ->orderBy('created_at', 'asc')
        ->limit(10)
        ->get();

    // Stats
    $stats = [
        'total_pickups' => PickupRequest::where('driver_id', $userId)->count(),
        'pending_pickups' => PickupRequest::where('driver_id', $userId)
            ->where('status', 'pending')
            ->count(),
        'active_pickups' => PickupRequest::where('driver_id', $userId)
            ->whereIn('status', ['assigned', 'in_progress'])
            ->count(),
        'completed_pickups' => PickupRequest::where('driver_id', $userId)
            ->where('status', 'completed')
            ->count(),
        'cancelled_pickups' => PickupRequest::where('driver_id', $userId)
            ->where('status', 'cancelled')
            ->count(),
        'total_earnings' => PickupRequest::where('driver_id', $userId)
            ->where('status', 'completed')
            ->sum('total_price') ?? 0,
    ];

    return view('driver.pickups', compact('pickups', 'completedPickups', 'availablePickups', 'stats'));
}


    /**
     * Complete a pickup job
     */
    public function completePickup($id)
    {
        $pickup = PickupRequest::where('driver_id', Auth::id())
            ->whereIn('status', ['assigned', 'in_progress'])
            ->findOrFail($id);

        $pickup->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Update pickup stops status
        if ($pickup->stops) {
            foreach ($pickup->stops as $stop) {
                $stop->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
            }
        }

        // Create notification for client
        if ($pickup->client_id) {
            Notification::create([
                'user_id' => $pickup->client_id,
                'title' => 'Pickup Completed',
                'message' => 'Your pickup request #' . $pickup->tracking_id . ' has been completed by ' . Auth::user()->name,
                'type' => 'success',
                'is_read' => false,
            ]);
        }

        return redirect()->route('driver.pickups')
            ->with('success', 'Pickup completed successfully!');
    }

    /**
     * Accept a pickup job
     */
    public function acceptPickup($id)
    {
        $pickup = PickupRequest::whereNull('driver_id')
            ->where('status', 'pending')
            ->findOrFail($id);

        $pickup->update([
            'driver_id' => Auth::id(),
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        // Create notification for client
        if ($pickup->client_id) {
            Notification::create([
                'user_id' => $pickup->client_id,
                'title' => 'Pickup Assigned',
                'message' => 'Your pickup request #' . $pickup->tracking_id . ' has been assigned to ' . Auth::user()->name,
                'type' => 'info',
                'is_read' => false,
            ]);
        }

        return redirect()->route('driver.pickups')
            ->with('success', 'Pickup accepted successfully!');
    }

    /**
     * Start a pickup job
     */
    public function startPickup($id)
    {
        $pickup = PickupRequest::where('driver_id', Auth::id())
            ->where('status', 'assigned')
            ->findOrFail($id);

        $pickup->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return redirect()->route('driver.pickups')
            ->with('success', 'Pickup started successfully!');
    }

    /**
     * Cancel a pickup job
     */
    public function cancelPickup($id)
    {
        $pickup = PickupRequest::where('driver_id', Auth::id())
            ->whereIn('status', ['assigned', 'in_progress'])
            ->findOrFail($id);

        $pickup->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Create notification for client
        if ($pickup->client_id) {
            Notification::create([
                'user_id' => $pickup->client_id,
                'title' => 'Pickup Cancelled',
                'message' => 'Your pickup request #' . $pickup->tracking_id . ' has been cancelled by ' . Auth::user()->name,
                'type' => 'warning',
                'is_read' => false,
            ]);
        }

        return redirect()->route('driver.pickups')
            ->with('warning', 'Pickup cancelled successfully!');
    }

    /**
     * Display available jobs for drivers
     */
    public function availableJobs()
    {
        $jobs = DispatchOrder::whereNull('driver_id')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('driver.available-jobs', [
            'jobs' => $jobs,
            'availableJobs' => $jobs,
        ]);
    }

/**
 * Start a job
 */
public function start($id)
{
    $job = DispatchOrder::where('driver_id', Auth::id())
        ->where('status', 'assigned')
        ->findOrFail($id);

    $job->update([
        'status' => 'picked_up',
        'picked_up_at' => now(),
    ]);

    return redirect()->route('driver.jobs')
        ->with('success', 'Job started successfully!');
}

/**
 * Cancel a job
 */
public function cancel($id)
{
    $job = DispatchOrder::where('driver_id', Auth::id())
        ->whereIn('status', ['assigned', 'picked_up'])
        ->findOrFail($id);

    $job->update([
        'status' => 'cancelled',
        'cancelled_at' => now(),
    ]);

    // Create notification for client
    if ($job->client_id) {
        Notification::create([
            'user_id' => $job->client_id,
            'title' => 'Job Cancelled',
            'message' => 'Your job #' . $job->id . ' has been cancelled by the driver.',
            'type' => 'warning',
            'is_read' => false,
        ]);
    }

    return redirect()->route('driver.jobs')
        ->with('warning', 'Job cancelled successfully!');
}

    /**
 * Display driver's jobs
 */
public function jobs()
{
    $user = Auth::user();
    $userId = $user->id;
    
    // Get active jobs (assigned, picked_up, on_the_way)
    $activeJobs = DispatchOrder::where('driver_id', $userId)
        ->whereIn('status', ['assigned', 'picked_up', 'on_the_way'])
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    
    // Get completed jobs
    $completedJobs = DispatchOrder::where('driver_id', $userId)
        ->where('status', 'delivered')
        ->orderBy('delivered_at', 'desc')
        ->paginate(10);
    
    // Get cancelled jobs
    $cancelledJobs = DispatchOrder::where('driver_id', $userId)
        ->where('status', 'cancelled')
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    
    // Get pending jobs (available to accept)
    $availableJobs = DispatchOrder::whereNull('driver_id')
        ->where('status', 'pending')
        ->orderBy('created_at', 'desc')
        ->paginate(10);
    
    return view('driver.jobs', compact('activeJobs', 'completedJobs', 'cancelledJobs', 'availableJobs'));
}
    /**
     * Accept a job
     */
    public function accept($id)
    {
        $job = DispatchOrder::whereNull('driver_id')
            ->where('status', 'pending')
            ->findOrFail($id);

        $job->update([
            'driver_id' => Auth::id(),
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);

        return redirect()->route('driver.jobs')
            ->with('success', 'Job accepted successfully!');
    }

    /**
     * Deliver a job
     */
    public function deliver($id)
    {
        $job = DispatchOrder::where('driver_id', Auth::id())
            ->where('status', 'assigned')
            ->findOrFail($id);

        $job->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        return redirect()->route('driver.jobs')
            ->with('success', 'Job delivered successfully!');
    }

    /**
     * Upload proof of delivery
     */
    public function uploadProof(Request $request, $id)
    {
        $job = DispatchOrder::where('driver_id', Auth::id())
            ->findOrFail($id);

        $request->validate([
            'proof_image' => 'required|image|max:2048',
        ]);

        $path = $request->file('proof_image')->store('proofs', 'public');
        
        $job->update([
            'proof_image' => $path,
        ]);

        return redirect()->route('driver.jobs')
            ->with('success', 'Proof uploaded successfully!');
    }

    /**
     * Display driver earnings
     */
    public function earnings()
    {
        $user = Auth::user();
        $userId = $user->id;

        $earnings = DispatchOrder::where('driver_id', $userId)
            ->where('status', 'delivered')
            ->orderBy('delivered_at', 'desc')
            ->paginate(10);

        $totalEarnings = DispatchOrder::where('driver_id', $userId)
            ->where('status', 'delivered')
            ->sum('driver_earning') ?? 0;

        $monthlyEarnings = DispatchOrder::where('driver_id', $userId)
            ->where('status', 'delivered')
            ->whereMonth('delivered_at', now()->month)
            ->sum('driver_earning') ?? 0;

        $weeklyEarnings = DispatchOrder::where('driver_id', $userId)
            ->where('status', 'delivered')
            ->whereBetween('delivered_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('driver_earning') ?? 0;

        // Get pickup earnings
        $pickupEarnings = PickupRequest::where('driver_id', $userId)
            ->where('status', 'completed')
            ->sum('total_price') ?? 0;

        return view('driver.earnings', compact(
            'earnings',
            'totalEarnings',
            'monthlyEarnings',
            'weeklyEarnings',
            'pickupEarnings'
        ));
    }

    /**
     * Display driver rates
     */
    public function rates()
    {
        $user = Auth::user();
        $rates = DriverRate::where('driver_id', $user->id)
            ->orWhere('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('driver.rates.index', compact('rates'));
    }

    /**
     * Store driver rates
     */
    public function storeRates(Request $request)
    {
        $request->validate([
            'flat_rate_0_5' => 'required|numeric|min:0',
            'flat_rate_6_10' => 'required|numeric|min:0',
            'flat_rate_11_20' => 'required|numeric|min:0',
            'rate_per_km_21_plus' => 'required|numeric|min:0',
            'valid_until' => 'nullable|date|after:today',
        ]);

        DriverRate::create([
            'driver_id' => Auth::id(),
            'user_id' => Auth::id(),
            'vehicle_type' => $request->vehicle_type ?? 'Standard',
            'flat_rate_0_5' => $request->flat_rate_0_5,
            'flat_rate_6_10' => $request->flat_rate_6_10,
            'flat_rate_11_20' => $request->flat_rate_11_20,
            'rate_per_km_21_plus' => $request->rate_per_km_21_plus,
            'base_fare' => $request->base_fare ?? 0,
            'minimum_fare' => $request->minimum_fare ?? 0,
            'date' => now()->toDateString(),
            'valid_until' => $request->valid_until,
            'is_active' => true,
        ]);

        return redirect()->route('driver.rates')
            ->with('success', 'Rates saved successfully!');
    }

/**
 * Get completed pickups for driver
 */
public function completedPickups()
{
    $user = Auth::user();
    
    $pickups = PickupRequest::where('driver_id', $user->id)
        ->where('status', 'completed')
        ->orderBy('completed_at', 'desc')
        ->limit(5)
        ->get();
    
    return view('driver.pickups.completed', compact('pickups'));
}
}