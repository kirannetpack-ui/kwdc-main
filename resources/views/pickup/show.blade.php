@extends('layouts.app')

@section('title', 'Pickup Request #' . ($pickup->tracking_id ?? $pickup->id))
@section('header', 'Pickup Request Details')

@section('content')
@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin() || ($user->is_admin ?? false) || $user->role === 'admin';
    $isDriver = $user->isDriver() || $user->role === 'driver';
    $isClient = $user->isClient() || $user->role === 'client';
    $isAssignedDriver = $pickup->driver_id === $user->id;

    $statusColors = [
        'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
        'assigned' => 'bg-blue-100 text-blue-800 border-blue-200',
        'picked_up' => 'bg-purple-100 text-purple-800 border-purple-200',
        'on_the_way' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'in_progress' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'delivered' => 'bg-green-100 text-green-800 border-green-200',
        'completed' => 'bg-green-100 text-green-800 border-green-200',
        'cancelled' => 'bg-red-100 text-red-800 border-red-200',
    ];
    $statusClass = $statusColors[$pickup->status ?? 'pending'] ?? 'bg-gray-100 text-gray-800 border-gray-200';
    $firstStop = $pickup->pickupStops->first();
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .kwdc-pickup-map {
        height: 240px;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
</style>
@endpush

<div class="max-w-5xl mx-auto space-y-6">
    <!-- Breadcrumbs & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('pickup.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-orange-600 transition mb-2">
                <i class="fas fa-arrow-left mr-2"></i> Back to Pickups
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold font-mono text-gray-900">{{ $pickup->tracking_id ?? ('#' . $pickup->id) }}</h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $statusClass }}">
                    {{ ucfirst(str_replace('_', ' ', $pickup->status ?? 'pending')) }}
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1">Created on {{ $pickup->created_at?->format('M d, Y (h:i A)') ?? 'N/A' }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Client Cancel Action -->
            @if(($isClient || $pickup->client_id === $user->id) && in_array($pickup->status, ['pending', 'assigned']))
                <form method="POST" action="{{ route('pickup.cancel', $pickup->id) }}" onsubmit="return confirm('Are you sure you want to cancel this pickup request?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-xl text-sm font-semibold transition">
                        <i class="fas fa-times mr-1.5"></i> Cancel Pickup
                    </button>
                </form>
            @endif

            <!-- Driver / Admin Status Transitions -->
            @if($isAdmin || $isAssignedDriver)
                <form method="POST" action="{{ route('pickup.update-status', $pickup->id) }}" class="inline-flex items-center gap-2">
                    @csrf
                    @if($pickup->status === 'assigned')
                        <input type="hidden" name="status" value="picked_up">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                            <i class="fas fa-box-open mr-1.5"></i> Mark Picked Up
                        </button>
                    @elseif($pickup->status === 'picked_up')
                        <input type="hidden" name="status" value="delivered">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                            <i class="fas fa-check-circle mr-1.5"></i> Mark Delivered
                        </button>
                    @endif
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Route & Stops -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 space-y-6">
                <h2 class="text-lg font-bold text-gray-900 flex items-center">
                    <i class="fas fa-route text-orange-500 mr-2"></i> Route & Destination
                </h2>

                <div class="space-y-4">
                    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-100">
                        <p class="text-xs font-bold uppercase text-emerald-800 tracking-wider">Pickup Origin</p>
                        <p class="font-bold text-gray-900 mt-1 text-base">{{ $pickup->pickup_address ?? optional($firstStop)->address ?? 'Kathmandu' }}</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-orange-50/70 border border-orange-100">
                        <p class="text-xs font-bold uppercase text-orange-800 tracking-wider">Destination / Delivery</p>
                        <p class="font-bold text-gray-900 mt-1 text-base">{{ $pickup->destination_address ?? optional($pickup->warehouse)->name ?? 'Assigned Warehouse Hub' }}</p>
                    </div>
                </div>

                <!-- Pickup Stops -->
                @if($pickup->pickupStops && $pickup->pickupStops->count() > 0)
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider text-xs text-gray-400 mb-3">
                            Intermediate Stops ({{ $pickup->pickupStops->count() }})
                        </h3>
                        <div class="space-y-3">
                            @foreach($pickup->pickupStops as $stop)
                                <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 flex items-start justify-between gap-4 text-sm">
                                    <div>
                                        <p class="font-bold text-gray-900">Stop {{ $stop->stop_number }}</p>
                                        <p class="text-gray-700 mt-1">{{ $stop->address }}</p>
                                        @if($stop->contact_name || $stop->contact_phone)
                                            <p class="text-xs text-gray-500 mt-1.5">
                                                <i class="fas fa-user text-gray-400 mr-1"></i> {{ $stop->contact_name }} &bull; {{ $stop->contact_phone }}
                                            </p>
                                        @endif
                                        @if($stop->items_description)
                                            <p class="text-xs text-gray-600 mt-1 italic">{{ $stop->items_description }}</p>
                                        @endif
                                    </div>
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-white border text-gray-700">
                                        {{ ucfirst($stop->status ?? 'pending') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Route Map -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-3">
                <h2 class="text-base font-bold text-gray-900 flex items-center">
                    <i class="fas fa-map-marked-alt text-orange-500 mr-2"></i> Route Map
                </h2>
                <div id="pickupRouteMap" class="kwdc-pickup-map" style="height: 380px; min-height: 380px; width: 100%;"></div>
            </div>
        </div>

        <!-- Summary Sidebar -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center">
                    <i class="fas fa-receipt text-orange-500 mr-2"></i> Order Summary
                </h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 font-semibold uppercase">Assigned Driver</dt>
                        <dd class="font-bold text-gray-900 mt-0.5">{{ optional($pickup->driver)->name ?? 'Awaiting assignment' }}</dd>
                        @if($pickup->driver && $pickup->driver->phone)
                            <dd class="text-xs text-gray-500">{{ $pickup->driver->phone }}</dd>
                        @endif
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-semibold uppercase">Total Distance</dt>
                        <dd class="font-bold text-gray-900 mt-0.5">{{ number_format((float) ($pickup->total_distance ?? 0), 1) }} km</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-semibold uppercase">Estimated Total</dt>
                        <dd class="text-xl font-bold text-orange-600 mt-0.5">NPR {{ number_format((float) ($pickup->total_price ?? 0), 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-semibold uppercase">Payment Status</dt>
                        <dd class="mt-0.5">
                            <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full
                                {{ ($pickup->payment_status ?? 'unpaid') === 'paid' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ucfirst($pickup->payment_status ?? 'Unpaid') }}
                            </span>
                        </dd>
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
    function initPickupShowMap() {
        const mapEl = document.getElementById('pickupRouteMap');
        if (!mapEl) return;
        if (typeof L === 'undefined') {
            setTimeout(initPickupShowMap, 100);
            return;
        }

        if (mapEl._leaflet_id) {
            mapEl._leaflet_id = null;
            mapEl.innerHTML = '';
        }

        const map = L.map(mapEl, {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView([27.7172, 85.3240], 12);
        mapEl._leaflet_map = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        L.marker([27.7172, 85.3240], {
            icon: L.divIcon({
                className: 'custom-hub-pin',
                html: '<div style="background:#f97316; width:22px; height:22px; border-radius:50%; border:3px solid white; box-shadow:0 3px 12px rgba(249,115,22,0.45); display:flex; align-items:center; justify-content:center;"><div style="width:6px; height:6px; background:white; border-radius:50%;"></div></div>',
                iconSize: [22, 22],
                iconAnchor: [11, 11]
            })
        }).addTo(map).bindPopup('<b>Kathmandu Hub</b>').openPopup();

        const resize = () => { map.invalidateSize(); };
        setTimeout(resize, 80);
        setTimeout(resize, 250);
        setTimeout(resize, 600);
        window.addEventListener('resize', resize);
    }

    if (document.readyState !== 'loading') {
        initPickupShowMap();
    } else {
        document.addEventListener('DOMContentLoaded', initPickupShowMap);
    }
    document.addEventListener('kwdc:page-loaded', initPickupShowMap);
})();
</script>
@endpush
