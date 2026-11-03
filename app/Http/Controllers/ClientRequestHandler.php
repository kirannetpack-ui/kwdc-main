<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WarehouseRequest;
use App\Models\Stock;
use App\Models\Warehouse;

class ClientRequestHandler extends Controller
{
    public function index()
    {
        $requests = WarehouseRequest::where('client_id', auth()->id())
            ->latest()
            ->paginate(10);
        return view('client.requests.index', compact('requests'));
    }
    
    public function create()
{
    $warehouses = Warehouse::where('status', 'approved')->get();
    
    // Get user's preferred location if exists
    $userPreferredLat = auth()->user()->preferred_latitude ?? null;
    $userPreferredLng = auth()->user()->preferred_longitude ?? null;
    
    return view('client.requests.create', compact('warehouses', 'userPreferredLat', 'userPreferredLng'));
}
    
    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'required_area' => 'required|numeric|min:1',
            'duration_months' => 'required|integer|min:1',
            'purpose' => 'required|string',
            'preferred_start_date' => 'nullable|date',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'invoice' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'packing_list' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'insurance' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

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
