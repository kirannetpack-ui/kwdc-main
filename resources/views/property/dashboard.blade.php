@extends('layouts.app')

@section('title', 'Property Partner Console')
@section('header', 'Property Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Apple-style Hero Banner -->
    <div class="kwdc-dashboard-hero flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-2.5 mb-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-50 text-orange-700 border border-orange-200">
                    Warehouse Property Partner
                </span>
                <span class="text-xs text-gray-400 font-medium">KTM-WDC Facilities</span>
            </div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight">
                Namaste, {{ Auth::user()->name }}
            </h1>
            <p class="text-sm text-gray-500 mt-1 max-w-xl">
                Manage commercial logistics storage facilities, review incoming enterprise tenant requests, and monitor monthly rental yields.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <div class="kwdc-beacon-container">
                <span class="kwdc-beacon-dot"></span>
                <span>Properties Active</span>
            </div>
            <div class="px-4 py-2 rounded-full bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-600">
                <i class="far fa-calendar-alt text-gray-400 mr-1.5"></i> {{ now()->format('D, M j, Y') }}
            </div>
        </div>
    </div>

    <!-- Quick Action Pills -->
    <div class="flex items-center gap-2.5 overflow-x-auto pb-1 scrollbar-none">
        <a href="{{ route('warehouses.create') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-plus-circle text-orange-500"></i>
            <span>Register New Warehouse</span>
        </a>
        <a href="{{ route('property.requests.index') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-clipboard-list text-blue-500"></i>
            <span>Client Space Requests</span>
        </a>
        <a href="{{ route('property.analytics') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-chart-line text-emerald-500"></i>
            <span>Facility Analytics</span>
        </a>
        <a href="{{ route('property.approved') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-building text-purple-500"></i>
            <span>Approved Facilities</span>
        </a>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Properties</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $myWarehouses ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #eff6ff; color: #2563eb;">
                    <i class="fas fa-building"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Registered Hubs</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Approved Facilities</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $approvedWarehouses ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #ecfdf5; color: #059669;">
                    <i class="fas fa-circle-check"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Ready for Tenants</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending Review</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $pendingWarehouses ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #fffbeb; color: #d97706;">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>In Verification</span>
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
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Accumulated Revenue</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>
    </div>

    <!-- Warehouse Facilities Roster -->
    <div class="kwdc-surface-card space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <i class="fas fa-warehouse text-orange-500"></i>
                <h2 class="font-bold text-gray-900 text-base">My Warehouse Portfolio</h2>
            </div>
            <a href="{{ route('warehouses.create') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">+ Add Property</a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($recentWarehouses ?? [] as $warehouse)
            <div class="kwdc-feed-item space-y-3 cursor-pointer" data-record-url="{{ route('warehouses.show', $warehouse->id) }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm">{{ $warehouse->name }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            <i class="fas fa-location-dot text-orange-500 mr-1"></i>{{ $warehouse->location ?? $warehouse->address ?? 'Kathmandu' }}
                        </p>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                        {{ $warehouse->status === 'approved' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        {{ ucfirst($warehouse->status ?? 'Pending') }}
                    </span>
                </div>
                <div class="flex items-center justify-between pt-2 border-t border-gray-100 text-xs text-gray-600">
                    <span>Rate: <strong>NPR {{ number_format((float) ($warehouse->price_per_unit ?? 0)) }}/sq ft</strong></span>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('warehouses.edit', $warehouse->id) }}" class="text-blue-600 hover:text-blue-800 font-bold" onclick="event.stopPropagation();">
                            Edit
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-3 py-8 text-center text-gray-400 text-sm">
                <p>No warehouses registered yet. Click "Register New Warehouse" above to add your facility.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
