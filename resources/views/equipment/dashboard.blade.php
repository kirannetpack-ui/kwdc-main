@extends('layouts.app')

@section('title', 'Equipment Fleet Console')
@section('header', 'Equipment Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Apple-style Hero Banner -->
    <div class="kwdc-dashboard-hero flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-2.5 mb-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-50 text-orange-700 border border-orange-200">
                    Heavy Machinery Fleet Console
                </span>
                <span class="text-xs text-gray-400 font-medium">KTM-WDC Equipment</span>
            </div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight">
                Namaste, {{ Auth::user()->name }}
            </h1>
            <p class="text-sm text-gray-500 mt-1 max-w-xl">
                Manage your heavy logistics machinery, monitor site rental deployments, update daily billing rates, and track maintenance records.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <div class="kwdc-beacon-container">
                <span class="kwdc-beacon-dot"></span>
                <span>Fleet Ready</span>
            </div>
            <div class="px-4 py-2 rounded-full bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-600">
                <i class="far fa-calendar-alt text-gray-400 mr-1.5"></i> {{ now()->format('D, M j, Y') }}
            </div>
        </div>
    </div>

    <!-- Quick Action Pills -->
    <div class="flex items-center gap-2.5 overflow-x-auto pb-1 scrollbar-none">
        <a href="{{ route('equipment.register') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-plus-circle text-orange-500"></i>
            <span>Register New Equipment</span>
        </a>
        <a href="{{ route('equipment.list') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-gear text-blue-500"></i>
            <span>Manage Machinery Fleet</span>
        </a>
        <a href="{{ route('equipment.jobs.requests') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-clipboard-list text-emerald-500"></i>
            <span>Job Booking Requests</span>
        </a>
        <a href="{{ route('equipment.jobs.earnings') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-coins text-purple-500"></i>
            <span>Revenue & Payouts</span>
        </a>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Fleet</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $myEquipment ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #eff6ff; color: #2563eb;">
                    <i class="fas fa-truck-ramp-box"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Registered Units</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Deployments</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $activeJobs ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #ecfdf5; color: #059669;">
                    <i class="fas fa-hard-hat"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>On-Site Rentals</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Completed Jobs</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $completedJobs ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #fffbeb; color: #d97706;">
                    <i class="fas fa-check-double"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Finished Rentals</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Rental Earnings</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">NPR {{ number_format((float) ($totalEarnings ?? 0)) }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #fdf2f8; color: #db2777;">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Net Machinery Payout</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>
    </div>

    <!-- Fleet Inventory Roster -->
    <div class="kwdc-surface-card space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <i class="fas fa-gear text-orange-500"></i>
                <h2 class="font-bold text-gray-900 text-base">Registered Machinery Fleet</h2>
            </div>
            <a href="{{ route('equipment.register') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">+ Register Equipment</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($equipmentList ?? [] as $item)
            <div class="kwdc-feed-item space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">{{ $item->name ?? $item->equipment_name ?? 'Machinery Asset' }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $item->type ?? $item->category ?? 'Logistics Machinery' }} &bull; {{ $item->model ?? 'Standard' }}</p>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                        {{ ($item->status ?? 'available') === 'available' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                        {{ ucfirst($item->status ?? 'available') }}
                    </span>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-gray-100 text-xs text-gray-600">
                    <span>Daily Rate: <strong>NPR {{ number_format((float) ($item->daily_rate ?? $item->price ?? 0)) }}/day</strong></span>
                    <span class="text-gray-400">ID #{{ $item->id }}</span>
                </div>
            </div>
            @empty
            <div class="col-span-3 py-8 text-center text-gray-400 text-sm">
                <p>No equipment registered yet. Click "Register New Equipment" above to add machinery to your fleet.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
