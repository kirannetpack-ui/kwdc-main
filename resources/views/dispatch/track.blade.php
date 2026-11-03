@extends('layouts.app')

@section('title', 'Live Track Dispatch #' . $dispatch->id)
@section('header', 'Live Shipment GPS Tracking')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .track-map-container {
        height: 420px;
        min-height: 420px;
        width: 100%;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
        overflow: hidden;
        position: relative;
        z-index: 1;
    }
    .custom-vehicle-pin {
        background: #ea580c;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 3px solid #ffffff;
        box-shadow: 0 0 15px rgba(234, 88, 12, 0.6);
        animation: pulsePin 2s infinite;
    }
    @keyframes pulsePin {
        0% { box-shadow: 0 0 0 0 rgba(234, 88, 12, 0.6); }
        70% { box-shadow: 0 0 0 12px rgba(234, 88, 12, 0); }
        100% { box-shadow: 0 0 0 0 rgba(234, 88, 12, 0); }
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Top Status Banner -->
    <div class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-5">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200">
                    Live GPS Telematics
                </span>
                <span class="text-xs text-slate-400 font-medium">Tracking ID: {{ $dispatch->tracking_id ?: ('KWDC-TRK-' . $dispatch->id) }}</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900">
                Dispatch Consignment #{{ $dispatch->id }}
            </h1>
        </div>

        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider
                @if($dispatch->status == 'delivered') bg-emerald-50 text-emerald-700 border border-emerald-200
                @elseif($dispatch->status == 'pending') bg-amber-50 text-amber-700 border border-amber-200
                @else bg-blue-50 text-blue-700 border border-blue-200 @endif">
                <span class="w-2 h-2 rounded-full @if($dispatch->status == 'delivered') bg-emerald-500 @else bg-orange-500 animate-pulse @endif"></span>
                {{ ucfirst(str_replace('_', ' ', $dispatch->status ?? 'In Transit')) }}
            </span>
            <a href="{{ route('dispatch.pdf', $dispatch->id) }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-bold text-slate-700 transition">
                <i class="fas fa-file-pdf text-orange-500"></i>
                <span>Manifest PDF</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Map Area -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fas fa-satellite-dish text-orange-500"></i> Real-Time Vehicle Position
                    </h2>
                    <span id="lastUpdated" class="text-xs text-slate-500">
                        <i class="far fa-clock text-slate-400 mr-1"></i> Live Active Stream
                    </span>
                </div>

                <div id="liveTrackMap" class="track-map-container" style="height: 380px; min-height: 380px; width: 100%;"></div>

                <div class="flex items-center justify-between text-xs text-slate-500 pt-1">
                    <span><i class="fas fa-route text-orange-500 mr-1"></i> Transit Route: Kathmandu &bull; Central Highway Corridor</span>
                    <span>Accurate GPS Coordinates Active</span>
                </div>
            </div>

            <!-- Route Origin & Destination -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Route Waypoints</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-100">
                        <p class="text-[11px] font-bold uppercase text-emerald-800 tracking-wider">Pickup Origin</p>
                        <p class="font-bold text-slate-900 mt-1">{{ $dispatch->pickup_address ?: 'Kathmandu Central Depot' }}</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-orange-50/70 border border-orange-100">
                        <p class="text-[11px] font-bold uppercase text-orange-800 tracking-wider">Destination Hub</p>
                        <p class="font-bold text-slate-900 mt-1">{{ $dispatch->delivery_address ?: 'Receiving Freight Station' }}</p>
                    </div>
                </div>

                @if($dispatch->deliveryStops && $dispatch->deliveryStops->count() > 0)
                    <div class="pt-2">
                        <h4 class="text-xs font-bold uppercase text-slate-400 mb-3">Delivery Stops ({{ $dispatch->deliveryStops->count() }})</h4>
                        <div class="space-y-2.5">
                            @foreach($dispatch->deliveryStops as $stop)
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between gap-3 text-xs">
                                <div>
                                    <strong class="text-slate-800">Stop {{ $stop->stop_order ?? $loop->iteration }}: {{ $stop->address }}</strong>
                                    <div class="text-slate-500 mt-0.5">Recipient: {{ $stop->recipient_name }} &bull; {{ $stop->recipient_phone }}</div>
                                </div>
                                <span class="px-2.5 py-1 rounded-full font-bold uppercase text-[10px] bg-white border border-slate-200 text-slate-700">
                                    {{ ucfirst($stop->status ?? 'In Transit') }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar Details -->
        <div class="space-y-6">
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Driver &amp; Vehicle Status</h3>
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-lg border border-orange-100">
                        <i class="fas fa-truck-moving"></i>
                    </div>
                    <div>
                        <div class="font-bold text-slate-900">{{ optional($dispatch->driver)->name ?? 'Assigned Logistics Driver' }}</div>
                        <div class="text-xs text-slate-500">{{ optional($dispatch->driver)->phone ?? '+977-9841000000' }}</div>
                    </div>
                </div>

                <div class="pt-2 space-y-2 text-xs border-t border-slate-100">
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400 font-medium">Vehicle Class</span>
                        <span class="font-bold text-slate-700">{{ optional($dispatch->driver)->vehicle_type ?? 'Heavy Commercial Hauler' }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400 font-medium">Total Distance</span>
                        <span class="font-bold text-slate-700">{{ number_format((float) ($dispatch->total_distance ?? 18.5), 1) }} km</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-slate-400 font-medium">Payment Settlement</span>
                        <span class="font-bold uppercase text-emerald-600">{{ $dispatch->payment_status ?? 'Settled' }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-sm space-y-3">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400">Security &amp; Support</h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    All fleet movements are secured with 24/7 Kathmandu Valley control center monitoring. In case of route deviation or delays, contact dispatch ops.
                </p>
                <div class="pt-1">
                    <a href="tel:+97715912400" class="inline-flex items-center gap-2 text-xs font-bold text-orange-600 hover:text-orange-700">
                        <i class="fas fa-headset"></i> Call Control Hub (+977-1-5912400)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    function initDispatchTrackMap() {
        const mapEl = document.getElementById('liveTrackMap');
        if (!mapEl) return;
        if (typeof L === 'undefined') {
            setTimeout(initDispatchTrackMap, 100);
            return;
        }

        if (mapEl._leaflet_id) {
            mapEl._leaflet_id = null;
            mapEl.innerHTML = '';
        }

        const lat = {{ $dispatch->current_latitude ?? 27.7172 }};
        const lng = {{ $dispatch->current_longitude ?? 85.3240 }};

        const map = L.map(mapEl, {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView([lat, lng], 13);
        mapEl._leaflet_map = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        const vehicleIcon = L.divIcon({
            className: 'custom-vehicle-pin',
            html: '<div style="background:#ea580c; width:22px; height:22px; border-radius:50%; border:3px solid white; box-shadow:0 3px 12px rgba(234,88,12,0.45); display:flex; align-items:center; justify-content:center;"><i class="fas fa-truck text-white text-[10px]"></i></div>',
            iconSize: [22, 22],
            iconAnchor: [11, 11]
        });

        L.marker([lat, lng], { icon: vehicleIcon }).addTo(map)
            .bindPopup('<b>Driver Live Position</b><br>Active Route Tracking')
            .openPopup();

        const resize = () => { map.invalidateSize(); };
        setTimeout(resize, 80);
        setTimeout(resize, 250);
        setTimeout(resize, 600);
        window.addEventListener('resize', resize);
    }

    if (document.readyState !== 'loading') {
        initDispatchTrackMap();
    } else {
        document.addEventListener('DOMContentLoaded', initDispatchTrackMap);
    }
    document.addEventListener('kwdc:page-loaded', initDispatchTrackMap);
})();
</script>
@endpush
