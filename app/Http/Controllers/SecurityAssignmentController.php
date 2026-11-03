<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\SecurityAssignment;
use App\Models\SecurityPersonnel;
use App\Models\Warehouse;
use App\Models\SecurityAgency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SecurityAssignmentController extends Controller
{
    protected function getAgency()
    {
        $agency = Auth::user()->securityAgency;

        if (!$agency) {
            abort(403, 'Security agency profile not found. Please complete your agency profile first.');
        }

        return $agency;
    }

    protected function notifyAgency(SecurityAgency $agency, string $type, string $title, string $message, ?SecurityAssignment $assignment = null): void
    {
        Notification::createNotification(
            $agency->user_id ?? Auth::id(),
            $type,
            $title,
            $message,
            $assignment?->id,
            SecurityAssignment::class
        );

        $emailAddress = $agency->email ?: ($agency->user?->email ?? Auth::user()->email ?? null);

        if ($emailAddress) {
            try {
                Mail::raw($message, function ($mail) use ($emailAddress, $title) {
                    $mail->to($emailAddress)->subject($title);
                });
            } catch (\Throwable $e) {
                Log::warning('Failed to send agency assignment notification email.', [
                    'agency_id' => $agency->id,
                    'email' => $emailAddress,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function ensureAgencyOwnsAssignment(SecurityAssignment $assignment): SecurityAgency
    {
        $agency = $this->getAgency();

        abort_unless($assignment->agency_id === $agency->id, 403);

        return $agency;
    }

    public function index()
    {
        $agency = $this->getAgency();
        $assignments = $agency->assignments()
            ->with(['personnel', 'warehouse'])
            ->paginate(10);

        return view('security.assignments.index', compact('assignments'));
    }

    public function create()
    {
        $agency = $this->getAgency();

        $personnel = $agency->personnel()
            ->where('status', 'active')
            ->pluck('name', 'id');

        $warehouses = Warehouse::pluck('name', 'id');

        return view('security.assignments.create', compact('personnel', 'warehouses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'warehouse_id'  => 'required|exists:warehouses,id',
            'personnel_id'  => 'nullable|exists:security_personnels,id',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'shift'         => 'required|in:day,night,24_hours,custom',
            'shift_start'   => 'nullable|date_format:H:i',
            'shift_end'     => 'nullable|date_format:H:i',
            'status'        => 'nullable|in:active,completed,cancelled',
            'notes'         => 'nullable|string',
            'total_cost'    => 'nullable|numeric|min:0',
        ]);

        if (!empty($validated['personnel_id'])) {
            $agency = $this->getAgency();
            $belongsToAgency = $agency->personnel()
                ->where('id', $validated['personnel_id'])
                ->exists();

            if (!$belongsToAgency) {
                return back()->withErrors(['personnel_id' => 'Selected personnel does not belong to your agency.']);
            }
        }

        $agency = $this->getAgency();
        $assignment = $agency->assignments()->create($validated);

        $this->notifyAgency(
            $agency,
            'security_assignment_created',
            'Assignment created',
            "Assignment for warehouse #{$assignment->warehouse_id} was created successfully.",
            $assignment
        );

        return redirect()->route('security.assignments.index')
            ->with('success', 'Assignment created successfully.');
    }

    public function show(SecurityAssignment $assignment)
    {
        $this->ensureAgencyOwnsAssignment($assignment);

        return view('security.assignments.show', compact('assignment'));
    }

    public function edit(SecurityAssignment $assignment)
    {
        $agency = $this->ensureAgencyOwnsAssignment($assignment);
        $personnel = $agency->personnel()
            ->where('status', 'active')
            ->pluck('name', 'id');
        $warehouses = Warehouse::pluck('name', 'id');

        return view('security.assignments.edit', compact('assignment', 'personnel', 'warehouses'));
    }

    public function update(Request $request, SecurityAssignment $assignment)
    {
        $agency = $this->ensureAgencyOwnsAssignment($assignment);

        $validated = $request->validate([
            'warehouse_id'  => 'required|exists:warehouses,id',
            'personnel_id'  => 'nullable|exists:security_personnels,id',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'shift'         => 'required|in:day,night,24_hours,custom',
            'shift_start'   => 'nullable|date_format:H:i',
            'shift_end'     => 'nullable|date_format:H:i',
            'status'        => 'nullable|in:active,completed,cancelled',
            'notes'         => 'nullable|string',
            'total_cost'    => 'nullable|numeric|min:0',
        ]);

        if (!empty($validated['personnel_id'])) {
            $belongsToAgency = $agency->personnel()
                ->where('id', $validated['personnel_id'])
                ->exists();

            if (!$belongsToAgency) {
                return back()->withErrors(['personnel_id' => 'Selected personnel does not belong to your agency.']);
            }
        }

        $assignment->update($validated);

        $this->notifyAgency(
            $agency,
            'security_assignment_updated',
            'Assignment updated',
            "Assignment for warehouse #{$assignment->warehouse_id} was updated successfully.",
            $assignment
        );

        return redirect()->route('security.assignments.index')
            ->with('success', 'Assignment updated successfully.');
    }

    public function destroy(SecurityAssignment $assignment)
    {
        $agency = $this->ensureAgencyOwnsAssignment($assignment);
        $warehouseId = $assignment->warehouse_id;
        $assignment->delete();

        $this->notifyAgency(
            $agency,
            'security_assignment_deleted',
            'Assignment deleted',
            "Assignment for warehouse #{$warehouseId} was deleted successfully.",
            $assignment
        );

        return redirect()->route('security.assignments.index')
            ->with('success', 'Assignment deleted successfully.');
    }
}
