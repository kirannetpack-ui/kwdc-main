<?php

namespace App\Http\Controllers;

use App\Models\LoaderAssignment;
use App\Models\LoaderManager;
use App\Models\DispatchOrder;
use App\Services\NotificationService;
use App\Notifications\LoaderAssignmentCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoaderAssignmentController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
        $this->middleware('auth');
    }

    public function index()
    {
        $assignments = LoaderAssignment::with(['dispatch', 'manager'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('loader.assignments.index', compact('assignments'));
    }

    public function create()
    {
        $dispatches = DispatchOrder::where('status', 'pending')->get();
        $managers = LoaderManager::where('status', 'active')->get();
        return view('loader.assignments.create', compact('dispatches', 'managers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'dispatch_id' => 'required|exists:dispatch_orders,id',
            'loader_manager_id' => 'required|exists:loader_managers,id',
            'required_loaders' => 'required|integer|min:1',
            'assignment_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'nullable',
            'notes' => 'nullable|string',
        ]);

        $assignment = LoaderAssignment::create([
            'dispatch_id' => $validated['dispatch_id'],
            'loader_manager_id' => $validated['loader_manager_id'],
            'required_loaders' => $validated['required_loaders'],
            'assignment_date' => $validated['assignment_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'] ?? null,
            'notes' => $validated['notes'],
            'status' => 'pending',
        ]);

        // 🔔 Notify the Loader Manager
        $manager = LoaderManager::find($validated['loader_manager_id']);
        if ($manager) {
            $user = $manager->user; // assume loader_manager has user_id
            $this->notificationService->send($user, new LoaderAssignmentCreatedNotification($assignment));
        }

        // Notify admins
        $this->notificationService->sendToAdmins(new LoaderAssignmentCreatedNotification($assignment));

        return redirect()->route('loader.assignments.index')->with('success', 'Loader assignment created.');
    }

    public function updateStatus(Request $request, $id)
    {
        $assignment = LoaderAssignment::findOrFail($id);
        $oldStatus = $assignment->status;
        $newStatus = $request->status;

        if (!in_array($newStatus, ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])) {
            return response()->json(['error' => 'Invalid status'], 400);
        }

        $assignment->status = $newStatus;
        $assignment->save();

        if ($oldStatus !== $newStatus) {
            $notification = new LoaderAssignmentStatusUpdatedNotification($assignment);
            // Notify related parties
            $manager = $assignment->manager;
            if ($manager && $manager->user) {
                $this->notificationService->send($manager->user, $notification);
            }
            $this->notificationService->sendToAdmins($notification);
        }

        return response()->json(['success' => true]);
    }

    // Other methods (show, edit, etc.)
}