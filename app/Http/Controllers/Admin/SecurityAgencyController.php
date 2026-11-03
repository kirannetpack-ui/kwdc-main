<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecurityAgency;
use App\Mail\AgencyApprovedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use PDF;

class SecurityAgencyController extends Controller
{
    public function index()
    {
        $agencies = SecurityAgency::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('admin.security.agencies', compact('agencies'));
    }

    public function show(SecurityAgency $agency)
    {
        $agency->load(['user', 'personnel', 'goods', 'rates']);
        return view('admin.security.agency-detail', compact('agency'));
    }

    public function approve(SecurityAgency $agency)
    {
        abort_unless($agency->status === 'pending', 409, 'This security agency has already been reviewed.');
        $agency->status = 'approved';
        $agency->approved_at = now();
        $agency->is_verified = true;
        $agency->save();

        // Send approval email safely
        try {
            if ($agency->user?->email) {
                Mail::to($agency->user->email)->send(new AgencyApprovedMail($agency));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Security agency approval email failed: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Security agency approved successfully!');
    }

    public function reject(Request $request, SecurityAgency $agency)
    {
        abort_unless($agency->status === 'pending', 409, 'This security agency has already been reviewed.');
        $agency->status = 'suspended';
        $agency->admin_notes = $request->reason;
        $agency->save();

        return redirect()->back()->with('success', 'Agency registration rejected.');
    }

    public function export(SecurityAgency $agency)
    {
        $pdf = PDF::loadView('admin.security.agency-export', compact('agency'));
        return $pdf->download('agency-' . $agency->id . '.pdf');
    }
}