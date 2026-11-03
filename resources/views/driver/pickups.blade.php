@extends('layouts.app')

@section('title', 'My Pickups - Driver')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>My Pickup Jobs</h2>
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
                            <h6 class="stats-label">Total Pickups</h6>
                            <h2 class="stats-number">{{ $stats['total_pickups'] ?? 0 }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-boxes"></i>
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
                            <h6 class="stats-label">Active Pickups</h6>
                            <h2 class="stats-number">{{ $stats['active_pickups'] ?? 0 }}</h2>
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
                            <h2 class="stats-number">{{ $stats['completed_pickups'] ?? 0 }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-check-circle"></i>
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
                            <h6 class="stats-label">Earnings</h6>
                            <h2 class="stats-number">रु {{ number_format($stats['total_earnings'] ?? 0) }}</h2>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Pickups -->
    @if(isset($availablePickups) && $availablePickups->count() > 0)
    <div class="card mb-3 border-0 shadow-2xs rounded-xl overflow-hidden">
        <div class="card-header bg-slate-50 border-bottom border-slate-100 py-2.5 px-3.5">
            <h5 class="mb-0 text-xs font-bold text-slate-900"><i class="fas fa-clock text-amber-500 me-1.5"></i>Available Pickups</h5>
        </div>
        <div class="card-body p-0">
            <div class="overflow-hidden">
                <table class="kwdc-table-fixed text-left mb-0">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="w-[12%]">Pickup ID</th>
                            <th class="w-[20%]">Client</th>
                            <th class="w-[26%]">Location</th>
                            <th class="w-[16%]">Items</th>
                            <th class="w-[10%]">Distance</th>
                            <th class="w-[8%]">Price</th>
                            <th class="w-[8%] text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($availablePickups as $pickup)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="font-bold text-slate-900 font-mono truncate">#{{ $pickup->id }}</td>
                            <td class="font-semibold text-slate-800 truncate" title="{{ $pickup->client->name ?? 'N/A' }}">{{ $pickup->client->name ?? 'N/A' }}</td>
                            <td class="text-slate-600 truncate" title="{{ $pickup->pickup_address ?? 'N/A' }}">{{ $pickup->pickup_address ?? 'N/A' }}</td>
                            <td class="text-slate-600 truncate" title="{{ $pickup->items_description ?? 'Standard cargo' }}">{{ $pickup->items_description ?? 'Standard cargo' }}</td>
                            <td class="text-slate-600 truncate">{{ number_format($pickup->total_distance, 1) }} km</td>
                            <td class="font-extrabold text-slate-900 truncate">रु {{ number_format($pickup->total_price) }}</td>
                            <td class="text-right">
                                <form action="{{ route('driver.accept-pickup', $pickup->id) }}" method="POST" class="inline">
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
    </div>
    @endif

    <!-- My Active Pickups -->
    @if(isset($pickups) && $pickups->count() > 0)
    <div class="card border-0 shadow-2xs rounded-xl overflow-hidden">
        <div class="card-header bg-slate-50 border-bottom border-slate-100 py-2.5 px-3.5">
            <h5 class="mb-0 text-xs font-bold text-slate-900"><i class="fas fa-tasks text-blue-600 me-1.5"></i>My Pickups</h5>
        </div>
        <div class="card-body p-0">
            <div class="overflow-hidden">
                <table class="kwdc-table-fixed text-left mb-0">
                    <thead class="bg-slate-50 border-b border-slate-100">
                        <tr>
                            <th class="w-[12%]">Pickup ID</th>
                            <th class="w-[22%]">Client</th>
                            <th class="w-[28%]">Location</th>
                            <th class="w-[14%]">Status</th>
                            <th class="w-[12%]">Distance</th>
                            <th class="w-[12%] text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($pickups as $pickup)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="font-bold text-slate-900 font-mono truncate">#{{ $pickup->id }}</td>
                            <td class="font-semibold text-slate-800 truncate" title="{{ $pickup->client->name ?? 'N/A' }}">{{ $pickup->client->name ?? 'N/A' }}</td>
                            <td class="text-slate-600 truncate" title="{{ $pickup->pickup_address ?? 'N/A' }}">{{ $pickup->pickup_address ?? 'N/A' }}</td>
                            <td>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold border bg-sky-50 text-sky-700 border-sky-200">
                                    {{ $pickup->status_text }}
                                </span>
                            </td>
                            <td class="text-slate-600 truncate">{{ number_format($pickup->total_distance, 1) }} km</td>
                            <td class="text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    @if($pickup->status == 'assigned')
                                        <form action="{{ route('driver.start-pickup', $pickup->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-0.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[10.5px] transition shadow-2xs">Start</button>
                                        </form>
                                    @endif
                                    @if($pickup->status == 'in_progress')
                                        <form action="{{ route('driver.complete-pickup', $pickup->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-0.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[10.5px] transition shadow-2xs">Complete</button>
                                        </form>
                                    @endif
                                    @if(in_array($pickup->status, ['assigned', 'in_progress']))
                                        <form action="{{ route('driver.cancel-pickup', $pickup->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-0.5 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-[10.5px] transition border border-rose-200" onclick="return confirm('Cancel this pickup?')">Cancel</button>
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
        @if(method_exists($pickups, 'links'))
            <div class="card-footer bg-white border-top border-slate-100 py-2 px-3">
                {{ $pickups->links() }}
            </div>
        @endif
    </div>
    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-boxes text-muted" style="font-size: 48px;"></i>
            <h4 class="mt-3">No Pickups</h4>
            <p class="text-muted">You don't have any active pickups.</p>
        </div>
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