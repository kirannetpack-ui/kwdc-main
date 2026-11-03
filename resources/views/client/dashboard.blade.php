@extends('layouts.app')

@section('title', 'Client Command Center')
@section('header', 'Client Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Apple-style Hero Banner -->
    <div class="kwdc-dashboard-hero flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-2.5 mb-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-50 text-orange-700 border border-orange-200">
                    Enterprise Client Portal
                </span>
                <span class="text-xs text-gray-400 font-medium">KTM-WDC Logistics</span>
            </div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight">
                Namaste, {{ Auth::user()->name }}
            </h1>
            <p class="text-sm text-gray-500 mt-1 max-w-xl">
                Real-time visibility into your supply chain, active transit orders, inventory movements, and warehouse leasing contracts.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <div class="kwdc-beacon-container">
                <span class="kwdc-beacon-dot"></span>
                <span>Live & Connected</span>
            </div>
            <div class="px-4 py-2 rounded-full bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-600">
                <i class="far fa-calendar-alt text-gray-400 mr-1.5"></i> {{ now()->format('D, M j, Y') }}
            </div>
        </div>
    </div>

    <!-- Quick Action Pills -->
    <div class="flex items-center gap-2.5 overflow-x-auto pb-1 scrollbar-none">
        <a href="{{ route('my-requests.create') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-plus-circle text-blue-500"></i>
            <span>Request Warehouse Space</span>
        </a>
        <a href="{{ route('dispatch.direct-create') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-truck text-emerald-500"></i>
            <span>Create Dispatch Order</span>
        </a>
        <a href="{{ route('pickup.direct-create') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-box-open text-orange-500"></i>
            <span>Schedule Pickup</span>
        </a>
        <a href="{{ route('invoices.client-index') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-receipt text-purple-500"></i>
            <span>My Invoices</span>
        </a>
        <a href="{{ route('client.reports') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-chart-pie text-gray-500"></i>
            <span>Logistics Reports</span>
        </a>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Leases</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $stats['total_requests'] ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #eff6ff; color: #2563eb;">
                    <i class="fas fa-warehouse"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Storage Contracts</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Requests</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $stats['active_requests'] ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #fffbeb; color: #d97706;">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Pending Approval</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Dispatches</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $stats['total_orders'] ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #ecfdf5; color: #059669;">
                    <i class="fas fa-truck-moving"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Shipment Orders</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Spent</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">NPR {{ number_format((float) ($stats['total_spent'] ?? 0)) }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #fdf2f8; color: #db2777;">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Settled Logistics</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>
    </div>

    <!-- Active Orders & Space Requests Grids -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- In-Transit Dispatches -->
        <div class="kwdc-surface-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-truck text-orange-500"></i>
                    <h2 class="font-bold text-gray-900 text-base">Active Shipment Orders</h2>
                </div>
                <a href="{{ route('dispatch.index') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">View All</a>
            </div>
            <div class="space-y-3">
                @forelse($activeOrders ?? [] as $order)
                <div class="kwdc-feed-item flex items-center justify-between gap-4 cursor-pointer" data-record-url="{{ route('dispatch.show', $order->id) }}">
                    <div>
                        <p class="font-bold text-gray-900 text-sm">Order #{{ $order->tracking_id ?? $order->id }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            To: {{ Str::limit($order->destination_address ?? 'Kathmandu Hub', 32) }} &bull; Driver: {{ optional($order->driver)->name ?? 'Assigned soon' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                            {{ ucfirst(str_replace('_', ' ', $order->status ?? 'in_progress')) }}
                        </span>
                        <a href="{{ route('tracking.shipment', $order->id) }}" class="p-2 rounded-xl bg-orange-50 text-orange-600 hover:bg-orange-100 transition text-xs font-bold" title="Live Tracking">
                            <i class="fas fa-location-crosshairs"></i>
                        </a>
                    </div>
                </div>
                @empty
                <p class="text-xs text-gray-400 py-6 text-center">No active shipments in transit right now.</p>
                @endforelse
            </div>
        </div>

        <!-- Space Requests -->
        <div class="kwdc-surface-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-warehouse text-blue-500"></i>
                    <h2 class="font-bold text-gray-900 text-base">Storage Space Leases</h2>
                </div>
                <a href="{{ route('my-requests.index') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">All Leases</a>
            </div>
            <div class="space-y-3">
                @forelse($recentRequests ?? [] as $req)
                <div class="kwdc-feed-item flex items-center justify-between gap-4 cursor-pointer" data-record-url="{{ route('warehouse-requests.show', $req->id) }}">
                    <div>
                        <p class="font-bold text-gray-900 text-sm">{{ optional($req->warehouse)->name ?? ('Request #' . $req->id) }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Space: {{ $req->space_sqft ?? 0 }} sq.ft &bull; Requested on {{ optional($req->created_at)->format('M d, Y') }}</p>
                    </div>
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider
                        {{ ($req->status ?? 'pending') === 'approved' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        {{ ucfirst($req->status ?? 'pending') }}
                    </span>
                </div>
                @empty
                <p class="text-xs text-gray-400 py-6 text-center">No warehouse requests yet. Book storage space to get started.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
