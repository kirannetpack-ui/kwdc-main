<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Vehicle;
use App\Models\Equipment;
use App\Models\EquipmentJob;
use App\Models\MarginTier;
use App\Models\WarehouseDocument;
use App\Models\Insurance;
use App\Models\WarehouseRequest;
use App\Models\DispatchOrder;
use App\Models\PickupRequest;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;
use App\Services\AIService;

class AdminController extends Controller
{
    // ==================== WAREHOUSE MANAGEMENT ====================

    /**
     * Show the form for creating a new warehouse.
     */
    public function createWarehouse()
    {
        $propertyOwners = User::where('role', 'property_owner')
            ->orderBy('name')
            ->get();

        return view('admin.warehouses.create', compact('propertyOwners'));
    }

    /**
     * Store a newly created warehouse in storage.
     */
    public function storeWarehouse(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Basic Information
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:500',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'owner_id' => 'required|exists:users,id',
            'owner_name' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',

            // Warehouse Details
            'area_sqft' => 'nullable|numeric|min:0',
            'area_sqm' => 'nullable|numeric|min:0',
            'price_per_sqft' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',

            // Security Features
            'cctv_count' => 'nullable|integer|min:0',
            'guards_count' => 'nullable|integer|min:0',
            'fire_extinguishers' => 'nullable|integer|min:0',
            'cctv_stream_urls' => 'nullable|array',
            'cctv_stream_urls.*' => 'nullable|url',

            // Nearby Facilities
            'nearby_police' => 'nullable|string|max:500',
            'nearby_fire' => 'nullable|string|max:500',
            'nearby_hospital' => 'nullable|string|max:500',
            'nearby_bank' => 'nullable|string|max:500',
            'nearby_fuel' => 'nullable|string|max:500',
            'nearby_market' => 'nullable|string|max:500',

            // Cold Storage
            'cold_storage' => 'nullable|boolean',
            'temperature_min' => 'nullable|numeric',
            'temperature_max' => 'nullable|numeric',
            'humidity_control' => 'nullable|boolean',

            // Files
            'front_image' => 'nullable|image|max:5120',
            'interior_image' => 'nullable|image|max:5120',
            'exterior_image' => 'nullable|image|max:5120',
            'ownership_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'tax_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'fire_safety_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'building_approval_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',

            // Facilities
            'facilities' => 'nullable|array',
            'facilities.*' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Handle file uploads
            $frontImage = $request->hasFile('front_image')
                ? $request->file('front_image')->store('warehouses/front', 'public')
                : null;

            $interiorImage = $request->hasFile('interior_image')
                ? $request->file('interior_image')->store('warehouses/interior', 'public')
                : null;

            $exteriorImage = $request->hasFile('exterior_image')
                ? $request->file('exterior_image')->store('warehouses/exterior', 'public')
                : null;

            $ownershipDoc = $request->hasFile('ownership_document')
                ? $request->file('ownership_document')->store('warehouses/documents', 'private_uploads')
                : null;

            $taxDoc = $request->hasFile('tax_document')
                ? $request->file('tax_document')->store('warehouses/documents', 'private_uploads')
                : null;

            $fireSafetyDoc = $request->hasFile('fire_safety_document')
                ? $request->file('fire_safety_document')->store('warehouses/documents', 'private_uploads')
                : null;

            $buildingApprovalDoc = $request->hasFile('building_approval_document')
                ? $request->file('building_approval_document')->store('warehouses/documents', 'private_uploads')
                : null;

            // Create warehouse
            $warehouse = Warehouse::create([
                'user_id' => $request->owner_id,
                'name' => $request->name,
                'location' => $request->location,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'owner_name' => $request->owner_name,
                'contact_number' => $request->contact_number,
                'area_sqft' => $request->area_sqft,
                'area_sqm' => $request->area_sqm,
                'price_per_sqft' => $request->price_per_sqft,
                'description' => $request->description,
                'cctv_count' => $request->cctv_count ?? 0,
                'guards_count' => $request->guards_count ?? 0,
                'fire_extinguishers' => $request->fire_extinguishers ?? 0,
                'cctv_stream_urls' => $request->cctv_stream_urls ? array_filter($request->cctv_stream_urls) : [],
                'nearby_police' => $request->nearby_police,
                'nearby_fire' => $request->nearby_fire,
                'nearby_hospital' => $request->nearby_hospital,
                'nearby_bank' => $request->nearby_bank,
                'nearby_fuel' => $request->nearby_fuel,
                'nearby_market' => $request->nearby_market,
                'cold_storage' => $request->has('cold_storage'),
                'temperature_min' => $request->temperature_min,
                'temperature_max' => $request->temperature_max,
                'humidity_control' => $request->has('humidity_control'),
                'front_image' => $frontImage,
                'interior_image' => $interiorImage,
                'exterior_image' => $exteriorImage,
                'ownership_document' => $ownershipDoc,
                'tax_document' => $taxDoc,
                'fire_safety_document' => $fireSafetyDoc,
                'building_approval_document' => $buildingApprovalDoc,
                'facilities' => $request->facilities ? array_filter($request->facilities) : [],
                'status' => 'pending',
            ]);

            return redirect()->route('admin.all-warehouses')
                ->with('success', 'Warehouse created successfully! Awaiting approval.');

        } catch (\Exception $e) {
            Log::error('Warehouse creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to create warehouse: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified warehouse.
     */
    public function showWarehouse($id)
    {
        $warehouse = Warehouse::with(['owner', 'user'])->findOrFail($id);
        return view('admin.warehouses.show', compact('warehouse'));
    }

    /**
     * Show the form for editing the specified warehouse.
     */
    public function editWarehouse($id)
    {
        $warehouse = Warehouse::with('owner')->findOrFail($id);
        // Get all property owners for dropdown
        $propertyOwners = User::where('role', 'property_owner')
            ->orderBy('name')
            ->get();

        return view('admin.warehouses.edit', compact('warehouse', 'propertyOwners'));
    }


    /**
     * Update the specified warehouse in storage.
     */
    public function updateWarehouse(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $validator = Validator::make($request->all(), [
            // Basic Information
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:500',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'owner_id' => 'required|exists:users,id',
            'owner_name' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:20',

            // Warehouse Details
            'area_sqft' => 'nullable|numeric|min:0',
            'area_sqm' => 'nullable|numeric|min:0',
            'price_per_sqft' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',

            // Security Features
            'cctv_count' => 'nullable|integer|min:0',
            'guards_count' => 'nullable|integer|min:0',
            'fire_extinguishers' => 'nullable|integer|min:0',
            'cctv_stream_urls' => 'nullable|array',
            'cctv_stream_urls.*' => 'nullable|url',

            // Nearby Facilities
            'nearby_police' => 'nullable|string|max:500',
            'nearby_fire' => 'nullable|string|max:500',
            'nearby_hospital' => 'nullable|string|max:500',
            'nearby_bank' => 'nullable|string|max:500',
            'nearby_fuel' => 'nullable|string|max:500',
            'nearby_market' => 'nullable|string|max:500',

            // Cold Storage
            'cold_storage' => 'nullable|boolean',
            'temperature_min' => 'nullable|numeric',
            'temperature_max' => 'nullable|numeric',
            'humidity_control' => 'nullable|boolean',

            // Files (Images & Docs)
            'front_image' => 'nullable|image|max:5120',
            'interior_image' => 'nullable|image|max:5120',
            'exterior_image' => 'nullable|image|max:5120',
            'ownership_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'tax_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'fire_safety_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'building_approval_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',

            // Facilities
            'facilities' => 'nullable|array',
            'facilities.*' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Helper to handle file updates (Delete old if new uploaded, else keep old)
            $handleFile = function($field, $path) use ($request, $warehouse) {
                if ($request->hasFile($field)) {
                    // Delete old file if it exists
                    $disk = in_array($field, ['ownership_document', 'tax_document', 'fire_safety_document', 'building_approval_document'], true)
                        ? 'private_uploads'
                        : 'public';

                    if ($warehouse->$field && \Storage::disk($disk)->exists($warehouse->$field)) {
                        \Storage::disk($disk)->delete($warehouse->$field);
                    } elseif ($warehouse->$field && $disk !== 'public' && \Storage::disk('public')->exists($warehouse->$field)) {
                        \Storage::disk('public')->delete($warehouse->$field);
                    }
                    return $request->file($field)->store($path, $disk);
                }
                return $warehouse->$field; // Keep the old file if no new one provided
            };

            $frontImage = $handleFile('front_image', 'warehouses/front');
            $interiorImage = $handleFile('interior_image', 'warehouses/interior');
            $exteriorImage = $handleFile('exterior_image', 'warehouses/exterior');
            $ownershipDoc = $handleFile('ownership_document', 'warehouses/documents');
            $taxDoc = $handleFile('tax_document', 'warehouses/documents');
            $fireSafetyDoc = $handleFile('fire_safety_document', 'warehouses/documents');
            $buildingApprovalDoc = $handleFile('building_approval_document', 'warehouses/documents');

            $warehouse->update([
                'user_id' => $request->owner_id,
                'name' => $request->name,
                'location' => $request->location,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'owner_name' => $request->owner_name,
                'contact_number' => $request->contact_number,
                'area_sqft' => $request->area_sqft,
                'area_sqm' => $request->area_sqm,
                'price_per_sqft' => $request->price_per_sqft,
                'description' => $request->description,
                'cctv_count' => $request->cctv_count ?? 0,
                'guards_count' => $request->guards_count ?? 0,
                'fire_extinguishers' => $request->fire_extinguishers ?? 0,
                'cctv_stream_urls' => $request->cctv_stream_urls ? array_filter($request->cctv_stream_urls) : [],
                'nearby_police' => $request->nearby_police,
                'nearby_fire' => $request->nearby_fire,
                'nearby_hospital' => $request->nearby_hospital,
                'nearby_bank' => $request->nearby_bank,
                'nearby_fuel' => $request->nearby_fuel,
                'nearby_market' => $request->nearby_market,
                'cold_storage' => $request->has('cold_storage'),
                'temperature_min' => $request->temperature_min,
                'temperature_max' => $request->temperature_max,
                'humidity_control' => $request->has('humidity_control'),
                'front_image' => $frontImage,
                'interior_image' => $interiorImage,
                'exterior_image' => $exteriorImage,
                'ownership_document' => $ownershipDoc,
                'tax_document' => $taxDoc,
                'fire_safety_document' => $fireSafetyDoc,
                'building_approval_document' => $buildingApprovalDoc,
                'facilities' => $request->facilities ? array_filter($request->facilities) : [],
                'status' => $request->status ?? $warehouse->status, // Allow admin to update status
            ]);

            return redirect()->route('admin.warehouses.show', $warehouse->id)
                ->with('success', 'Warehouse updated successfully!');

        } catch (\Exception $e) {
            Log::error('Warehouse update failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to update warehouse: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get warehouse details for AJAX.
     */
    public function getWarehouseDetails($id)
    {
        $warehouse = Warehouse::with('owner')->findOrFail($id);
        return response()->json($warehouse);
    }

    /**
     * Display a listing of pending warehouses.
     */
    public function pending()
    {
        $pendingWarehouses = Warehouse::where('status', 'pending')
            ->with('owner')
            ->latest()
            ->paginate(10);

        return view('admin.pending', compact('pendingWarehouses'));
    }

    /**
     * Approve a warehouse.
     */
    public function approve($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $updated = Warehouse::whereKey($warehouse->id)->where('status', 'pending')->update(['status' => 'approved']);
        abort_unless($updated, 409, 'This warehouse has already been reviewed.');

        try {
            NotificationService::notifyWarehouseApproved($warehouse);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Warehouse approval notification failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Warehouse approved successfully');
    }

    /**
     * Reject a warehouse.
     */
    public function reject($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $updated = Warehouse::whereKey($warehouse->id)->where('status', 'pending')->update(['status' => 'rejected']);
        abort_unless($updated, 409, 'This warehouse has already been reviewed.');

        return back()->with('success', 'Warehouse rejected');
    }

    /**
     * Display a listing of all warehouses.
     */
    public function allWarehouses()
    {
        $warehouses = Warehouse::with('owner')->latest()->get();
        return view('admin.warehouses.index', compact('warehouses'));
    }

    /**
     * Display a listing of warehouse tenants.
     */
    public function warehouseTenants()
    {
        $warehouses = Warehouse::with('tenants')->get();
        return view('admin.warehouse-tenants', compact('warehouses'));
    }

    /**
     * Display a listing of warehouse documents.
     */
    public function warehouseDocuments()
    {
        $documents = WarehouseDocument::with('warehouse', 'user')->latest()->get();
        return view('admin.warehouse-documents', compact('documents'));
    }


    // ==================== REQUEST MANAGEMENT ====================

    /**
     * Display a listing of warehouse requests.
     */
    public function requests()
    {
        $requests = WarehouseRequest::with('client', 'warehouse')->latest()->get();
        return view('admin.requests.index', compact('requests'));
    }

    // ==================== STOCK MANAGEMENT ====================

    /**
     * Display a listing of stock items.
     */
    public function manageStock()
    {
        $stocks = Stock::with('warehouse', 'request')->latest()->get();
        return view('admin.stocks.index', compact('stocks'));
    }

    /**
     * Display a listing of pending stock items.
     */
    public function pendingStocks()
    {
        $pendingStocks = Stock::where('status', 'pending')->with('warehouse')->get();
        return view('admin.stocks.pending', compact('pendingStocks'));
    }

    // ==================== VEHICLE & DISPATCH ====================

    /**
     * Display a listing of vehicles.
     */
    public function vehicles()
    {
        $vehicles = Vehicle::with('owner')->latest()->get();
        return view('admin.vehicles.index', compact('vehicles'));
    }

    /**
     * Display a listing of dispatch orders.
     */
    public function dispatchOrders()
    {
        $orders = DispatchOrder::with('driver', 'vehicle', 'warehouseRequest')->latest()->get();
        return view('admin.dispatch.index', compact('orders'));
    }

    /**
     * Display a listing of pickup requests.
     */
    public function pickupRequests()
    {
        $pickups = PickupRequest::with('client', 'driver', 'vehicle')->latest()->get();
        return view('admin.pickup.index', compact('pickups'));
    }

    // ==================== EQUIPMENT MANAGEMENT ====================

    /**
     * Display a listing of equipment jobs.
     */
   // 1. List all equipment with job counts
    public function equipmentList()
    {
        $equipment = Equipment::with(['owner', 'equipmentJobs'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.equipment-list', compact('equipment'));
    }

    // 2. Edit Equipment details (Admin can update ratings, status, etc)
    public function editEquipment($id)
    {
        $equipment = Equipment::findOrFail($id);
        return view('admin.equipment.edit', compact('equipment'));
    }

    // 3. Update Equipment
    public function updateEquipment(Request $request, $id)
    {
        $equipment = Equipment::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'year' => 'nullable|integer|min:1900|max:' . (now()->year + 1),
            'description' => 'nullable|string',
            'weight' => 'nullable|numeric|min:0',
            'engine_power' => 'nullable|numeric|min:0',
            'bucket_capacity' => 'nullable|numeric|min:0',
            'max_reach' => 'nullable|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'weekly_rate' => 'nullable|numeric|min:0',
            'monthly_rate' => 'nullable|numeric|min:0',
            'security_deposit' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:available,rented,in_use,maintenance,pending,approved,rejected',
        ]);

        $equipment->update($validated);
        return redirect()->route('admin.equipment-list')->with('success', 'Equipment updated successfully');
    }

    // 4. Delete Equipment
    public function destroyEquipment($id)
    {
        $equipment = Equipment::findOrFail($id);
        $equipment->delete();
        return redirect()->route('admin.equipment-list')->with('success', 'Equipment deleted successfully');
    }

    // 5. 🟢 TRACK RECORDS: Show all jobs for a specific equipment
    public function showEquipmentJobs($id)
    {
        $equipment = Equipment::with(['equipmentJobs.client', 'owner'])->findOrFail($id);
        $jobs = $equipment->equipmentJobs()->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.equipment.jobs', compact('equipment', 'jobs'));
    }

    // ==================== USER MANAGEMENT ====================

    /**
     * Display a listing of clients.
     */
       public function clients()
    {
        $clients = User::where('role', 'client')->get();
        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Display a listing of drivers.
     */
    public function drivers()
    {
        $drivers = User::where('role', 'driver')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.drivers.index', compact('drivers'));
    }

    // Add these new methods below it:
    public function showDriver($id)
    {
        $driver = User::where('role', 'driver')
            ->with(['vehicles', 'dispatchOrders' => fn($q) => $q->latest()->limit(10), 'driverRates'])
            ->findOrFail($id);
        $totalTrips = \App\Models\DispatchOrder::where('driver_id', $driver->id)->where('status', 'delivered')->count()
            + \App\Models\PickupRequest::where('driver_id', $driver->id)->whereIn('status', ['delivered', 'completed'])->count();
        $totalEarnings = (float) \App\Models\DispatchOrder::where('driver_id', $driver->id)->where('status', 'delivered')->sum('base_price')
            + (float) \App\Models\PickupRequest::where('driver_id', $driver->id)->whereIn('status', ['delivered', 'completed'])->sum('total_price');

        return view('admin.drivers.show', compact('driver', 'totalTrips', 'totalEarnings'));
    }

    public function editDriver($id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        return view('admin.drivers.edit', compact('driver'));
    }

    public function updateDriver(Request $request, $id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $driver->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
            'avg_rating' => 'nullable|numeric|min:0|max:5',
        ]);

        $driver->update($validated);
        return redirect()->route('admin.drivers')->with('success', 'Driver updated successfully');
    }

    public function destroyDriver($id)
    {
        $driver = User::where('role', 'driver')->findOrFail($id);
        $driver->delete();
        return redirect()->route('admin.drivers')->with('success', 'Driver deleted successfully');
    }

    /**
     * Display a listing of equipment owners.
     */
    public function equipmentOwners()
    {
        $equipmentOwners = User::where('is_equipment_owner', true)->orWhere('role', 'equipment_owner')->get();
        return view('admin.equipment-owners', compact('equipmentOwners'));
    }

    /**
     * Display a listing of property owners.
     */
    public function propertyOwners()
    {
        $propertyOwners = User::where('role', 'property_owner')
            ->orderBy('name', 'asc')
            ->get();
        return view('admin.property-owners', compact('propertyOwners'));
    }


    // ==================== FINANCIAL ====================

    /**
     * Display a listing of invoices.
     */
    public function invoices()
    {
        $invoices = \App\Models\Invoice::with('client', 'warehouse')->latest()->get();
        return view('admin.invoices.index', compact('invoices'));
    }

    /**
     * Display a listing of partner earnings.
     */
      public function partnerEarnings()
    {
        // Fetch all earnings with the partner (user) relationship
        $earnings = \App\Models\PartnerEarning::with('partner')->latest()->get();

        // Calculate Total Earnings (Assuming status 'paid' is actual earnings, or sum all)
        $totalEarnings = $earnings->where('status', 'paid')->sum('amount') ?? 0;
        $pendingEarnings = $earnings->where('status', 'pending')->sum('amount') ?? 0;

        // Separate orders by type for display (Filter based on your 'order_type' column)
        $dispatchOrders = $earnings->where('order_type', 'dispatch');
        $pickupOrders = $earnings->where('order_type', 'pickup');
        $equipmentOrders = $earnings->where('order_type', 'equipment');

        return view('admin.partner-earnings', compact(
            'earnings',
            'totalEarnings',
            'pendingEarnings',
            'dispatchOrders',
            'pickupOrders',
            'equipmentOrders'
        ));
    }


    // ==================== MARGIN TIERS ====================

    /**
     * Display a listing of margin tiers.
     */
    public function marginTiers()
    {
        $marginTiers = MarginTier::orderBy('min_distance')->get();
        return view('admin.margin-tiers', compact('marginTiers'));
    }

    /**
     * Store a newly created margin tier.
     */
    public function storeMarginTier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'service_type' => 'required|in:dispatch,pickup,warehouse,equipment',
            'margin_type' => 'required|in:percentage,flat',
            'margin_value' => 'required|numeric|min:0',
            'min_distance' => 'nullable|numeric|min:0',
            'max_distance' => 'nullable|numeric|gt:min_distance',
        ]);

        MarginTier::create([
            'name' => $request->name,
            'service_type' => $request->service_type,
            'margin_type' => $request->margin_type,
            'margin_value' => $request->margin_value,
            'min_distance' => $request->min_distance ?? 0,
            'max_distance' => $request->max_distance,
            'is_active' => true,
        ]);

        return redirect()->route('admin.margin-tiers')
            ->with('success', 'Margin tier created successfully');
    }


    /**
     * Update the specified margin tier.
     */
      public function updateMarginTier(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'service_type' => 'required|in:dispatch,pickup,warehouse,equipment',
            'margin_type' => 'required|in:percentage,flat',
            'margin_value' => 'required|numeric|min:0',
            'min_distance' => 'nullable|numeric|min:0',
            'max_distance' => 'nullable|numeric|gt:min_distance',
            'is_active' => 'nullable|boolean',
        ]);

        $tier = MarginTier::findOrFail($id);
        $tier->update($validated);

        return redirect()->route('admin.margin-tiers')
            ->with('success', 'Margin tier updated successfully');
    }

    /**
     * Remove the specified margin tier.
     */
    public function destroyMarginTier($id)
    {
        $tier = MarginTier::findOrFail($id);
        $tier->delete();

        return redirect()->route('admin.margin-tiers')
            ->with('success', 'Margin tier deleted successfully');
    }

    // ==================== INSURANCE ====================

    /**
     * Display a listing of insurances.
     */
    public function insuranceList()
    {
        $insurances = Insurance::with('client')->latest()->get();
        return view('admin.insurance-list', compact('insurances'));
    }

    // ==================== REPORTS ====================

    /**
     * Display the reports page.
     */
    public function reports()
    {
        $monthExpression = match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', created_at)",
            'pgsql' => "to_char(created_at, 'YYYY-MM')",
            default => "DATE_FORMAT(created_at, '%Y-%m')",
        };
        $monthlyStats = DB::table('dispatch_orders')
            ->select(DB::raw($monthExpression.' as month'), DB::raw('count(*) as count'))
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        return view('admin.reports', compact('monthlyStats'));
    }

