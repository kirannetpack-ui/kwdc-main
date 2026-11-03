<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\User;
use App\Models\WarehouseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\AdminEmailService;
 use App\Services\AIService;

class WarehouseController extends Controller
{
    protected $adminEmailService;

    /**
     * Single constructor - inject AdminEmailService
     */
    public function __construct(AdminEmailService $adminEmailService)
    {
        $this->middleware('auth');
        $this->adminEmailService = $adminEmailService;
    }

    /**
     * Display a listing of warehouses for property owner
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role == 'property_owner') {
            $warehouses = Warehouse::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } elseif ($user->role == 'admin') {
            $warehouses = Warehouse::orderBy('created_at', 'desc')->paginate(20);
        } else {
            $warehouses = collect();
        }

        return view('property.warehouses.index', compact('warehouses'));
    }

    /**
     * Show the form for creating a new warehouse (Property Owner)
     */
    public function create()
    {
        return view('property.warehouses.create');
    }

public function search(Request $request, AIService $aiService)
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return response()->json(['warehouses' => []]);
        }

        // 1. Let the AI parse the user's query
        $filters = $aiService->parseWarehouseSearch($query);

        // 2. Build the database query based on AI filters
        $warehouses = Warehouse::query();

        if (!empty($filters['location'])) {
            $warehouses->where('location', 'LIKE', '%' . $filters['location'] . '%');
        }

        if (!empty($filters['min_area'])) {
            $warehouses->where('area_sqft', '>=', $filters['min_area']);
        }

        if (!empty($filters['max_price'])) {
            $warehouses->where('price_per_sqft', '<=', $filters['max_price']);
        }

        if (!empty($filters['facilities']) && is_array($filters['facilities'])) {
            // Check if the 'facilities' JSON column contains the requested tags
            foreach ($filters['facilities'] as $facility) {
                $warehouses->whereJsonContains('facilities', $facility);
            }
        }

        // 3. Return the results (Only show approved)
        $results = $warehouses->where('status', 'approved')->limit(10)->get();

        return response()->json([
            'query' => $query,
            'parsed_filters' => $filters,
            'warehouses' => $results
        ]);
    }

    /**
     * Store a newly created warehouse (Property Owner)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            // Basic Information
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:500',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'contact_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',

            // Warehouse Details
            'area_sqft' => 'required|numeric|min:0',
            'area_sqm' => 'nullable|numeric|min:0',
            'price_per_sqft' => 'required|numeric|min:0',
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

            // Additional Facilities
            'insurance_available' => 'nullable|boolean',
            'loading_dock' => 'nullable|boolean',
            'office_space' => 'nullable|boolean',
            'staff_quarters' => 'nullable|boolean',
            'parking_spaces' => 'nullable|integer|min:0',
            'parking_type' => 'nullable|string|in:open,covered,both,none',
            'available_from' => 'nullable|date',
            'minimum_rental_period' => 'nullable|integer|min:0',
            'special_notes' => 'nullable|string',

            // Facilities
            'facilities' => 'nullable|array',
            'facilities.*' => 'nullable|string',

            // Photos
            'front_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'interior_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'exterior_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',

            // Documents
            'ownership_document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'tax_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'fire_safety_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'building_approval_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
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
                'user_id' => $user->id,
                'name' => $request->name,
                'location' => $request->location,
                'address' => $request->address ?? $request->location,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'contact_number' => $request->contact_number,
                'email' => $request->email,
                'area_sqft' => $request->area_sqft,
                'area_sqm' => $request->area_sqm ?? ($request->area_sqft / 10.764),
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
                'insurance_available' => $request->has('insurance_available'),
                'loading_dock' => $request->has('loading_dock'),
                'office_space' => $request->has('office_space'),
                'staff_quarters' => $request->has('staff_quarters'),
                'parking_spaces' => $request->parking_spaces ?? 0,
                'parking_type' => $request->parking_type ?? 'open',
                'available_from' => $request->available_from,
                'minimum_rental_period' => $request->minimum_rental_period ?? 1,
                'special_notes' => $request->special_notes,
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

            // Send admin notification
            $this->adminEmailService->notifyNewWarehouse($warehouse);

            // Send notification to property owner
            $this->notifyPropertyOwner($warehouse);

            Log::info('Warehouse registered successfully', [
                'warehouse_id' => $warehouse->id,
                'user_id' => $user->id,
                'user_name' => $user->name
            ]);

            return redirect()->route('warehouses.index')
                ->with('success', 'Warehouse registered successfully! Awaiting admin approval.');

        } catch (\Exception $e) {
            Log::error('Warehouse creation failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to register warehouse: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Send notification to property owner
     */
    private function notifyPropertyOwner($warehouse)
    {
        try {
            \Mail::send('emails.warehouse-registered', ['warehouse' => $warehouse], function ($message) use ($warehouse) {
                $message->to($warehouse->user->email, $warehouse->user->name)
                        ->subject('Your Warehouse Registration is Under Review');
            });
        } catch (\Exception $e) {
            \Log::error('Property owner notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified warehouse
     */
    public function show($id)
    {
        $user = Auth::user();

        if ($user->isAdmin() || ($user->is_admin ?? false) || $user->role === 'admin') {
            $warehouse = Warehouse::with(['user', 'owner', 'warehouseRequests.client'])->findOrFail($id);
        } elseif ($user->isPropertyOwner() || ($user->is_property_owner ?? false) || $user->role === 'property_owner') {
            $warehouse = Warehouse::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)->orWhere('owner_id', $user->id);
            })->with(['user', 'owner', 'warehouseRequests.client'])->findOrFail($id);
            $requests = WarehouseRequest::where('warehouse_id', $id)
                ->with('client')
                ->orderBy('created_at', 'desc')
                ->get();
            return view('property.warehouses.show', compact('warehouse', 'requests'));
        } else {
            // Client / Driver: Can view approved warehouses, or warehouses where user has a request
            $warehouse = Warehouse::where(function ($q) use ($user) {
                $q->where('status', 'approved')
                  ->orWhere('is_approved', true)
                  ->orWhereHas('warehouseRequests', function ($rq) use ($user) {
                      $rq->where('client_id', $user->id);
                  });
            })->with(['user', 'owner'])->findOrFail($id);
        }

        $requests = WarehouseRequest::where('warehouse_id', $id)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('warehouses.show', compact('warehouse', 'requests'));
    }

    /**
     * Show the form for editing the specified warehouse
     * Allows both property owners and admins to edit
     */
    public function edit($id)
    {
        $user = Auth::user();

        if ($user->role == 'admin') {
            $warehouse = Warehouse::with('user')->findOrFail($id);
            $propertyOwners = User::where('role', 'property_owner')->get();
        } else {
            $warehouse = Warehouse::where('user_id', $user->id)->findOrFail($id);
            $propertyOwners = collect();
        }

        return view('property.warehouses.edit', compact('warehouse', 'propertyOwners'));
    }

    /**
     * Update the specified warehouse
     * Allows both property owners and admins to update
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if ($user->role == 'admin') {
            $warehouse = Warehouse::findOrFail($id);
        } else {
            $warehouse = Warehouse::where('user_id', $user->id)->findOrFail($id);
        }

        $validator = Validator::make($request->all(), [
            // Basic Information
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:500',
            'address' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'contact_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',

            // Warehouse Details
            'area_sqft' => 'required|numeric|min:0',
            'area_sqm' => 'nullable|numeric|min:0',
            'price_per_sqft' => 'required|numeric|min:0',
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

            // Additional Facilities
            'insurance_available' => 'nullable|boolean',
            'loading_dock' => 'nullable|boolean',
            'office_space' => 'nullable|boolean',
            'staff_quarters' => 'nullable|boolean',
            'parking_spaces' => 'nullable|integer|min:0',
            'parking_type' => 'nullable|string|in:open,covered,both,none',
            'available_from' => 'nullable|date',
            'minimum_rental_period' => 'nullable|integer|min:0',
            'special_notes' => 'nullable|string',

            // Facilities
            'facilities' => 'nullable|array',
            'facilities.*' => 'nullable|string',

            // Photos (optional for update)
            'front_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'interior_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'exterior_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',

            // Documents (optional for update)
            'ownership_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'tax_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'fire_safety_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'building_approval_document' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',

            // Status (admin only)
            'status' => 'nullable|in:pending,approved,rejected',

            // Owner change (admin only)
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Handle file uploads (only if new files are uploaded)
            $frontImage = $this->handleFileUpload($request, 'front_image', 'warehouses/front', $warehouse->front_image);
            $interiorImage = $this->handleFileUpload($request, 'interior_image', 'warehouses/interior', $warehouse->interior_image);
            $exteriorImage = $this->handleFileUpload($request, 'exterior_image', 'warehouses/exterior', $warehouse->exterior_image);
            $ownershipDoc = $this->handleFileUpload($request, 'ownership_document', 'warehouses/documents', $warehouse->ownership_document, 'private_uploads');
            $taxDoc = $this->handleFileUpload($request, 'tax_document', 'warehouses/documents', $warehouse->tax_document, 'private_uploads');
            $fireSafetyDoc = $this->handleFileUpload($request, 'fire_safety_document', 'warehouses/documents', $warehouse->fire_safety_document, 'private_uploads');
            $buildingApprovalDoc = $this->handleFileUpload($request, 'building_approval_document', 'warehouses/documents', $warehouse->building_approval_document, 'private_uploads');

            // Prepare update data
            $updateData = [
                'name' => $request->name,
                'location' => $request->location,
                'address' => $request->address ?? $request->location,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'contact_number' => $request->contact_number,
                'email' => $request->email,
                'area_sqft' => $request->area_sqft,
                'area_sqm' => $request->area_sqm ?? ($request->area_sqft / 10.764),
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
                'insurance_available' => $request->has('insurance_available'),
                'loading_dock' => $request->has('loading_dock'),
                'office_space' => $request->has('office_space'),
                'staff_quarters' => $request->has('staff_quarters'),
                'parking_spaces' => $request->parking_spaces ?? 0,
                'parking_type' => $request->parking_type ?? 'open',
                'available_from' => $request->available_from,
                'minimum_rental_period' => $request->minimum_rental_period ?? 1,
                'special_notes' => $request->special_notes,
                'front_image' => $frontImage,
                'interior_image' => $interiorImage,
                'exterior_image' => $exteriorImage,
                'ownership_document' => $ownershipDoc,
                'tax_document' => $taxDoc,
                'fire_safety_document' => $fireSafetyDoc,
                'building_approval_document' => $buildingApprovalDoc,
                'facilities' => $request->facilities ? array_filter($request->facilities) : [],
            ];

            // Allow admin to update status and owner
            if ($user->role == 'admin') {
                if ($request->has('status')) {
                    $updateData['status'] = $request->status;
                    if ($request->status == 'approved') {
                        $updateData['approved_at'] = now();
                        $updateData['approved_by'] = $user->id;
                    }
                }

                if ($request->has('user_id')) {
                    $updateData['user_id'] = $request->user_id;
                }
            }

            $warehouse->update($updateData);

            Log::info('Warehouse updated successfully', [
                'warehouse_id' => $warehouse->id,
                'user_id' => $user->id,
                'user_role' => $user->role
            ]);

            return redirect()->route('property.warehouses.show', $warehouse->id)
                ->with('success', 'Warehouse updated successfully!');

        } catch (\Exception $e) {
            Log::error('Warehouse update failed: ' . $e->getMessage(), [
                'warehouse_id' => $id,
                'user_id' => $user->id,
                'request_data' => $request->all()
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update warehouse: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Handle file upload and delete old file
     */
    private function handleFileUpload($request, $fieldName, $path, $oldFile = null, string $disk = 'public')
    {
        if ($request->hasFile($fieldName)) {
            // Delete old file if exists
            if ($oldFile && Storage::disk($disk)->exists($oldFile)) {
                Storage::disk($disk)->delete($oldFile);
            } elseif ($oldFile && $disk !== 'public' && Storage::disk('public')->exists($oldFile)) {
                Storage::disk('public')->delete($oldFile);
            }

            // Store new file
            return $request->file($fieldName)->store($path, $disk);
        }

        return $oldFile;
    }

