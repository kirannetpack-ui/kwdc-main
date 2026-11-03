<?php

namespace App\Http\Controllers;

use App\Models\DispatchOrder;
use App\Models\DeliveryStop;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\DriverRate;
use App\Models\Warehouse;
use App\Models\WarehouseRequest;
use App\Models\Stock;
use App\Events\OrderDelivered;
use App\Events\LocationUpdated;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DispatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();
        $query = DispatchOrder::with(['client', 'driver', 'vehicle']);

        if ($user->role === 'client') {
            $query->where('client_id', $user->id);
        } elseif ($user->role === 'driver') {
            $query->where('driver_id', $user->id);
        }

        $dispatches = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('dispatch.index', compact('dispatches'));
    }

    public function create($requestId = null)
    {
        $warehouseRequest = null;
        if ($requestId) {
            $warehouseRequest = WarehouseRequest::with('warehouse')
                ->when(!auth()->user()->isAdmin(), function ($query) {
                    $query->where('client_id', auth()->id());
                })
                ->findOrFail($requestId);
        }

        $clients = auth()->user()->isAdmin()
            ? User::where('role', 'client')->get()
            : User::where('id', auth()->id())->get();
        $drivers = User::where('role', 'driver')->get();
        $vehicles = Vehicle::all();

        return view('dispatch.create', compact('warehouseRequest', 'clients', 'drivers', 'vehicles'));
    } // <-- ✅ THIS BRACE IS NOW HERE

    public function directCreate()
    {
        $clients = auth()->user()->isAdmin()
            ? User::where('role', 'client')->get()
            : User::where('id', auth()->id())->get();
        $drivers = User::where('role', 'driver')->get();
        $vehicles = Vehicle::all();

        // Get available drivers with their rates for AI recommendations
        $availableDrivers = User::where('role', 'driver')
            ->with(['driverRates' => function($q) {
                $q->where('is_active', true);
            }])
            ->get()
            ->map(function($driver) {
                $rate = $driver->driverRates->first();
                return [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'phone' => $driver->phone ?? 'N/A',
                    'vehicle_type' => $driver->vehicle_type ?? 'Standard',
                    'price' => $rate ? $rate->price_per_km * 10 : 500,
                    'rating' => $driver->average_rating ?? 4,
                ];
            });

        $assignedWarehouses = Warehouse::where('user_id', Auth::id())->get();
        $stocks = Stock::where('user_id', Auth::id())->get();

        return view('dispatch.direct-create', compact(
            'clients', 'drivers', 'vehicles', 'availableDrivers',
            'assignedWarehouses', 'stocks'
        ));
    }


    public function store(Request $request)
    {
        if (!$request->has('delivery_stops') && $request->filled('delivery_address')) {
            $request->merge([
                'delivery_stops' => [[
                    'address' => $request->delivery_address,
                    'recipient_name' => $request->input('recipient_name', $request->user()->name),
                    'recipient_phone' => $request->input('recipient_phone', $request->user()->phone ?? 'N/A'),
                ]],
            ]);
        }

        $validator = Validator::make($request->all(), [
            'pickup_address' => 'required|string|max:500',
            'driver_id' => 'nullable|exists:users,id',
            'vehicle_type' => 'nullable|string',
            'delivery_stops' => 'required|array|min:1',
            'delivery_stops.*.address' => 'required|string|max:500',
            'delivery_stops.*.recipient_name' => 'required|string|max:100',
            'delivery_stops.*.recipient_phone' => 'required|string|max:20',
            'delivery_stops.*.latitude' => 'nullable|numeric|between:-90,90',
            'delivery_stops.*.longitude' => 'nullable|numeric|between:-180,180',
            'total_distance' => 'nullable|numeric|min:0',
            'base_price' => 'nullable|numeric|min:0',
            'total_price' => 'nullable|numeric|min:0',
            'client_id' => 'nullable|exists:users,id',
            'pickup_contact_person' => 'nullable|string|max:100',
            'pickup_contact_phone' => 'nullable|string|max:20',
            'pickup_latitude' => 'nullable|numeric|between:-90,90',
            'pickup_longitude' => 'nullable|numeric|between:-180,180',
            'bill_type' => 'nullable|string',
            'pan_number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $clientId = $this->dispatchClientId($request);
            $basePrice = (float) ($request->input('total_price', $request->input('base_price', 0)));
            $firstDeliveryStop = collect($request->input('delivery_stops', []))->first();

            $dispatch = DispatchOrder::create([
                'client_id' => $clientId,
                'driver_id' => $request->driver_id,
                'pickup_address' => $request->pickup_address,
                'delivery_address' => $request->delivery_address ?? ($firstDeliveryStop['address'] ?? null),
                'pickup_contact_person' => $request->pickup_contact_person,
                'pickup_contact_phone' => $request->pickup_contact_phone,
                'pickup_latitude' => $request->pickup_latitude,
                'pickup_longitude' => $request->pickup_longitude,
                'total_distance' => $request->total_distance ?? 0,
                'base_price' => $basePrice,
                'driver_earning' => $basePrice * 0.75,
                'admin_margin' => $basePrice * 0.25,
                'status' => 'pending',
                'tracking_id' => 'TRK-' . strtoupper(Str::random(8)),
                'bill_type' => $request->bill_type ?? 'regular',
                'pan_number' => $request->pan_number,
            ]);

            foreach ($request->delivery_stops as $index => $stopData) {
                DeliveryStop::create([
                    'dispatch_order_id' => $dispatch->id,
                    'stop_number' => $stopData['stop_number'] ?? $stopData['stop_order'] ?? ($index + 1),
                    'address' => $stopData['address'],
                    'recipient_name' => $stopData['recipient_name'],
                    'recipient_phone' => $stopData['recipient_phone'],
                    'latitude' => $stopData['latitude'] ?? null,
                    'longitude' => $stopData['longitude'] ?? null,
                    'notes' => $stopData['notes'] ?? null,
                    'status' => 'pending',
                ]);
            }

            DB::commit();

            $message = 'Dispatch created successfully! Tracking ID: ' . $dispatch->tracking_id;

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'dispatch_id' => $dispatch->id,
                    'redirect_url' => route('dispatch.show', $dispatch->id)
                ]);
            }

            return redirect()->route('dispatch.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Dispatch creation error: ' . $e->getMessage());

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create dispatch: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to create dispatch: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $dispatch = DispatchOrder::with(['client', 'driver', 'deliveryStops', 'vehicle'])
            ->findOrFail($id);

        abort_unless($this->canViewDispatch($dispatch), 403);

        return view('dispatch.show', compact('dispatch'));
    }

    public function edit($id)
    {
        $dispatch = DispatchOrder::findOrFail($id);

        abort_unless($this->canManageDispatch($dispatch), 403);

        $clients = User::where('role', 'client')->get();
        $drivers = User::where('role', 'driver')->get();
        $vehicles = Vehicle::all();
        return view('dispatch.edit', compact('dispatch', 'clients', 'drivers', 'vehicles'));
    }

    public function update(Request $request, $id)
    {
        $dispatch = DispatchOrder::findOrFail($id);

        abort_unless($this->canManageDispatch($dispatch), 403);

        $validator = Validator::make($request->all(), [
            'pickup_address' => 'required|string|max:500',
            'driver_id' => 'required|exists:users,id',
            'total_distance' => 'nullable|numeric|min:0',
            'base_price' => 'nullable|numeric|min:0',
            'status' => 'in:pending,in_progress,delivered,cancelled',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $dispatch->update($request->only([
            'pickup_address', 'driver_id', 'total_distance', 'base_price', 'status'
        ]));

        return redirect()->route('dispatch.index')->with('success', 'Dispatch updated!');
    }

    public function destroy($id)
    {
        $dispatch = DispatchOrder::findOrFail($id);

        abort_unless($this->canManageDispatch($dispatch), 403);

        $dispatch->delete();
        return redirect()->route('dispatch.index')->with('success', 'Dispatch deleted!');
    }

    public function calculatePriceAjax(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pickup_stops' => 'required|array|min:1',
            'delivery_stops' => 'required|array|min:1',
            'total_distance' => 'required|numeric|min:0',
            'vehicle_type' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $totalDistance = (float) $request->total_distance;
        $basePrice = $totalDistance * 20;
        $marginPercentage = 10;
        $marginAmount = $basePrice * ($marginPercentage / 100);
        $finalPrice = $basePrice + $marginAmount;

        $explanation = "Road distance: {$totalDistance} km. Base rate: NPR 20/km, margin: {$marginPercentage}%.";

        return response()->json([
            'success' => true,
            'total_distance' => $totalDistance,
            'base_price' => round($basePrice, 2),
            'margin_amount' => round($marginAmount, 2),
            'margin_applied' => $marginPercentage . '%',
            'final_price' => round($finalPrice, 2),
            'explanation' => $explanation,
        ]);
    }

    public function updateLocation(Request $request, $id)
    {
        $dispatch = DispatchOrder::findOrFail($id);

        if (auth()->user()->role !== 'admin' && auth()->id() !== $dispatch->driver_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $dispatch->current_latitude = $request->latitude;
        $dispatch->current_longitude = $request->longitude;
        $dispatch->last_location_update = now();
        $dispatch->save();

        $eta = null;
        if ($dispatch->delivery_lat && $dispatch->delivery_lng) {
            $distance = $this->haversineDistance(
                $request->latitude,
                $request->longitude,
                $dispatch->delivery_lat,
                $dispatch->delivery_lng
            );
            $avgSpeed = 30;
            $timeInHours = $distance / $avgSpeed;
            $eta = now()->addHours($timeInHours);
            $dispatch->estimated_arrival = $eta;
            $dispatch->save();
        }

        broadcast(new LocationUpdated($dispatch->id, $request->latitude, $request->longitude, $eta));

        return response()->json([
            'success' => true,
            'eta' => $eta ? $eta->toIso8601String() : null,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $dispatch = DispatchOrder::findOrFail($id);

        abort_unless($this->canManageDispatch($dispatch), 403);

        $oldStatus = $dispatch->status;
        $status = $request->status;

        if (!in_array($status, ['pending', 'in_progress', 'delivered', 'cancelled'])) {
            return response()->json(['error' => 'Invalid status'], 400);
        }

        $dispatch->status = $status;

        if ($status === 'delivered') {
            $dispatch->delivered_at = now();
            if (!$dispatch->driver_earning) {
                $dispatch->driver_earning = (float) $dispatch->base_price * 0.75;
            }
        }

        $dispatch->save();

        NotificationService::notifyStatusUpdate($dispatch, 'dispatch', $oldStatus, $status);

        if ($oldStatus !== 'delivered' && $status === 'delivered') {
            event(new OrderDelivered($dispatch, 'dispatch'));
        }

        return response()->json(['success' => true]);
    }

    public function getDriverRecommendations(Request $request)
    {
        $drivers = User::where('role', 'driver')
            ->limit(5)
            ->get()
            ->map(function($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->name,
                    'rating' => rand(3, 5),
                    'price' => rand(400, 800),
                ];
            });

        return response()->json([
            'success' => true,
            'drivers' => $drivers,
            'recommended_vehicle' => 'Standard',
        ]);
    }

    private function haversineDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng / 2) * sin($dLng / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    public function enableTracking(Request $request, $id)
    {
        $dispatch = DispatchOrder::findOrFail($id);

        abort_unless($this->canViewDispatch($dispatch), 403);

        $dispatch->tracking_enabled = true;
        $dispatch->tracking_token = $dispatch->tracking_token ?: Str::random(40);
        $dispatch->save();

        return response()->json([
            'success' => true,
            'tracking_token' => $dispatch->tracking_token,
            'tracking_url' => $this->trackingUrl($dispatch),
        ]);
    }

    public function track(Request $request, $id)
    {
        $dispatch = DispatchOrder::with(['driver', 'deliveryStops'])
            ->where('id', $id)
            ->where('tracking_enabled', true)
            ->firstOrFail();

        abort_unless($this->canTrackDispatch($dispatch, $request), 403);

        return view('dispatch.track', compact('dispatch'));
    }

    public function rate(Request $request, $id)
    {
        $dispatch = DispatchOrder::findOrFail($id);

        abort_unless($this->canRateDispatch($dispatch), 403);

        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'feedback' => 'nullable|string|max:500',
        ]);

        $dispatch->client_rating = $request->rating;
        $dispatch->client_feedback = $request->feedback;
        $dispatch->save();

        return response()->json(['success' => true, 'message' => 'Thank you for your feedback!']);
    }

    public function updateStopStatus(Request $request, $stopId)
    {
        $stop = DeliveryStop::with('dispatchOrder')->findOrFail($stopId);

        abort_unless($this->canManageDispatch($stop->dispatchOrder), 403);

        $stop->status = $request->status;
        $stop->save();

        return response()->json(['success' => true]);
    }

    private function canViewDispatch(DispatchOrder $dispatch): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->isAdmin()
            || $dispatch->client_id === $user->id
            || $dispatch->driver_id === $user->id;
    }

    private function canManageDispatch(DispatchOrder $dispatch): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->isAdmin()
            || $dispatch->driver_id === $user->id;
    }

    private function canRateDispatch(DispatchOrder $dispatch): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->isAdmin()
            || $dispatch->client_id === $user->id;
    }

    private function dispatchClientId(Request $request): int
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            return $user->id;
        }

        return (int) ($request->input('client_id') ?: $user->id);
    }

    private function canTrackDispatch(DispatchOrder $dispatch, Request $request): bool
    {
        if ($this->canViewDispatch($dispatch)) {
            return true;
        }

        $token = (string) $request->query('token', '');

        return filled($dispatch->tracking_token)
            && filled($token)
            && hash_equals($dispatch->tracking_token, $token);
    }

    private function trackingUrl(DispatchOrder $dispatch): string
    {
        return route('dispatch.track', [
            'id' => $dispatch->id,
            'token' => $dispatch->tracking_token,
        ]);
    }
}
