<?php

namespace App\Http\Controllers;

use App\Models\PickupRequest;
use App\Models\PickupStop;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\DriverRate;
use App\Models\Notification;
use App\Services\InvoiceService;
use App\Services\AdminEmailService;
use App\Services\AIService; // <-- Added AIService here
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Events\OrderDelivered;

class PickupRequestController extends Controller
{
    protected $invoiceService;
    protected $adminEmailService;
    protected $aiService; // <-- Added AIService property

    public function __construct(InvoiceService $invoiceService, AdminEmailService $adminEmailService, AIService $aiService)
    {
        $this->middleware('auth');
        $this->invoiceService = $invoiceService;
        $this->adminEmailService = $adminEmailService;
        $this->aiService = $aiService; // <-- Inject AIService
    }

    /**
     * Display a listing of pickup requests
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PickupRequest::with(['client', 'driver', 'warehouse', 'stops']);
        
        // Filter by status
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        }
        
        // Role-based filtering
        if ($user->role == 'client') {
            $query->where('client_id', $user->id);
        } elseif ($user->role == 'driver') {
            $query->where('driver_id', $user->id);
        }
        
        $pickups = $query->orderBy('created_at', 'desc')->paginate(20);
        
        // Get statistics
        $stats = $this->getPickupStats($user);
        
        return view('pickup.index', compact('pickups', 'stats'));
    }

    /**
     * Show form for direct pickup creation
     */
    public function directCreate()
    {
        $user = Auth::user();
        
        if ($user->role != 'client') {
            return redirect()->route('dashboard')->with('error', 'Only clients can create pickups.');
        }
        
        // Get warehouses
        $warehouses = Warehouse::where('status', 'approved')->orderBy('name')->get();
        
        // Get assigned warehouses
        $assignedWarehouses = $this->getAssignedWarehouses($user);
        
        // Get available drivers
        $availableDrivers = $this->getAvailableDrivers();
        
        return view('pickup.direct-create', compact(
            'warehouses',
            'assignedWarehouses',
            'availableDrivers'
        ));
    }

    /**
     * Show form for pickup creation
     */
    public function create()
    {
        $user = Auth::user();
        
        if ($user->role != 'client') {
            return redirect()->route('dashboard')->with('error', 'Only clients can create pickups.');
        }
        
        $warehouses = Warehouse::where('status', 'approved')->orderBy('name')->get();
        $assignedWarehouses = $this->getAssignedWarehouses($user);
        $availableDrivers = $this->getAvailableDrivers();
        
        return view('pickup.create', compact('warehouses', 'assignedWarehouses', 'availableDrivers'));
    }

