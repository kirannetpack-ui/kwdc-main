@extends('layouts.app')

@section('title', 'Driver Dashboard')
@section('header', 'Driver Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Hero Banner -->
    <div class="kwdc-dashboard-hero flex flex-col md:flex-row md:items-center md:justify-between gap-5">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-50 text-orange-700 border border-orange-200">
                    Fleet Driver
                </span>
                <span class="text-xs text-gray-400 font-medium">{{ auth()->user()->name }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                Driver Dashboard
            </h1>
            <p class="text-xs text-gray-500 mt-1">
                Route management & deliveries.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="kwdc-beacon-container">
                <span class="kwdc-beacon-dot"></span>
                <span>On Duty</span>
            </div>
            <div class="px-3.5 py-1.5 rounded-full bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-600">
                <i class="far fa-calendar-alt text-gray-400 mr-1.5"></i> {{ now()->format('M j, Y') }}
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
        <a href="{{ route('driver.available-jobs') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-magnifying-glass-location text-orange-500"></i>
            <span>Jobs</span>
        </a>
        <a href="{{ route('driver.jobs') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-route text-blue-500"></i>
            <span>Active</span>
        </a>
        <a href="{{ route('driver.pickups') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-boxes-stacked text-emerald-500"></i>
            <span>Pickups</span>
        </a>
        <a href="{{ route('driver.earnings') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-wallet text-purple-500"></i>
            <span>Earnings</span>
        </a>
        <a href="{{ route('reminders.index') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-calendar-check text-gray-500"></i>
            <span>Reminders</span>
        </a>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Active</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $stats['active_jobs'] ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #eff6ff; color: #2563eb;">
                    <i class="fas fa-route"></i>
                </div>
            </div>
            <div class="pt-3 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>In transit</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Completed</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $stats['completed_jobs'] ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #ecfdf5; color: #059669;">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
            <div class="pt-3 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Delivered</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Earnings</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">NPR {{ number_format((float) ($stats['total_earnings'] ?? 0)) }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #fffbeb; color: #d97706;">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div class="pt-3 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Net payout</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Pickups</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $stats['active_pickups'] ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #fdf2f8; color: #db2777;">
                    <i class="fas fa-box"></i>
                </div>
            </div>
            <div class="pt-3 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Scheduled</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>
    </div>

    <!-- Active Jobs & Marketplace Grids -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Assigned Dispatches -->
        <div class="kwdc-surface-card space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-truck text-orange-500 text-sm"></i>
                    <h2 class="font-bold text-gray-900 text-sm">Assigned Dispatches</h2>
                </div>
                <a href="{{ route('driver.jobs') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">All</a>
            </div>
            <div class="space-y-2">
                @forelse($recentJobs ?? [] as $job)
                <div class="kwdc-feed-item flex items-center justify-between gap-3 cursor-pointer" data-record-url="{{ route('dispatch.show', $job->id) }}">
                    <div>
                        <p class="font-bold text-gray-900 text-xs">Order #{{ $job->tracking_id ?? $job->id }}</p>
                        <p class="text-[11px] text-gray-600 mt-0.5">
                            <span class="font-semibold text-gray-800">{{ $job->pickup_address ?? 'Origin' }}</span> &rarr; 
                            <span>{{ $job->delivery_address ?? $job->destination_address ?? 'Destination' }}</span>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200">
                            {{ ucfirst(str_replace('_', ' ', $job->status ?? 'assigned')) }}
                        </span>
                        <p class="text-xs font-bold text-emerald-600 mt-0.5">NPR {{ number_format((float) ($job->driver_earning ?? 0)) }}</p>
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400 py-4 text-center">No active assignments.</p>
                @endforelse
            </div>
        </div>

        <!-- Available Marketplace Jobs -->
        <div class="kwdc-surface-card space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-hand-holding-dollar text-emerald-500 text-sm"></i>
                    <h2 class="font-bold text-gray-900 text-sm">Marketplace</h2>
                </div>
                <a href="{{ route('driver.available-jobs') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">Browse</a>
            </div>
            <div class="space-y-2">
                @forelse($availableJobs ?? [] as $job)
                <div class="kwdc-feed-item flex items-center justify-between gap-3 cursor-pointer" data-record-url="{{ route('dispatch.show', $job->id) }}">
                    <div>
                        <p class="font-bold text-gray-900 text-xs">Dispatch #{{ $job->tracking_id ?? $job->id }}</p>
                        <p class="text-[11px] text-gray-600 mt-0.5">
                            <span class="font-semibold text-gray-800">{{ $job->pickup_address ?? 'Kathmandu' }}</span>
                            @if($job->delivery_address || $job->destination_address)
                                &bull; {{ $job->delivery_address ?? $job->destination_address }}
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-extrabold text-emerald-600">NPR {{ number_format((float) ($job->driver_earning ?? $job->base_price ?? 0)) }}</p>
                        <span class="text-[10px] text-gray-400">{{ number_format((float) ($job->total_distance ?? 0), 1) }} km</span>
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400 py-4 text-center">No open jobs.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Pickups Section -->
    @if(!empty($recentPickups) && count($recentPickups) > 0)
    <div class="kwdc-surface-card space-y-3">
        <div class="flex items-center justify-between pb-2 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <i class="fas fa-boxes-stacked text-orange-500 text-sm"></i>
                <h2 class="font-bold text-gray-900 text-sm">Pickups</h2>
            </div>
            <a href="{{ route('driver.pickups') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">All</a>
        </div>
        <div class="space-y-2">
            @foreach($recentPickups as $pickup)
            <div class="kwdc-feed-item flex items-center justify-between gap-3 cursor-pointer" data-record-url="{{ route('pickup.show', $pickup->id) }}">
                <div>
                    <p class="font-bold text-gray-900 text-xs">Pickup {{ $pickup->tracking_id ?? ('#' . $pickup->id) }}</p>
                    <p class="text-[11px] text-gray-600 mt-0.5">
                        {{ $pickup->pickup_address ?? $pickup->notes ?? 'Kathmandu' }}
                        @if($pickup->notes && $pickup->notes !== $pickup->pickup_address)
                            &bull; <span class="italic text-gray-500">{{ $pickup->notes }}</span>
                        @endif
                    </p>
                </div>
                <div class="text-right">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-700 border border-orange-200">
                        {{ ucfirst(str_replace('_', ' ', $pickup->status ?? 'pending')) }}
                    </span>
                    <p class="text-[10px] text-gray-400 mt-0.5">{{ optional($pickup->created_at)->format('M d') }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
