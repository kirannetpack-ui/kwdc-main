@extends('layouts.app')

@section('title', 'My Jobs - Driver')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Jobs</h2>
        <a href="{{ route('driver.dashboard') }}" class="btn btn-secondary">
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
                            <h6 class="stats-label">Active Jobs</h6>
                            <h2 class="stats-number">{{ $activeJobs->total() ?? 0 }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-spinner"></i>
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
                            <h6 class="stats-label">Completed</h6>
                            <h2 class="stats-number">{{ $completedJobs->total() ?? 0 }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-check-circle"></i>
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
                            <h6 class="stats-label">Available</h6>
                            <h2 class="stats-number">{{ $availableJobs->total() ?? 0 }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-clock"></i>
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
                            <h6 class="stats-label">Cancelled</h6>
                            <h2 class="stats-number">{{ $cancelledJobs->total() ?? 0 }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Jobs -->
    @if(isset($availableJobs) && $availableJobs->count() > 0)
    <div class="card mb-3 border-0 shadow-2xs rounded-xl overflow-hidden">
        <div class="card-header bg-slate-50 border-bottom border-slate-100 py-2.5 px-3.5">
            <h5 class="mb-0 text-xs font-bold text-slate-900"><i class="fas fa-clock text-amber-500 me-1.5"></i>Available Jobs</h5>
        </div>
        <div class="card-body p-0">
            <div class="overflow-hidden">
                <table class="kwdc-table-fixed text-left mb-0">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="w-[12%]">Job ID</th>
                            <th class="w-[24%]">Client</th>
                            <th class="w-[32%]">Pickup Location</th>
                            <th class="w-[12%]">Distance</th>
                            <th class="w-[10%]">Amount</th>
                            <th class="w-[10%] text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($availableJobs as $job)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="font-bold text-slate-900 font-mono truncate">#{{ $job->id }}</td>
                            <td class="font-semibold text-slate-800 truncate" title="{{ $job->client->name ?? 'N/A' }}">{{ $job->client->name ?? 'N/A' }}</td>
                            <td class="text-slate-600 truncate" title="{{ $job->pickup_address ?? 'N/A' }}">{{ $job->pickup_address ?? 'N/A' }}</td>
                            <td class="text-slate-600 truncate">{{ number_format($job->total_distance ?? 0, 1) }} km</td>
                            <td class="font-extrabold text-slate-900 truncate">रु {{ number_format($job->base_price ?? 0) }}</td>
                            <td class="text-right">
                                <form action="{{ route('driver.jobs.accept', $job->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2 py-0.5 rounded-md bg-orange-500 hover:bg-orange-600 text-white font-semibold text-[10.5px] transition shadow-2xs">Accept</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($availableJobs, 'links'))
            <div class="card-footer bg-white border-top border-slate-100 py-2 px-3">
                {{ $availableJobs->links() }}
            </div>
        @endif
    </div>
    @endif

    <!-- Active Jobs -->
    @if(isset($activeJobs) && $activeJobs->count() > 0)
    <div class="card mb-3 border-0 shadow-2xs rounded-xl overflow-hidden">
        <div class="card-header bg-slate-50 border-bottom border-slate-100 py-2.5 px-3.5">
            <h5 class="mb-0 text-xs font-bold text-slate-900"><i class="fas fa-tasks text-blue-600 me-1.5"></i>Active Jobs</h5>
        </div>
        <div class="card-body p-0">
            <div class="overflow-hidden">
                <table class="kwdc-table-fixed text-left mb-0">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="w-[12%]">Job ID</th>
                            <th class="w-[24%]">Client</th>
                            <th class="w-[30%]">Pickup Location</th>
                            <th class="w-[12%]">Status</th>
                            <th class="w-[10%]">Amount</th>
                            <th class="w-[12%] text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($activeJobs as $job)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="font-bold text-slate-900 font-mono truncate">#{{ $job->id }}</td>
                            <td class="font-semibold text-slate-800 truncate" title="{{ $job->client->name ?? 'N/A' }}">{{ $job->client->name ?? 'N/A' }}</td>
                            <td class="text-slate-600 truncate" title="{{ $job->pickup_address ?? 'N/A' }}">{{ $job->pickup_address ?? 'N/A' }}</td>
                            <td>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $job->status === 'assigned' ? 'bg-sky-50 text-sky-700 border-sky-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                    {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                                </span>
                            </td>
                            <td class="font-extrabold text-slate-900 truncate">रु {{ number_format($job->driver_earning ?? 0) }}</td>
                            <td class="text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    @if($job->status == 'assigned')
                                        <form action="{{ route('driver.jobs.start', $job->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-0.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[10.5px] transition shadow-2xs">Start</button>
                                        </form>
                                    @endif
                                    @if($job->status == 'picked_up' || $job->status == 'on_the_way')
                                        <form action="{{ route('driver.jobs.deliver', $job->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-0.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[10.5px] transition shadow-2xs">Deliver</button>
                                        </form>
                                    @endif
                                    @if(in_array($job->status, ['assigned', 'picked_up']))
                                        <form action="{{ route('driver.jobs.cancel', $job->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-0.5 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-[10.5px] transition border border-rose-200" onclick="return confirm('Cancel this job?')">Cancel</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($activeJobs, 'links'))
            <div class="card-footer bg-white border-top border-slate-100 py-2 px-3">
                {{ $activeJobs->links() }}
            </div>
        @endif
    </div>
    @else
    <div class="card mb-3 border-0 shadow-2xs rounded-xl overflow-hidden">
        <div class="card-body text-center py-4 text-slate-400 text-xs">
            <i class="fas fa-check-circle text-emerald-500 text-2xl mb-1 block"></i>
            <h5 class="text-xs font-bold text-slate-700 mb-0.5">No Active Jobs</h5>
            <p class="text-[11px] text-slate-400 mb-0">You don't have any active jobs right now.</p>
        </div>
    </div>
    @endif

    <!-- Completed Jobs -->
    @if(isset($completedJobs) && $completedJobs->count() > 0)
    <div class="card border-0 shadow-2xs rounded-xl overflow-hidden">
        <div class="card-header bg-slate-50 border-bottom border-slate-100 py-2.5 px-3.5">
            <h5 class="mb-0 text-xs font-bold text-slate-900"><i class="fas fa-check-circle text-emerald-600 me-1.5"></i>Completed Jobs</h5>
        </div>
        <div class="card-body p-0">
            <div class="overflow-hidden">
                <table class="kwdc-table-fixed text-left mb-0">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="w-[14%]">Job ID</th>
                            <th class="w-[26%]">Client</th>
                            <th class="w-[16%]">Status</th>
                            <th class="w-[18%]">Amount</th>
                            <th class="w-[26%] text-right">Completed</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($completedJobs as $job)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="font-bold text-slate-900 font-mono truncate">#{{ $job->id }}</td>
                            <td class="font-semibold text-slate-800 truncate" title="{{ $job->client->name ?? 'N/A' }}">{{ $job->client->name ?? 'N/A' }}</td>
                            <td>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200">Delivered</span>
                            </td>
                            <td class="font-extrabold text-slate-900 truncate">रु {{ number_format($job->driver_earning ?? 0) }}</td>
                            <td class="text-slate-500 text-xs text-right truncate">{{ $job->delivered_at ? $job->delivered_at->format('M d, Y H:i') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if(method_exists($completedJobs, 'links'))
            <div class="card-footer bg-white border-top border-slate-100 py-2 px-3">
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
    
    .table th {
        font-weight: 600;
        color: #475569;
        border-top: none;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 12px;
    }
</style>
@endpush