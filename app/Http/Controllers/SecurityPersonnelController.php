<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\SecurityPersonnel;
use App\Models\SecurityAgency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SecurityPersonnelController extends Controller
{
    protected function getAgency()
    {
        $agency = Auth::user()->securityAgency;

        if (!$agency) {
            abort(403, 'Security agency profile not found. Please complete your agency profile first.');
        }

        return $agency;
    }

    protected function notifyAgency(SecurityAgency $agency, string $type, string $title, string $message, ?SecurityPersonnel $personnel = null): void
    {
        $userId = $agency->user_id ?? Auth::id();

        Notification::createNotification(
            $userId,
            $type,
            $title,
            $message,
            $personnel?->id,
            SecurityPersonnel::class
        );

        $emailAddress = $agency->email ?: ($agency->user?->email ?? Auth::user()->email ?? null);

        if ($emailAddress) {
            try {
                Mail::raw($message, function ($mail) use ($emailAddress, $title) {
                    $mail->to($emailAddress)
                        ->subject($title);
                });
            } catch (\Throwable $e) {
                Log::warning('Failed to send security personnel email to agency.', [
                    'agency_id' => $agency->id,
                    'email' => $emailAddress,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function ensureAgencyOwnsPersonnel(SecurityPersonnel $personnel): SecurityAgency
    {
        $agency = $this->getAgency();

        abort_unless($personnel->agency_id === $agency->id, 403);

        return $agency;
    }

    public function index()
    {
        $agency = $this->getAgency();
        $personnel = $agency->personnel()->paginate(10);
        return view('security.personnel.index', compact('personnel'));
    }

    public function create()
    {
        return view('security.personnel.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'position' => 'nullable|string|max:255',
            'employee_id' => 'nullable|string|max:100',
            'citizenship_number' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'address' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'training_certificate' => 'nullable|string',
            'training_expiry' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,on_leave',
            'has_vehicle' => 'nullable|boolean',
            'vehicle_type' => 'nullable|string',
            'shift_availability' => 'nullable|json',
            'photo' => 'nullable|image|max:2048',
        ]);

        $agency = $this->getAgency();
        $personnel = $agency->personnel()->create($validated);

        $this->notifyAgency(
            $agency,
            'security_personnel_created',
            'Personnel added to your security agency',
            "Personnel '{$personnel->name}' was added successfully to your agency.",
            $personnel
        );

        return redirect()->route('security.personnel.index')
            ->with('success', 'Personnel added successfully.');
    }

    public function edit(SecurityPersonnel $personnel)
    {
        $this->ensureAgencyOwnsPersonnel($personnel);

        return view('security.personnel.edit', compact('personnel'));
    }

    public function update(Request $request, SecurityPersonnel $personnel)
    {
        $agency = $this->ensureAgencyOwnsPersonnel($personnel);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'position' => 'nullable|string|max:255',
            'employee_id' => 'nullable|string|max:100',
            'citizenship_number' => 'nullable|string|max:50',
            'dob' => 'nullable|date',
            'address' => 'nullable|string',
            'qualifications' => 'nullable|string',
            'training_certificate' => 'nullable|string',
            'training_expiry' => 'nullable|date',
            'status' => 'nullable|in:active,inactive,on_leave',
            'has_vehicle' => 'nullable|boolean',
            'vehicle_type' => 'nullable|string',
            'shift_availability' => 'nullable|json',
            'photo' => 'nullable|image|max:2048',
        ]);

        $personnel->update($validated);

        $this->notifyAgency(
            $agency,
            'security_personnel_updated',
            'Personnel updated',
            "Personnel '{$personnel->name}' was updated successfully.",
            $personnel
        );

        return redirect()->route('security.personnel.index')->with('success', 'Updated.');
    }

    public function destroy(SecurityPersonnel $personnel)
    {
        $agency = $this->ensureAgencyOwnsPersonnel($personnel);
        $personnelName = $personnel->name;
        $personnel->delete();

        $this->notifyAgency(
            $agency,
            'security_personnel_deleted',
            'Personnel deleted',
            "Personnel '{$personnelName}' was deleted from your agency.",
            $personnel
        );

        return redirect()->route('security.personnel.index')->with('success', 'Deleted.');
    }
}