    // ==================== PREDICTIVE ANALYTICS ====================

    /**
     * Show Predictive Warehouse Analytics with Visualization.
     */
    public function predictiveAnalytics(AIService $aiService)
    {
        // Load all warehouses with their approved requests to calculate occupancy
        $warehouses = Warehouse::with(['warehouseRequests' => function($q) {
                $q->where('status', 'approved');
            }])
            ->get();

        $stats = [];
        $chartLabels = [];
        $chartCapacity = [];
        $chartOccupied = [];
        $totalCapacity = 0;
        $totalOccupied = 0;

        foreach ($warehouses as $w) {
            $areaSqft = $w->area_sqft ?? 0;
            $occupied = $w->warehouseRequests->sum('space_required') ?? 0;

            $occupied = min($occupied, $areaSqft);

            $totalCapacity += $areaSqft;
            $totalOccupied += $occupied;

            $stats[] = [
                'name' => $w->name,
                'current_area_sqft' => $areaSqft,
                'occupied_sqft' => round($occupied, 2),
                'incoming_requests' => $w->warehouseRequests->count(),
                'avg_growth_percent' => max(1, round(($w->warehouseRequests->count() / 10) * 5, 1))
            ];

            $chartLabels[] = $w->name;
            $chartCapacity[] = $areaSqft;
            $chartOccupied[] = round($occupied, 2);
        }

        $rawPredictions = empty($stats) ? [] : $aiService->predictWarehouseUtilization($stats, 30);

        // Normalize the AI's keys so the View never crashes
        $predictions = [];
        foreach ($rawPredictions as $pred) {
            $predictions[] = [
                'warehouse_name' => $pred['warehouse_name'] ?? $pred['name'] ?? 'Unknown Warehouse',
                'predicted_days_until_full' => $pred['predicted_days_until_full'] ?? $pred['days'] ?? $pred['predicted_days'] ?? 365,
                'suggestion' => $pred['suggestion'] ?? $pred['recommendation'] ?? 'No AI suggestion available.',
            ];
        }

        // Pass visualization data to the view
        $totalAvailable = max(0, $totalCapacity - $totalOccupied);
        $utilizationPercent = $totalCapacity > 0 ? round(($totalOccupied / $totalCapacity) * 100, 1) : 0;

        return view('admin.analytics.predictive', compact(
            'predictions',
            'stats',
            'chartLabels',
            'chartCapacity',
            'chartOccupied',
            'totalCapacity',
            'totalOccupied',
            'totalAvailable',
            'utilizationPercent'
        ));
    }

