<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EquipmentRequest;

class ClientEquipmentController extends Controller
{
    public function index()
    {
        $requests = EquipmentRequest::where('client_id', auth()->id())
            ->latest()
            ->paginate(10);
        return view('client.equipment.requests', compact('requests'));
    }

    public function create()
    {
        return view('client.equipment.request');
    }

    public function store(Request $request)
    {
        $request->validate([
            'equipment_type' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'duration_days' => 'required|integer|min:1',
            'location' => 'required',
            'description' => 'required',
        ]);

        EquipmentRequest::create([
            'client_id' => auth()->id(),
            'equipment_type' => $request->equipment_type,
            'equipment_name' => $request->equipment_name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'duration_days' => $request->duration_days,
            'proposed_budget' => $request->proposed_budget,
            'location' => $request->location,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        return redirect()->route('client.equipment.requests')
            ->with('success', 'Equipment request submitted successfully.');
    }

    public function acceptQuote($id)
    {
        $request = EquipmentRequest::where('client_id', auth()->id())->findOrFail($id);
        $request->update(['status' => 'accepted']);
        
        return back()->with('success', 'Quote accepted. Equipment owner will be notified.');
    }

    public function rejectQuote($id)
    {
        $request = EquipmentRequest::where('client_id', auth()->id())->findOrFail($id);
        $request->update(['status' => 'rejected']);
        
        return back()->with('success', 'Quote rejected.');
    }

    public function negotiatePrice(Request $request, $id)
    {
        $request->validate([
            'counter_price' => 'required|numeric|min:0',
        ]);
        
        $equipmentRequest = EquipmentRequest::where('client_id', auth()->id())->findOrFail($id);
        $equipmentRequest->update([
            'counter_price' => $request->counter_price,
            'status' => 'negotiating'
        ]);
        
        return back()->with('success', 'Counter offer sent to equipment owner.');
    }
}