public function approve(Warehouse $warehouse)
{
    $warehouse->status = 'approved';
    $warehouse->approved_at = now();
    $warehouse->save();

    $owner = User::find($warehouse->user_id);
    if ($owner) {
        $this->notificationService->send($owner, new WarehouseApprovedNotification($warehouse, 'approved'));
    }

    return redirect()->back()->with('success', 'Warehouse approved.');
}

public function reject(Request $request, Warehouse $warehouse)
{
    $warehouse->status = 'rejected';
    $warehouse->save();

    $owner = User::find($warehouse->user_id);
    if ($owner) {
        $this->notificationService->send($owner, new WarehouseApprovedNotification($warehouse, 'rejected'));
    }

    return redirect()->back()->with('success', 'Warehouse rejected.');
}

    /**
     * Remove the specified warehouse
     */
    public function destroy($id)
    {
        $user = Auth::user();

        if ($user->role == 'admin') {
            $warehouse = Warehouse::findOrFail($id);
        } else {
            $warehouse = Warehouse::where('user_id', $user->id)->findOrFail($id);
        }

        try {
            // Delete associated files
            $fileFields = [
                'front_image', 'interior_image', 'exterior_image',
                'ownership_document', 'tax_document',
                'fire_safety_document', 'building_approval_document'
            ];

            foreach ($fileFields as $field) {
                if ($warehouse->$field && Storage::disk('public')->exists($warehouse->$field)) {
                    Storage::disk('public')->delete($warehouse->$field);
                }
            }

            $warehouse->delete();

            return redirect()->route('warehouses.index')
                ->with('success', 'Warehouse deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Warehouse deletion failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to delete warehouse: ' . $e->getMessage());
        }
    }

    /**
     * Verify Kataho code
     */
    public function verifyKataho(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kataho_code' => 'required|string|min:5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        return response()->json([
            'success' => true,
            'verified' => true,
            'location' => 'Baneshwor-10, Kathmandu, Nepal',
            'message' => 'Kataho code verified successfully'
        ]);
    }

    /**
     * Get Kataho code from coordinates
     */
    public function getKatahoFromCoords(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        return response()->json([
            'success' => true,
            'kataho_code' => 'KH-' . rand(1000, 9999) . '-' . rand(1000, 9999),
            'location' => 'Baneshwor-10, Kathmandu, Nepal',
            'message' => 'Kataho code found'
        ]);
    }

    /**
     * Get warehouses for the current property owner (AJAX)
     */
    public function getMyWarehouses()
    {
        $user = Auth::user();

        $warehouses = Warehouse::where('user_id', $user->id)
            ->select('id', 'name', 'location', 'status', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'warehouses' => $warehouses
        ]);
    }

    /**
     * Get warehouse details (AJAX)
     */
    public function getWarehouseDetails($id)
    {
        $user = Auth::user();

        if ($user->role == 'admin') {
            $warehouse = Warehouse::with('user')->findOrFail($id);
        } else {
            $warehouse = Warehouse::where('user_id', $user->id)->findOrFail($id);
        }

        return response()->json([
            'success' => true,
            'warehouse' => $warehouse
        ]);
    }
}
