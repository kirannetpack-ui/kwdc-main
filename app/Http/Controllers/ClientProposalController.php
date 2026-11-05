<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Services\DriverJobAssignment;
use Illuminate\Support\Facades\DB;
use App\Models\WarehouseRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientProposalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of proposals for the client
     */
    public function index()
    {
        $proposals = Proposal::where('client_id', Auth::id())
            ->with(['warehouseRequest', 'driver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('client.proposals.index', compact('proposals'));
    }

    /**
     * Show the form for creating a new proposal
     */
    public function create($requestId = null)
    {
        $warehouseRequests = WarehouseRequest::where('client_id', Auth::id())
            ->where('status', 'approved')
            ->get();
        
        $drivers = User::where('role', 'driver')->get();
        
        return view('client.proposals.create', compact('warehouseRequests', 'drivers'));
    }

    /**
     * Store a newly created proposal
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_request_id' => 'required|exists:warehouse_requests,id',
            'driver_id' => 'required|exists:users,id',
            'proposed_price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'valid_until' => 'nullable|date|after:today',
        ]);

        $validated['client_id'] = Auth::id();
        $validated['status'] = 'pending';

        $proposal = Proposal::create($validated);

        // Send notification to driver (optional)
        // $this->sendNotification($proposal);

        return redirect()->route('client.proposals.show', $proposal->id)
            ->with('success', 'Proposal created successfully!');
    }

    /**
     * Display the specified proposal
     */
    public function show($id)
    {
        $proposal = Proposal::where('client_id', Auth::id())
            ->with(['warehouseRequest', 'driver', 'warehouse'])
            ->findOrFail($id);
        
        return view('client.proposals.show', compact('proposal'));
    }

    /**
     * Show the form for editing the specified proposal
     */
    public function edit($id)
    {
        $proposal = Proposal::where('client_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);
        
        return view('client.proposals.edit', compact('proposal'));
    }

    /**
     * Update the specified proposal
     */
    public function update(Request $request, $id)
    {
        $proposal = Proposal::where('client_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);
        
        $validated = $request->validate([
            'proposed_price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'valid_until' => 'nullable|date|after:today',
        ]);

        $proposal->update($validated);

        return redirect()->route('client.proposals.show', $proposal->id)
            ->with('success', 'Proposal updated successfully!');
    }

    /**
     * Remove the specified proposal
     */
    public function destroy($id)
    {
        $proposal = Proposal::where('client_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);
        
        $proposal->delete();

        return redirect()->route('client.proposals.index')
            ->with('success', 'Proposal deleted successfully!');
    }

    /**
     * Accept a proposal
     */
    public function accept($id)
    {
        $proposal = DB::transaction(function () use ($id) {
            $proposal = Proposal::where('client_id', Auth::id())->lockForUpdate()->findOrFail($id);
            abort_if($proposal->isExpired() || !$proposal->isPending(), 409, 'This quote is no longer available.');

            if ($proposal->job_type === 'dispatch') {
                app(DriverJobAssignment::class)->acceptQuote($proposal);
            } else {
                $proposal->accept();
            }

            return $proposal;
        }, 3);

        return redirect()->route('client.proposals.show', $proposal->id)
            ->with('success', 'Proposal accepted successfully!');
    }

    /**
     * Reject a proposal
     */
    public function reject($id)
    {
        $proposal = Proposal::where('client_id', Auth::id())
            ->findOrFail($id);
        
        $proposal->reject();

        return redirect()->route('client.proposals.show', $proposal->id)
            ->with('success', 'Proposal rejected.');
    }

    /**
     * Negotiate a proposal (send counter offer)
     */
    public function negotiate(Request $request, $id)
    {
        $validated = $request->validate([
            'counter_price' => 'required|numeric|min:0',
            'message' => 'nullable|string|max:500',
        ]);

        $proposal = Proposal::where('client_id', Auth::id())
            ->findOrFail($id);
        
        abort_if($proposal->isExpired() || !$proposal->isPending(), 409, 'This quote is no longer available.');
        $proposal->counter($validated['counter_price'], $validated['message']);

        return redirect()->route('client.proposals.show', $proposal->id)
            ->with('success', 'Counter offer sent to driver!');
    }

    /**
     * Accept driver's counter offer
     */
    public function acceptCounter($id)
    {
        $proposal = Proposal::where('client_id', Auth::id())
            ->where('status', 'negotiating')
            ->findOrFail($id);
        
        abort_if($proposal->job_type === 'dispatch' || $proposal->isExpired(), 409, 'This counter offer is awaiting the driver or has expired.');
        $proposal->accept();

        return redirect()->route('client.proposals.show', $proposal->id)
            ->with('success', 'Counter offer accepted!');
    }
}