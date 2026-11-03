<?php

namespace App\Http\Controllers;

use App\Models\EquipmentJob;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EquipmentJobController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of equipment jobs
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->role == 'equipment_owner') {
            $jobs = EquipmentJob::where('owner_id', $user->id)
                ->with(['equipment', 'client'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } elseif ($user->role == 'client') {
            $jobs = EquipmentJob::where('client_id', $user->id)
                ->with(['equipment', 'owner'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        } else {
            $jobs = EquipmentJob::with(['equipment', 'client', 'owner'])
                ->orderBy('created_at', 'desc')
                ->paginate(20);
        }
        
        return view('equipment.jobs.index', compact('jobs'));
    }

    /**
     * Show pending job requests
     */
    public function requests()
    {
        $user = Auth::user();
        
        $jobRequests = EquipmentJob::where('owner_id', $user->id)
            ->where('status', 'pending')
            ->with(['equipment', 'client'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('equipment.jobs.requests', compact('jobRequests'));
    }

    /**
     * Show active jobs
     */
    public function active()
    {
        $user = Auth::user();
        
        $activeJobs = EquipmentJob::where('owner_id', $user->id)
            ->whereIn('status', ['accepted', 'in_progress', 'started'])
            ->with(['equipment', 'client'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('equipment.jobs.active', compact('activeJobs'));
    }

    /**
     * Show job history
     */
    public function history()
    {
        $user = Auth::user();
        
        $completedJobs = EquipmentJob::where('owner_id', $user->id)
            ->where('status', 'completed')
            ->with(['equipment', 'client'])
            ->orderBy('completed_at', 'desc')
            ->paginate(20);
        
        return view('equipment.jobs.history', compact('completedJobs'));
    }

    /**
     * Show earnings
     */
    public function earnings()
    {
        $user = Auth::user();
        
        $totalEarnings = EquipmentJob::where('owner_id', $user->id)
            ->where('status', 'completed')
            ->sum('price') ?? 0;
        
        $monthlyEarnings = EquipmentJob::where('owner_id', $user->id)
            ->where('status', 'completed')
            ->whereYear('completed_at', date('Y'))
            ->whereMonth('completed_at', date('m'))
            ->sum('price') ?? 0;
        
        $weeklyEarnings = EquipmentJob::where('owner_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('price') ?? 0;
        
        $recentEarnings = EquipmentJob::where('owner_id', $user->id)
            ->where('status', 'completed')
            ->with(['equipment', 'client'])
            ->orderBy('completed_at', 'desc')
            ->limit(10)
            ->get();
        
        $stats = [
            'total_earnings' => $totalEarnings,
            'monthly_earnings' => $monthlyEarnings,
            'weekly_earnings' => $weeklyEarnings,
            'total_jobs' => EquipmentJob::where('owner_id', $user->id)->count(),
            'completed_jobs' => EquipmentJob::where('owner_id', $user->id)->where('status', 'completed')->count(),
            'pending_jobs' => EquipmentJob::where('owner_id', $user->id)->where('status', 'pending')->count(),
            'active_jobs' => EquipmentJob::where('owner_id', $user->id)->whereIn('status', ['accepted', 'in_progress'])->count(),
        ];
        
        return view('equipment.jobs.earnings', compact('stats', 'recentEarnings'));
    }

    /**
     * Show job details
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $job = EquipmentJob::with(['equipment', 'client', 'owner'])
            ->where(function($q) use ($user) {
                $q->where('owner_id', $user->id)
                  ->orWhere('client_id', $user->id);
            })
            ->findOrFail($id);
        
        return view('equipment.jobs.show', compact('job'));
    }

    /**
     * Accept a job
     */
    public function accept($id)
    {
        $job = EquipmentJob::where('owner_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        
        $job->status = 'accepted';
        $job->accepted_at = now();
        $job->save();
        
        // Create notification for client
        $this->createNotification($job, 'accepted');
        
        return redirect()->back()->with('success', 'Job accepted successfully!');
    }

    /**
     * Reject a job
     */
    public function reject($id)
    {
        $job = EquipmentJob::where('owner_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        
        $job->status = 'rejected';
        $job->rejected_at = now();
        $job->save();
        
        // Create notification for client
        $this->createNotification($job, 'rejected');
        
        return redirect()->back()->with('success', 'Job rejected.');
    }

    /**
     * Propose a price for a job
     */
    public function proposePrice(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'proposed_price' => 'required|numeric|min:0',
            'message' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $job = EquipmentJob::where('owner_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        
        $job->proposed_price = $request->proposed_price;
        $job->proposal_message = $request->message;
        $job->status = 'price_proposed';
        $job->proposed_at = now();
        $job->save();
        
        // Create notification for client
        $this->createNotification($job, 'price_proposed');
        
        return redirect()->back()->with('success', 'Price proposed successfully!');
    }

    /**
     * Start a job
     */
    public function start($id)
    {
        $job = EquipmentJob::where('owner_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        
        $job->status = 'in_progress';
        $job->started_at = now();
        $job->save();
        
        // Create notification for client
        $this->createNotification($job, 'started');
        
        return redirect()->back()->with('success', 'Job started!');
    }

    /**
     * Complete a job
     */
    public function complete($id)
    {
        $job = EquipmentJob::where('owner_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();
        
        $job->status = 'completed';
        $job->completed_at = now();
        $job->completion_date = now();
        $job->save();
        
        // Create notification for client
        $this->createNotification($job, 'completed');
        
        return redirect()->back()->with('success', 'Job completed successfully!');
    }

    /**
     * Cancel a job
     */
    public function cancel($id)
    {
        $job = EquipmentJob::where('owner_id', Auth::id())
            ->where('id', $id)
            ->whereIn('status', ['pending', 'accepted'])
            ->firstOrFail();
        
        $job->status = 'cancelled';
        $job->save();
        
        // Create notification for client
        $this->createNotification($job, 'cancelled');
        
        return redirect()->back()->with('success', 'Job cancelled.');
    }

    /**
     * Create notification for job status change
     */
    private function createNotification($job, $status)
    {
        $messages = [
            'accepted' => 'Your job request has been accepted.',
            'rejected' => 'Your job request has been rejected.',
            'price_proposed' => 'A price has been proposed for your job request.',
            'started' => 'Your job has been started.',
            'completed' => 'Your job has been completed.',
            'cancelled' => 'Your job has been cancelled.',
        ];
        
        \App\Models\Notification::create([
            'user_id' => $job->client_id,
            'type' => 'equipment_job',
            'title' => 'Job ' . ucfirst($status),
            'message' => $messages[$status] ?? 'Job status updated.',
            'related_id' => $job->id,
            'related_type' => 'equipment_job',
        ]);
    }

    /**
     * Get job statistics for equipment owner dashboard
     */
    public function getStats()
    {
        $user = Auth::user();
        
        return [
            'pending' => EquipmentJob::where('owner_id', $user->id)
                ->where('status', 'pending')
                ->count(),
            'active' => EquipmentJob::where('owner_id', $user->id)
                ->whereIn('status', ['accepted', 'in_progress'])
                ->count(),
            'completed' => EquipmentJob::where('owner_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'total_earnings' => EquipmentJob::where('owner_id', $user->id)
                ->where('status', 'completed')
                ->sum('price') ?? 0,
        ];
    }
}