    /**
     * Store a newly created pickup request
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $pickupStops = $request->input('pickup_stops');

        if (!$pickupStops && $request->has('delivery_stops')) {
            $pickupStops = collect($request->input('delivery_stops', []))
                ->map(function ($stop) use ($user) {
                    return [
                        'address' => $stop['address'] ?? '',
                        'contact_name' => $stop['contact_name'] ?? $stop['recipient_name'] ?? $user->name,
                        'contact_phone' => $stop['contact_phone'] ?? $stop['recipient_phone'] ?? ($user->phone ?? 'N/A'),
                        'items_description' => $stop['items_description'] ?? $stop['notes'] ?? null,
                        'estimated_weight' => $stop['estimated_weight'] ?? 0,
                        'latitude' => $stop['latitude'] ?? null,
                        'longitude' => $stop['longitude'] ?? null,
                    ];
                })
                ->values()
                ->all();
        }

        if (!$pickupStops && $request->filled('pickup_address')) {
            $pickupStops = [[
                'address' => $request->pickup_address,
                'contact_name' => $request->input('contact_person', $request->input('pickup_contact_person', $user->name)),
                'contact_phone' => $request->input('contact_phone', $request->input('pickup_contact_phone', $user->phone ?? 'N/A')),
                'items_description' => $request->input('description', $request->input('items_description')),
                'estimated_weight' => $request->input('estimated_boxes', $request->input('weight', 0)),
                'latitude' => $request->pickup_latitude,
                'longitude' => $request->pickup_longitude,
            ]];
        }

        if ($pickupStops) {
            $request->merge(['pickup_stops' => $pickupStops]);
        }

        if (!$request->filled('total_price') && $request->filled('base_price')) {
            $request->merge(['total_price' => $request->base_price]);
        }
        
        $validator = Validator::make($request->all(), [
            'destination_warehouse_id' => 'nullable|exists:warehouses,id',
            'pickup_address' => 'nullable|string|max:500',
            'destination_address' => 'nullable|string|max:500',
            'pickup_latitude' => 'nullable|numeric|between:-90,90',
            'pickup_longitude' => 'nullable|numeric|between:-180,180',
            'destination_latitude' => 'nullable|numeric|between:-90,90',
            'destination_longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string',
            'items_description' => 'nullable|string',
            'estimated_boxes' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'scheduled_date' => 'nullable|date',
            'scheduled_time' => 'nullable',
            'pickup_stops' => 'required|array|min:1',
            'pickup_stops.*.address' => 'required|string|max:500',
            'pickup_stops.*.contact_name' => 'required|string|max:255',
            'pickup_stops.*.contact_phone' => 'required|string|max:20',
            'pickup_stops.*.items_description' => 'nullable|string',
            'pickup_stops.*.estimated_weight' => 'nullable|numeric|min:0',
            'pickup_stops.*.latitude' => 'nullable|numeric|between:-90,90',
            'pickup_stops.*.longitude' => 'nullable|numeric|between:-180,180',
            'total_distance' => 'nullable|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'driver_id' => 'nullable|exists:users,id',
            'bill_type' => 'nullable|in:regular,vat,pan',
            'pan_number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        
        try {
            // Generate tracking ID and invoice number
            $trackingId = $this->generateTrackingId();
            $invoiceNo = $this->generateInvoiceNumber();
            $totalDistance = (float) $request->input('total_distance', max(count($request->pickup_stops) * 4, 4));
            $totalPrice = (float) $request->input('total_price', max($totalDistance * 80, 500));
            $taxAmount = $request->bill_type === 'vat' ? round($totalPrice * 0.13, 2) : 0;
            $destinationAddress = $request->destination_address
                ?? optional(Warehouse::find($request->destination_warehouse_id))->address
                ?? optional(Warehouse::find($request->destination_warehouse_id))->location
                ?? ($request->pickup_stops[0]['address'] ?? null);
            $firstStop = collect($request->input('pickup_stops', []))->first();
            
            // Create pickup request
            $pickup = PickupRequest::create([
                'client_id' => $user->id,
                'driver_id' => $request->driver_id,
                'warehouse_id' => $request->destination_warehouse_id,
                'tracking_id' => $trackingId,
                'invoice_no' => $invoiceNo,
                'pickup_address' => $request->pickup_address ?? ($request->pickup_stops[0]['address'] ?? null),
                'destination_address' => $destinationAddress,
                'pickup_latitude' => $request->pickup_latitude,
                'pickup_longitude' => $request->pickup_longitude,
                'destination_latitude' => $request->destination_latitude ?? ($firstStop['latitude'] ?? null),
                'destination_longitude' => $request->destination_longitude ?? ($firstStop['longitude'] ?? null),
                'items_description' => $request->items_description ?? $request->description,
                'weight' => $request->weight ?? $request->estimated_boxes,
                'scheduled_date' => $request->scheduled_date,
                'scheduled_time' => $request->scheduled_time,
                'total_distance' => $totalDistance,
                'total_price' => $totalPrice,
                'driver_earning' => $totalPrice * 0.75,
                'admin_margin' => $totalPrice * 0.25,
                'tax_amount' => $taxAmount,
                'grand_total' => $totalPrice + $taxAmount,
                'status' => 'pending',
                'bill_type' => $request->bill_type ?? 'regular',
                'pan_number' => $request->pan_number ?? null,
                'payment_status' => 'pending',
                'payment_due_date' => now()->addDays(7),
                'notes' => $request->notes,
            ]);
            
            // Create pickup stops
            foreach ($request->pickup_stops as $index => $stop) {
                PickupStop::create([
                    'pickup_request_id' => $pickup->id,
                    'stop_number' => $index + 1,
                    'address' => $stop['address'],
                    'contact_name' => $stop['contact_name'],
                    'contact_phone' => $stop['contact_phone'],
                    'items_description' => $stop['items_description'] ?? null,
                    'estimated_weight' => $stop['estimated_weight'] ?? 0,
                    'latitude' => $stop['latitude'] ?? null,
                    'longitude' => $stop['longitude'] ?? null,
                    'status' => 'pending',
                ]);
            }
            
            // Generate invoice
            $this->invoiceService->generatePickupInvoice($pickup->load('stops'));
            
            // Send notifications
            $this->sendNotifications($pickup);
            
            DB::commit();
            
            $message = 'Pickup request created successfully! Tracking ID: ' . $trackingId;
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'pickup_id' => $pickup->id,
                    'tracking_id' => $trackingId,
                    'redirect_url' => route('pickup.show', $pickup->id),
                ]);
            }
            
            return redirect()
                ->route('pickup.show', $pickup->id)
                ->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pickup creation failed: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create pickup: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()
                ->back()
                ->with('error', 'Failed to create pickup: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display pickup request details
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $pickup = PickupRequest::with(['client', 'driver', 'warehouse', 'stops'])
            ->findOrFail($id);
        
        // Authorize view
        if ($user->role != 'admin' && 
            $pickup->client_id != $user->id && 
            $pickup->driver_id != $user->id) {
            abort(403, 'Unauthorized access.');
        }
        
        return view('pickup.show', compact('pickup'));
    }

    /**
     * Update pickup status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,assigned,picked_up,on_the_way,delivered,cancelled',
        ]);
        
        $pickup = PickupRequest::findOrFail($id);
        $user = Auth::user();
        
        // Authorize
        if ($user->role != 'admin' && $pickup->driver_id != $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }
        
        $oldStatus = $pickup->status;
        $pickup->status = $request->status;
    $pickup->save();

    // 🔥 Fire the event if status is delivered
    if ($pickup->status === 'delivered') {
    event(new \App\Events\OrderDelivered($pickup, 'pickup'));
    }

        
        // Update timestamps
        if ($request->status == 'assigned' && !$pickup->assigned_at) {
            $pickup->assigned_at = now();
        } elseif ($request->status == 'picked_up' && !$pickup->picked_up_at) {
            $pickup->picked_up_at = now();
        } elseif ($request->status == 'delivered' && !$pickup->delivered_at) {
            $pickup->delivered_at = now();
        }
        
        $pickup->save();
        
        // Create notification for status change
        $this->createStatusNotification($pickup, $oldStatus);
        
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'status' => $pickup->status,
            ]);
        }
        return back()->with('success', 'Pickup status updated to ' . ucfirst(str_replace('_', ' ', $pickup->status)));
    }

    /**
     * Calculate price via AJAX using AI (For Pickup)
     */
    public function calculatePriceAjax(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'pickup_stops' => 'required|array|min:1',
                'total_distance' => 'required|numeric|min:0',
                'vehicle_type' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()->first()
                ], 422);
            }

            $totalDistance = $request->total_distance;
            $vehicleType = $request->vehicle_type;

            $configService = app(\App\Services\AdminConfigService::class);
            $pricePerKm = $configService->getPricePerKm();
            $minCharge = $configService->getMinimumCharge();
            $adminMargin = $configService->getAdminMargin($totalDistance);

            $result = $this->aiService->calculateAndExplainPrice(
                $totalDistance,
                $vehicleType,
                $pricePerKm,
                $minCharge,
                $adminMargin
            );

            return response()->json([
                'success' => true,
                'total_distance' => round($totalDistance, 2),
                'final_price' => number_format($result['final_price'], 2),
                'base_price' => number_format($result['base_price'], 2),
                'margin_applied' => $adminMargin . '%',
                'explanation' => $result['explanation']
            ]);

        } catch (\Exception $e) {
            \Log::error('Pickup Price Calculation Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * AJAX: Get recommended drivers for a Pickup.
     */
    public function getDriverRecommendations(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pickup_stops' => 'required|array|min:1',
            'total_distance' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()->first()], 422);
        }

        $pickupAddress = $request->pickup_stops[0]['address'] ?? 'Client pickup';
        $totalDistance = $request->total_distance;
        $clientVehicleSelection = $request->vehicle_type ?? 'Standard';

        $drivers = \App\Models\User::where('role', 'driver')
            ->where('is_active', true)
            ->get();

        $driverData = $drivers->map(function($driver) use ($totalDistance) {
            return [
                'id' => $driver->id,
                'name' => $driver->name,
                'rating' => $driver->avg_rating ?? 4.0,
                'distance_km' => null,
                'base_price' => 400,
            ];
        })->toArray();

        $aiResponse = $this->aiService->recommendDrivers($driverData, $pickupAddress, $totalDistance, $clientVehicleSelection);

        return response()->json([
            'success' => true,
            'recommended_vehicle' => $aiResponse['recommended_vehicle'],
            'drivers' => $aiResponse['drivers']
        ]);
    }

    /**
     * Get assigned warehouses for client
     */
    private function getAssignedWarehouses($user)
    {
        $assignedWarehouses = Warehouse::whereHas('warehouseRequests', function($query) use ($user) {
            $query->where('client_id', $user->id)
                  ->where('status', 'approved');
        })->with('user')->get();
        
        if ($assignedWarehouses->isEmpty()) {
            $assignedWarehouses = Warehouse::where('status', 'approved')
                ->limit(10)
                ->with('user')
                ->get();
        }
        
        return $assignedWarehouses;
    }

    /**
     * Get available drivers with their rates
     */
    private function getAvailableDrivers()
    {
        $today = date('Y-m-d');
        
        $drivers = User::where('role', 'driver')
            ->where('is_active', true)
            ->get();
        
        $availableDrivers = [];
        
        foreach ($drivers as $driver) {
            $rate = DriverRate::where('user_id', $driver->id)
                ->where('date', $today)
                ->where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('valid_until')
                      ->orWhere('valid_until', '>', now());
                })
                ->first();
            
            $availableDrivers[] = [
                'id' => $driver->id,
                'name' => $driver->name,
                'email' => $driver->email,
                'phone' => $driver->phone ?? 'N/A',
                'has_rate' => !is_null($rate),
                'vehicle_type' => $rate->vehicle_type ?? 'Standard',
                'price' => $rate ? ($rate->base_price ?? 400) : 400,
                'rating' => $driver->avg_rating ?? 4.0,
            ];
        }
        
        return collect($availableDrivers);
    }

    /**
     * Generate unique tracking ID
     */
    private function generateTrackingId()
    {
        $prefix = 'PICK';
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        
        $trackingId = $prefix . $date . $random;
        
        while (PickupRequest::where('tracking_id', $trackingId)->exists()) {
            $random = strtoupper(substr(uniqid(), -6));
            $trackingId = $prefix . $date . $random;
        }
        
        return $trackingId;
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        $lastPickup = PickupRequest::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastPickup && $lastPickup->invoice_no) {
            $lastNumber = intval(substr($lastPickup->invoice_no, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "PIC-{$year}{$month}-{$newNumber}";
    }

    /**
     * Send notifications for new pickup
     */
    private function sendNotifications($pickup)
    {
        if ($pickup->driver_id) {
            Notification::create([
                'user_id' => $pickup->driver_id,
                'type' => 'new_pickup',
                'title' => 'New Pickup Assignment',
                'message' => 'You have been assigned a new pickup. Tracking ID: ' . $pickup->tracking_id,
                'related_id' => $pickup->id,
                'related_type' => 'pickup',
                'pickup_request_id' => $pickup->id,
            ]);
        }
        
        // Notify client
        Notification::create([
            'user_id' => $pickup->client_id,
            'type' => 'pickup_created',
            'title' => 'Pickup Request Created',
            'message' => 'Your pickup request has been created. Tracking ID: ' . $pickup->tracking_id,
            'related_id' => $pickup->id,
            'related_type' => 'pickup',
            'pickup_request_id' => $pickup->id,
        ]);
        
        // Send admin notification
        try {
            $this->adminEmailService->notifyNewPickup($pickup);
        } catch (\Throwable $e) {
            Log::warning('Admin pickup email notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Create notification for status change
     */
    private function createStatusNotification($pickup, $oldStatus)
    {
        $message = "Pickup status changed from " . ucfirst($oldStatus) . " to " . ucfirst($pickup->status);
        
        Notification::create([
            'user_id' => $pickup->client_id,
            'type' => 'status_update',
            'title' => 'Pickup Status Update',
            'message' => $message . '. Tracking ID: ' . $pickup->tracking_id,
            'related_id' => $pickup->id,
            'related_type' => 'pickup',
        ]);
    }

    /**
     * Get pickup statistics
     */
    private function getPickupStats($user)
    {
        $query = PickupRequest::query();
        
        if ($user->role == 'client') {
            $query->where('client_id', $user->id);
        } elseif ($user->role == 'driver') {
            $query->where('driver_id', $user->id);
        }
        
        return [
            'total' => $query->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'in_progress' => (clone $query)->whereIn('status', ['assigned', 'picked_up', 'on_the_way'])->count(),
            'completed' => (clone $query)->where('status', 'delivered')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
        ];
    }

    /**
     * Cancel pickup request
     */
    public function cancel($id)
    {
        $pickup = PickupRequest::where('client_id', Auth::id())
            ->whereIn('status', ['pending', 'assigned'])
            ->findOrFail($id);
        
        $pickup->status = 'cancelled';
        $pickup->save();
        
        return redirect()->route('pickup.show', $pickup->id)
            ->with('success', 'Pickup request cancelled successfully.');
    }

    /**
     * Delete pickup request (admin only)
     */
    public function destroy($id)
    {
        $user = Auth::user();
        
        if ($user->role != 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $pickup = PickupRequest::findOrFail($id);
        $pickup->delete();
        
        return redirect()->route('pickup.index')
            ->with('success', 'Pickup request deleted successfully.');
    }
}
