@extends('layouts.app')

@section('title', 'Dispatch Order #' . ($dispatch->dispatch_number ?? $dispatch->id))
@section('header', 'Dispatch Order')

@section('content')
@php
    $user = auth()->user();
    $isAdmin = $user->isAdmin() || ($user->is_admin ?? false) || $user->role === 'admin';
    $isAssignedDriver = $dispatch->driver_id === $user->id;
    $status = strtolower($dispatch->status ?? 'pending');

    $statusColors = [
        'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
        'assigned' => 'bg-blue-100 text-blue-800 border-blue-200',
        'picked_up' => 'bg-purple-100 text-purple-800 border-purple-200',
        'on_the_way' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'in_progress' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'delivered' => 'bg-green-100 text-green-800 border-green-200',
        'cancelled' => 'bg-red-100 text-red-800 border-red-200',
    ];
    $statusClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .kwdc-live-map {
        height: 360px;
        border-radius: 20px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }
</style>
@endpush

<div class="max-w-5xl mx-auto space-y-6">
    <!-- Top Bar Navigation & Status -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('dispatch.index') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-orange-600 transition mb-2">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dispatches
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl sm:text-3xl font-bold font-mono text-gray-900">
                    {{ $dispatch->dispatch_number ?? ('TRK-' . str_pad($dispatch->id, 5, '0', STR_PAD_LEFT)) }}
                </h1>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider border {{ $statusClass }}">
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                </span>
                @if($dispatch->driver_id && $dispatch->current_latitude && $dispatch->current_longitude)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-2 h-2 mr-1.5 bg-emerald-500 rounded-full animate-ping"></span>
                        Live GPS Active
                    </span>
                @endif
            </div>
            <p class="text-xs text-gray-500 mt-1">Created on {{ $dispatch->created_at?->format('M d, Y (h:i A)') ?? 'N/A' }}</p>
        </div>

        <!-- Driver / Admin Action Controls -->
        <div class="flex flex-wrap items-center gap-2">
            @if($isAdmin || $isAssignedDriver)
                <form method="POST" action="{{ route('dispatch.update-status', $dispatch->id) }}" class="inline-flex items-center gap-2">
                    @csrf
                    @if($status === 'assigned')
                        <input type="hidden" name="status" value="picked_up">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                            <i class="fas fa-box-open mr-1.5"></i> Mark Picked Up
                        </button>
                    @elseif($status === 'picked_up')
                        <input type="hidden" name="status" value="on_the_way">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-sm transition">
                            <i class="fas fa-truck-moving mr-1.5"></i> Start Delivery Trip
                        </button>
                    @elseif($status === 'on_the_way')
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
        <!-- Route & Delivery Stops -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 space-y-6">
                <h2 class="text-lg font-bold text-gray-900 flex items-center">
                    <i class="fas fa-route text-orange-500 mr-2"></i> Route Details
                </h2>

                <div class="space-y-4">
                    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-100">
                        <p class="text-xs font-bold uppercase text-emerald-800 tracking-wider">Pickup Origin</p>
                        <p class="font-bold text-gray-900 mt-1 text-base">{{ $dispatch->pickup_address ?? 'Kathmandu Hub' }}</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-orange-50/70 border border-orange-100">
                        <p class="text-xs font-bold uppercase text-orange-800 tracking-wider">Destination / Delivery Address</p>
                        <p class="font-bold text-gray-900 mt-1 text-base">{{ $dispatch->delivery_address ?? 'Destination Hub' }}</p>
                    </div>
                </div>

                @if($dispatch->deliveryStops && $dispatch->deliveryStops->count() > 0)
                    <div>
                        <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider text-xs text-gray-400 mb-3">
                            Delivery Stops ({{ $dispatch->deliveryStops->count() }})
                        </h3>
                        <div class="space-y-3">
                            @foreach($dispatch->deliveryStops as $stop)
                                <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-sm">
                                    <div>
                                        <p class="font-bold text-gray-900">Stop {{ $stop->stop_order ?? $loop->iteration }}: {{ $stop->address }}</p>
                                        <p class="text-xs text-gray-600 mt-1">
                                            Recipient: <span class="font-medium">{{ $stop->recipient_name }}</span> ({{ $stop->recipient_phone }}) &bull; Boxes: <span class="font-medium">{{ $stop->boxes_count ?? 1 }}</span>
                                        </p>
                                    </div>
                                    @if($stop->invoice_document)
                                        <a href="{{ route('documents.private.show', ['path' => $stop->invoice_document]) }}" target="_blank" class="text-orange-600 hover:text-orange-800 text-xs font-semibold">
                                            <i class="fas fa-file-invoice mr-1"></i> Invoice &rarr;
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Live GPS / Route Map -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-gray-900 flex items-center">
                        <i class="fas fa-map-marked-alt text-orange-500 mr-2"></i> Live Location Map
                    </h2>
                    <div id="etaDisplay" class="text-xs font-semibold text-gray-600 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">
                        <i class="fas fa-clock text-gray-400 mr-1"></i>
                        <span id="etaText">Estimated travel ready</span>
                    </div>
                </div>

                <div id="liveMap" class="kwdc-live-map" style="height: 380px; min-height: 380px; width: 100%;"></div>
                <p class="text-xs text-gray-500">Live GPS tracking and route position updates in real-time.</p>
            </div>
        </div>

        <!-- Sidebar Summary -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center">
                    <i class="fas fa-file-invoice-dollar text-orange-500 mr-2"></i> Order Summary
                </h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 font-semibold uppercase">Assigned Driver</dt>
                        <dd class="font-bold text-gray-900 mt-0.5">{{ optional($dispatch->driver)->name ?? 'Awaiting Driver Assignment' }}</dd>
                        @if($dispatch->driver && $dispatch->driver->phone)
                            <dd class="text-xs text-gray-500">{{ $dispatch->driver->phone }}</dd>
                        @endif
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-semibold uppercase">Total Distance</dt>
                        <dd class="font-bold text-gray-900 mt-0.5">{{ number_format((float) ($dispatch->total_distance ?? 0), 1) }} km</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-semibold uppercase">Estimated Total</dt>
                        <dd class="text-xl font-bold text-orange-600 mt-0.5">NPR {{ number_format((float) ($dispatch->total_price ?? 0), 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 font-semibold uppercase">Payment Status</dt>
                        <dd class="mt-0.5">
                            <span class="inline-flex px-2.5 py-0.5 text-xs font-semibold rounded-full
                                {{ ($dispatch->payment_status ?? 'unpaid') === 'paid' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ucfirst($dispatch->payment_status ?? 'Unpaid') }}
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
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>
<script>
(function() {
    function initDispatchShowMap() {
        const mapEl = document.getElementById('liveMap');
        if (!mapEl) return;
        if (typeof L === 'undefined') {
            setTimeout(initDispatchShowMap, 100);
            return;
        }

        if (mapEl._leaflet_id) {
            mapEl._leaflet_id = null;
            mapEl.innerHTML = '';
        }

        const initialLat = {{ $dispatch->current_latitude ?? 27.7172 }};
        const initialLng = {{ $dispatch->current_longitude ?? 85.3240 }};

        const map = L.map(mapEl, {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView([initialLat, initialLng], 13);
        mapEl._leaflet_map = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        const driverMarker = L.marker([initialLat, initialLng], {
            icon: L.divIcon({
                className: 'custom-driver-pin',
                html: '<div style="background:#ea580c; width:22px; height:22px; border-radius:50%; border:3px solid white; box-shadow:0 3px 12px rgba(234,88,12,0.45); display:flex; align-items:center; justify-content:center;"><div style="width:6px; height:6px; background:white; border-radius:50%;"></div></div>',
                iconSize: [22, 22],
                iconAnchor: [11, 11]
            })
        }).addTo(map).bindPopup('<b>Driver Location</b>');

        const resize = () => { map.invalidateSize(); };
        setTimeout(resize, 80);
        setTimeout(resize, 250);
        setTimeout(resize, 600);
        window.addEventListener('resize', resize);

        @if($dispatch->driver_id)
        if (typeof Echo !== 'undefined' && typeof Pusher !== 'undefined') {
            window.Pusher = Pusher;
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: "{{ config('broadcasting.connections.reverb.key') }}",
                wsHost: "{{ config('broadcasting.connections.reverb.options.host') }}",
                wsPort: {{ (int) config('broadcasting.connections.reverb.options.port', 8080) }},
                forceTLS: false,
                enabledTransports: ['ws', 'wss'],
            });

            window.Echo.channel('dispatch.{{ $dispatch->id }}')
                .listen('.location.updated', (e) => {
                    if (e.latitude && e.longitude) {
                        driverMarker.setLatLng([e.latitude, e.longitude]);
                        map.panTo([e.latitude, e.longitude]);
                    }
                });
        }
        @endif
    }

    if (document.readyState !== 'loading') {
        initDispatchShowMap();
    } else {
        document.addEventListener('DOMContentLoaded', initDispatchShowMap);
    }
    document.addEventListener('kwdc:page-loaded', initDispatchShowMap);
})();
</script>
@endpush
