@extends('layouts.app')

@section('title', 'Equipment Jobs')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Equipment Jobs</h2>
        <a href="{{ route('equipment.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card stats-card stats-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="stats-label">Total Jobs</h6>
                            <h2 class="stats-number">{{ $stats['total_jobs'] ?? 0 }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stats-card stats-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="stats-label">Pending</h6>
                            <h2 class="stats-number">{{ $stats['pending'] ?? 0 }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stats-card stats-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="stats-label">Active</h6>
                            <h2 class="stats-number">{{ $stats['active'] ?? 0 }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-spinner"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card stats-card stats-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="stats-label">Completed</h6>
                            <h2 class="stats-number">{{ $stats['completed'] ?? 0 }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Requests -->
    @if(isset($jobRequests) && $jobRequests->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-clipboard-list text-warning me-2"></i>Job Requests</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Equipment</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobRequests as $job)
                        <tr>
                            <td>#{{ $job->id }}</td>
                            <td>{{ $job->client->name ?? 'N/A' }}</td>
                            <td>{{ $job->equipment->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $job->status_badge ?? 'warning' }}">
                                    {{ ucfirst($job->status) }}
                                </span>
                            </td>
                            <td>रु {{ number_format($job->price ?? 0, 2) }}</td>
                            <td>
                                @if($job->status == 'pending')
                                    <div class="btn-group btn-group-sm">
                                        <form action="{{ route('equipment.jobs.accept', $job->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success">Accept</button>
                                        </form>
                                        <form action="{{ route('equipment.jobs.reject', $job->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Reject this job?')">Reject</button>
                                        </form>
                                        <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#proposeModal{{ $job->id }}">
                                            Propose Price
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>

                        <!-- Propose Price Modal -->
                        <div class="modal fade" id="proposeModal{{ $job->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Propose Price for Job #{{ $job->id }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form action="{{ route('equipment.jobs.propose-price', $job->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Your Price (रु) <span class="text-danger">*</span></label>
                                                <input type="number" name="price" class="form-control" step="0.01" min="0" required placeholder="Enter your price">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Notes</label>
                                                <textarea name="notes" class="form-control" rows="3" placeholder="Add any notes..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Submit Price</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($jobRequests, 'links'))
            <div class="card-footer">
                {{ $jobRequests->links() }}
            </div>
        @endif
    </div>
    @endif

    <!-- Active Jobs -->
    @if(isset($activeJobs) && $activeJobs->count() > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-tasks text-primary me-2"></i>Active Jobs</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Equipment</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeJobs as $job)
                        <tr>
                            <td>#{{ $job->id }}</td>
                            <td>{{ $job->client->name ?? 'N/A' }}</td>
                            <td>{{ $job->equipment->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-{{ $job->status === 'in_progress' ? 'primary' : 'info' }}">
                                    {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                                </span>
                            </td>
                            <td>रु {{ number_format($job->price ?? 0, 2) }}</td>
                            <td>
                                @if($job->status == 'accepted')
                                    <form action="{{ route('equipment.jobs.start', $job->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-primary">Start</button>
                                    </form>
                                @endif
                                @if($job->status == 'in_progress')
                                    <form action="{{ route('equipment.jobs.complete', $job->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Complete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($activeJobs, 'links'))
            <div class="card-footer">
                {{ $activeJobs->links() }}
            </div>
        @endif
    </div>
    @endif

    <!-- Completed Jobs -->
    @if(isset($completedJobs) && $completedJobs->count() > 0)
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-check-circle text-success me-2"></i>Completed Jobs</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Client</th>
                            <th>Equipment</th>
                            <th>Price</th>
                            <th>Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($completedJobs as $job)
                        <tr>
                            <td>#{{ $job->id }}</td>
                            <td>{{ $job->client->name ?? 'N/A' }}</td>
                            <td>{{ $job->equipment->name ?? 'N/A' }}</td>
                            <td>रु {{ number_format($job->price ?? 0, 2) }}</td>
                            <td>{{ $job->completed_at ? $job->completed_at->format('M d, Y H:i') : 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($completedJobs, 'links'))
            <div class="card-footer">
                {{ $completedJobs->links() }}
            </div>
        @endif
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    .stats-card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    
    .stats-card .stats-label {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }
    
    .stats-card .stats-number {
        font-size: 28px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    
    .stats-card .stats-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    
    .stats-card.stats-primary .stats-icon {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }
    
    .stats-card.stats-success .stats-icon {
        background: rgba(34, 197, 94, 0.1);
        color: #22c55e;
    }
    
    .stats-card.stats-warning .stats-icon {
        background: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }
    
    .stats-card.stats-danger .stats-icon {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
</style>
@endpush