    // ==================== CLIENT SHOW ====================

    /**
     * Display the specified client with their requests and stats.
     */
    public function showClient($id)
    {
        $client = User::with(['warehouseRequests' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        // Add assigned warehouses to each request
        foreach ($client->warehouseRequests as $request) {
            $request->assignedWarehouses = Warehouse::whereHas('warehouseRequests', function($q) use ($request) {
                $q->where('id', $request->id);
            })->get();
        }

        $stats = [
            'total_requests' => $client->warehouseRequests->count(),
            'approved_requests' => $client->warehouseRequests->where('status', 'approved')->count(),
            'pending_requests' => $client->warehouseRequests->where('status', 'pending')->count(),
            'total_dispatches' => DispatchOrder::where('client_id', $client->id)->count(),
            'total_spent' => DispatchOrder::where('client_id', $client->id)
                ->where('status', 'delivered')
                ->sum('base_price') ?? 0,
        ];

        return view('admin.clients.show', compact('client', 'stats'));
    }

    /**
     * Display the Analytics Dashboard with Charts.
     */
    public function analytics()
    {
        // 1. Monthly Revenue (Last 12 Months)
        $months = collect();
        $revenues = collect();

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $revenue = \App\Models\DispatchOrder::where('status', 'delivered')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('base_price');

            $months->push($date->format('M Y'));
            $revenues->push(round($revenue, 2));
        }

        // 2. Monthly Dispatch Volume (Count)
        $dispatchCounts = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $count = \App\Models\DispatchOrder::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $dispatchCounts->push($count);
        }

        // 3. Dispatch Status Distribution
        $statuses = ['pending', 'assigned', 'picked_up', 'on_the_way', 'delivered', 'cancelled'];
        $statusCounts = [];
        $statusColors = [
            'pending' => '#f59e0b',
            'assigned' => '#3b82f6',
            'picked_up' => '#8b5cf6',
            'on_the_way' => '#ec4899',
            'delivered' => '#22c55e',
            'cancelled' => '#ef4444',
        ];

        foreach ($statuses as $status) {
            $count = \App\Models\DispatchOrder::where('status', $status)->count();
            $statusCounts[$status] = $count;
        }

        // 4. Top 5 Drivers by Earnings
        $topDrivers = \App\Models\User::where('role', 'driver')
            ->withSum(['dispatchOrders' => function($q) {
                $q->where('status', 'delivered');
            }], 'driver_earning')
            ->orderBy('dispatch_orders_sum_driver_earning', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'dispatch_orders_sum_driver_earning']);

        return view('admin.analytics.index', compact(
            'months', 'revenues', 'dispatchCounts', 'statusCounts', 'statusColors', 'topDrivers'
        ));
    }

    /**
     * Show vehicle fleet compliance, Blue Book renewal, and route permit statuses.
     */
    public function vehicleCompliance()
    {
        $vehicles = Vehicle::with('driver')->orderBy('created_at', 'desc')->paginate(20);

        $now = now()->startOfDay();
        $totalVehicles = Vehicle::count();
        $compliantCount = 0;
        $expiringCount = 0;
        $expiredCount = 0;

        foreach ($vehicles as $v) {
            $issues = $v->getComplianceIssues();
            $hasExpired = collect($issues)->contains('status', 'expired');
            $hasExpiring = collect($issues)->contains('status', 'expiring');

            if ($hasExpired) {
                $expiredCount++;
            } elseif ($hasExpiring) {
                $expiringCount++;
            } else {
                $compliantCount++;
            }
        }

        return view('admin.vehicles.compliance', compact(
            'vehicles', 'totalVehicles', 'compliantCount', 'expiringCount', 'expiredCount'
        ));
    }
}
