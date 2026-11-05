<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WarehouseRequest;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Services\WarehouseAvailability;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientRequestHandler extends Controller
{
    public function index()
    {
        $requests = WarehouseRequest::where('client_id', auth()->id())
            ->latest()
            ->paginate(10);
        return view('client.requests.index', compact('requests'));
    }
    
    public function create(Request $request, WarehouseAvailability $availability)
{
    $filters = $request->validate([
        'location' => 'nullable|string|max:150',
        'min_area' => 'nullable|numeric|min:1',
        'start_date' => 'nullable|date|after_or_equal:today',
        'end_date' => 'nullable|date|after_or_equal:today'.($request->filled('start_date') ? '|after_or_equal:start_date' : ''),
        'lat' => 'nullable|numeric|between:-90,90|required_with:lng',
        'lng' => 'nullable|numeric|between:-180,180|required_with:lat',
        'radius' => 'nullable|numeric|between:1,100',
    ]);
    $searching = collect($filters)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty();
    $warehouses = $availability->search($filters + ['include_unavailable' => !$searching]);
    $availabilityByWarehouse = $warehouses->mapWithKeys(fn ($warehouse) => [$warehouse->id => $warehouse->current_availability]);

    // Get user's preferred location if exists
    $userPreferredLat = auth()->user()->preferred_latitude ?? null;
    $userPreferredLng = auth()->user()->preferred_longitude ?? null;
    
    return view('client.requests.create', compact('warehouses', 'availabilityByWarehouse', 'filters', 'searching', 'userPreferredLat', 'userPreferredLng'));
}
    
    public function store(Request $request, WarehouseAvailability $availability)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'required_area' => 'required|numeric|min:1',
            'duration_months' => 'required|integer|min:1',
            'purpose' => 'required|string',
            'preferred_start_date' => 'nullable|date|after_or_equal:today',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'invoice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'packing_list' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'insurance' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $warehouse = Warehouse::where('status', 'approved')->findOrFail($request->warehouse_id);
        $start = $request->filled('preferred_start_date') ? \Illuminate\Support\Carbon::parse($request->preferred_start_date)->startOfDay() : today();
        if ((float) $request->required_area > $availability->snapshot($warehouse, $start, $start->copy()->addMonthsNoOverflow((int) $request->duration_months)->subDay())['available']) {
            throw ValidationException::withMessages(['required_area' => 'This much warehouse space is unavailable on the requested start date.']);
        }

        $data = [
            'client_id' => auth()->id(),
            'warehouse_id' => $request->warehouse_id,
            'required_area' => $request->required_area,
            'space_required' => $request->required_area,
            'duration_months' => $request->duration_months,
            'purpose' => $request->purpose,
            'preferred_start_date' => $request->preferred_start_date,
            'contact_person' => $request->contact_person,
            'contact_phone' => $request->contact_phone,
            'status' => 'pending',
        ];

        if ($request->hasFile('invoice')) {
            $data['invoice_path'] = $request->file('invoice')->store('warehouse-requests/documents/invoices', 'private_uploads');
        }

        if ($request->hasFile('packing_list')) {
            $data['packing_list_path'] = $request->file('packing_list')->store('warehouse-requests/documents/packing-lists', 'private_uploads');
        }

        if ($request->hasFile('insurance')) {
            $data['insurance_path'] = $request->file('insurance')->store('warehouse-requests/documents/insurance', 'private_uploads');
        }

        WarehouseRequest::create($data);
        
        return redirect()->route('my-requests.index')
            ->with('success', 'Request submitted successfully');
    }
    
    public function show($id)
    {
        $request = WarehouseRequest::where('client_id', auth()->id())
            ->with('warehouse')
            ->findOrFail($id);
        return view('client.requests.show', compact('request'));
    }
    
    public function renewal(Request $request, int $id)
    {
        $decision = $request->validate(['decision' => 'required|in:renew,vacate'])['decision'];

        DB::transaction(function () use ($id, $decision) {
            $agreement = WarehouseRequest::where('client_id', auth()->id())->lockForUpdate()->findOrFail($id);
            $end = $agreement->agreementEnd();
            abort_unless($agreement->status === 'assigned' && $end && today()->lt($agreement->renewalDeadline())
                && today()->gte($end->copy()->subMonthsNoOverflow(2))
                && !$agreement->renewalChoice(), 409, 'The renewal window is closed or your decision was already recorded.');

            $agreement->forceFill([
                'renewal_decision' => $decision,
                'renewal_decided_for' => $end->toDateString(),
            ])->save();
        });

        return back()->with('success', 'Your warehouse decision has been recorded.');
    }

    public function myStock()
    {
        $stocks = Stock::whereHas('warehouseRequest', function($q) {
            $q->where('client_id', auth()->id());
        })->latest()->paginate(10);
        return view('client.stock', compact('stocks'));
    }
    
    public function myInsurance()
    {
        return view('client.insurance');
    }
    
    public function savePreferredLocation(Request $request)
    {
        $user = auth()->user();
        $user->preferred_latitude = $request->latitude;
        $user->preferred_longitude = $request->longitude;
        $user->preferred_location_name = $request->location_name;
        $user->search_radius = $request->radius;
        $user->save();
        
        return response()->json(['success' => true]);
    }
}
