@extends('layouts.app')

@section('title', 'Warehouse - ' . $warehouse->name)
@section('header', 'Warehouse Details')

@section('content')
@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin() || ($user->is_admin ?? false) || $user->role === 'admin';
    $isOwner = $warehouse->user_id === $user->id || $warehouse->owner_id === $user->id;
    $backRoute = $isAdmin ? route('admin.warehouses.index') : route('warehouses.index');
    $status = strtolower($warehouse->status ?? 'pending');
    $statusClasses = [
        'approved' => 'bg-green-100 text-green-800 border-green-200',
        'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
        'rejected' => 'bg-red-100 text-red-800 border-red-200',
    ];
    $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
    $lat = $warehouse->latitude ?? 27.7172;
    $lng = $warehouse->longitude ?? 85.3240;
    $hasCoords = !empty($warehouse->latitude) && !empty($warehouse->longitude);
    $totalCap = (float) ($warehouse->total_capacity ?? 0);
    $allocated = (float) ($warehouse->allocated_space ?? 0);
    $occupancyPct = $totalCap > 0 ? min(100, round(($allocated / $totalCap) * 100)) : 0;
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .kwdc-map-container {
        height: 280px;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
</style>
@endpush

<div class="max-w-5xl mx-auto space-y-6">
    <!-- Top Bar Navigation & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ $backRoute }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-orange-600 transition mb-2">
                <i class="fas fa-arrow-left mr-2"></i> Back to Warehouses
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $warehouse->name }}</h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $statusClass }}">
                    {{ ucfirst($status) }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">
                <i class="fas fa-map-marker-alt text-orange-500 mr-1.5"></i>
                {{ $warehouse->address ?? 'Kathmandu Valley, Nepal' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if(!$isAdmin && !$isOwner)
                <a href="{{ route('my-requests.create') }}?warehouse_id={{ $warehouse->id }}" class="inline-flex items-center px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                    <i class="fas fa-calendar-plus mr-2"></i> Request Space
                </a>
            @endif

            @if($isOwner)
                <a href="{{ route('warehouses.edit', $warehouse->id) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                    <i class="fas fa-edit mr-2 text-gray-500"></i> Edit Warehouse
                </a>
            @endif

            @if($isAdmin && $status === 'pending')
                <form method="POST" action="{{ route('admin.approve', $warehouse->id) }}" class="inline-block">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                        <i class="fas fa-check mr-1.5"></i> Approve
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.reject', $warehouse->id) }}" class="inline-block">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                        <i class="fas fa-times mr-1.5"></i> Reject
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <i class="fas fa-check-circle text-green-600"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-bold uppercase text-gray-400 tracking-wider">Total Capacity</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">
                {{ number_format($totalCap, 0) }} <span class="text-sm font-normal text-gray-500">{{ $warehouse->type === 'building' ? 'm³' : 'm²' }}</span>
            </p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-bold uppercase text-gray-400 tracking-wider">Allocated Space</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">
                {{ number_format($allocated, 0) }} <span class="text-sm font-normal text-gray-500">{{ $warehouse->type === 'building' ? 'm³' : 'm²' }}</span>
            </p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-bold uppercase text-gray-400 tracking-wider">Occupancy Rate</p>
            <div class="mt-2">
                <span class="text-2xl font-bold text-gray-900">{{ $occupancyPct }}%</span>
                <div class="w-full bg-gray-100 rounded-full h-2 mt-2">
                    <div class="bg-orange-500 h-2 rounded-full transition-all duration-500" style="width: {{ $occupancyPct }}%"></div>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-bold uppercase text-gray-400 tracking-wider">Price per Unit</p>
            <p class="text-2xl font-bold text-orange-600 mt-2">
                NPR {{ number_format((float) ($warehouse->price_per_unit ?? $warehouse->price_per_sqft ?? 0), 2) }}
            </p>
        </div>
    </div>

    <!-- Main Details & Map -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Specs & Details -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 space-y-6">
                <h2 class="text-lg font-bold text-gray-900 flex items-center">
                    <i class="fas fa-warehouse text-orange-500 mr-2"></i> Warehouse Specifications
                </h2>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="p-3.5 bg-gray-50 rounded-xl">
                        <dt class="text-xs text-gray-500 uppercase font-semibold">Structure Type</dt>
                        <dd class="font-bold text-gray-900 mt-1">
                            {{ $warehouse->type === 'building' ? 'Industrial Building (Indoor)' : 'Open Plot / Secure Yard' }}
                        </dd>
                    </div>

                    @if($warehouse->type === 'building' && ($warehouse->length || $warehouse->width))
                        <div class="p-3.5 bg-gray-50 rounded-xl">
                            <dt class="text-xs text-gray-500 uppercase font-semibold">Dimensions (L × W × H)</dt>
                            <dd class="font-bold text-gray-900 mt-1">
                                {{ $warehouse->length }} × {{ $warehouse->width }} × {{ $warehouse->height ?? 4 }} m
                            </dd>
                        </div>
                    @endif

                    <div class="p-3.5 bg-gray-50 rounded-xl">
                        <dt class="text-xs text-gray-500 uppercase font-semibold">Usable Capacity</dt>
                        <dd class="font-bold text-gray-900 mt-1">
                            {{ number_format((float) ($warehouse->usable_capacity ?? ($totalCap * 0.9)), 0) }} {{ $warehouse->type === 'building' ? 'm³' : 'm²' }}
                        </dd>
                    </div>

                    <div class="p-3.5 bg-gray-50 rounded-xl">
                        <dt class="text-xs text-gray-500 uppercase font-semibold">Sharing Policy</dt>
                        <dd class="font-bold text-gray-900 mt-1">
                            {{ $warehouse->allow_shared ? 'Shared / Multi-tenant Allowed' : 'Exclusive Single-Tenant' }}
                        </dd>
                    </div>

                    <div class="p-3.5 bg-gray-50 rounded-xl">
                        <dt class="text-xs text-gray-500 uppercase font-semibold">Security Deposit</dt>
                        <dd class="font-bold text-gray-900 mt-1">
                            @if($warehouse->security_deposit_fixed)
                                NPR {{ number_format((float) $warehouse->security_deposit_fixed, 2) }}
                            @elseif($warehouse->security_deposit_percentage)
                                {{ $warehouse->security_deposit_percentage }}% of monthly rent
                            @else
                                Standard agreement
                            @endif
                        </dd>
                    </div>

                    <div class="p-3.5 bg-gray-50 rounded-xl">
                        <dt class="text-xs text-gray-500 uppercase font-semibold">Distance to City Hub</dt>
                        <dd class="font-bold text-gray-900 mt-1">
                            {{ $warehouse->distance_from_city ? $warehouse->distance_from_city . ' km' : 'Within metropolitan zone' }}
                        </dd>
                    </div>
                </dl>

                <!-- Facilities & Amenities Chips -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider text-xs text-gray-400 mb-3">Facilities & Amenities</h3>
                    <div class="flex flex-wrap gap-2">
                        @if($warehouse->has_cctv || ($warehouse->cctv_count ?? 0) > 0)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-blue-50 text-blue-700 text-xs font-semibold">
                                <i class="fas fa-video mr-1.5 text-blue-500"></i> CCTV Surveillance {{ ($warehouse->cctv_count ?? 0) > 0 ? '(' . $warehouse->cctv_count . ' units)' : '' }}
                            </span>
                        @endif
                        @if($warehouse->has_security_guard || ($warehouse->guards_count ?? 0) > 0)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-semibold">
                                <i class="fas fa-user-shield mr-1.5 text-emerald-500"></i> 24/7 Security Guards
                            </span>
                        @endif
                        @if($warehouse->is_motorable)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-purple-50 text-purple-700 text-xs font-semibold">
                                <i class="fas fa-road mr-1.5 text-purple-500"></i> Heavy Vehicle Motorable Access
                            </span>
                        @endif
                        @if($warehouse->cold_storage)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-cyan-50 text-cyan-700 text-xs font-semibold">
                                <i class="fas fa-snowflake mr-1.5 text-cyan-500"></i> Temperature Controlled / Cold Storage
                            </span>
                        @endif
                        @if($warehouse->loading_dock)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-amber-50 text-amber-700 text-xs font-semibold">
                                <i class="fas fa-dolly mr-1.5 text-amber-500"></i> Dedicated Loading Dock
                            </span>
                        @endif
                        @if($warehouse->has_labors)
                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl bg-gray-100 text-gray-700 text-xs font-semibold">
                                <i class="fas fa-people-carry mr-1.5 text-gray-500"></i> On-Site Handling Labor Available
                            </span>
                        @endif
                    </div>
                </div>

                @if($warehouse->description)
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 mb-2">Description & Notes</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $warehouse->description }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Location Map & Owner Info -->
        <div class="space-y-6">
            <!-- Leaflet Location Map -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h2 class="text-base font-bold text-gray-900 flex items-center">
                    <i class="fas fa-map-marked-alt text-orange-500 mr-2"></i> Location Map
                </h2>
                <div id="warehouseLocationMap" class="kwdc-map-container" style="height: 380px; min-height: 380px; width: 100%;"></div>
                <p class="text-xs text-gray-500">
                    <i class="fas fa-info-circle mr-1"></i>
                    Coordinates: {{ number_format($lat, 4) }}, {{ number_format($lng, 4) }}
                </p>
            </div>

            <!-- Property Owner / Manager Info -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-3">
                <h2 class="text-base font-bold text-gray-900 flex items-center">
                    <i class="fas fa-user-tie text-orange-500 mr-2"></i> Property Contact
                </h2>
                <dl class="text-sm space-y-2">
                    <div>
                        <dt class="text-xs text-gray-400 font-semibold uppercase">Owner</dt>
                        <dd class="font-bold text-gray-900">{{ optional($warehouse->owner ?? $warehouse->user)->name ?? 'KTM-WDC Network' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-semibold uppercase">Email</dt>
                        <dd class="text-gray-700">{{ optional($warehouse->owner ?? $warehouse->user)->email ?? 'contact@kwdc.test' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-semibold uppercase">Phone</dt>
                        <dd class="text-gray-700">{{ optional($warehouse->owner ?? $warehouse->user)->phone ?? '9800000003' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    function initWarehouseShowMap() {
        const lat = {{ $lat }};
        const lng = {{ $lng }};
        const mapEl = document.getElementById('warehouseLocationMap');
        if (!mapEl) return;
        if (typeof L === 'undefined') {
            setTimeout(initWarehouseShowMap, 100);
            return;
        }

        if (mapEl._leaflet_id) {
            mapEl._leaflet_id = null;
            mapEl.innerHTML = '';
        }

        const map = L.map(mapEl, {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView([lat, lng], 13);
        mapEl._leaflet_map = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        const marker = L.marker([lat, lng], {
            icon: L.divIcon({
                className: 'custom-warehouse-pin',
                html: '<div style="background:#f97316; width:22px; height:22px; border-radius:50%; border:3px solid white; box-shadow:0 3px 12px rgba(249,115,22,0.45); display:flex; align-items:center; justify-content:center;"><i class="fas fa-warehouse text-white text-[10px]"></i></div>',
                iconSize: [22, 22],
                iconAnchor: [11, 11]
            })
        }).addTo(map);

        marker.bindPopup('<b>{{ addslashes($warehouse->name) }}</b><br>{{ addslashes($warehouse->address ?? "Kathmandu Valley") }}').openPopup();

        const resize = () => { map.invalidateSize(); };
        setTimeout(resize, 80);
        setTimeout(resize, 250);
        setTimeout(resize, 600);
        window.addEventListener('resize', resize);
    }

    if (document.readyState !== 'loading') {
        initWarehouseShowMap();
    } else {
        document.addEventListener('DOMContentLoaded', initWarehouseShowMap);
    }
    document.addEventListener('kwdc:page-loaded', initWarehouseShowMap);
})();
</script>
@endpush
