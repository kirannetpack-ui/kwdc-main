@extends('layouts.app')

@section('title', 'Warehouse Details')
@section('header', 'Warehouse Details')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-gray-800">
                <i class="fas fa-warehouse text-orange-500 me-2"></i>{{ $warehouse->name }}
            </h1>
            <p class="text-muted small">View and manage your property details</p>
        </div>
        <div>
            <a href="{{ route('property.approved') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Properties
            </a>
            <a href="{{ route('warehouses.pdf', $warehouse->id) }}" class="btn btn-primary me-2" style="background:#ea580c; border-color:#ea580c; color:#fff;">
                <i class="fas fa-file-pdf me-2"></i>Certificate PDF
            </a>
            <a href="{{ route('property.warehouses.edit', $warehouse->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Details -->
        <div class="col-lg-8">
            <!-- Status Badge -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-{{ $warehouse->status == 'approved' ? 'success' : ($warehouse->status == 'pending' ? 'warning' : 'danger') }} p-2">
                                <i class="fas fa-{{ $warehouse->status == 'approved' ? 'check-circle' : ($warehouse->status == 'pending' ? 'clock' : 'times-circle') }} me-1"></i>
                                {{ ucfirst($warehouse->status) }}
                            </span>
                            @if($warehouse->cold_storage)
                                <span class="badge bg-info ms-2"><i class="fas fa-snowflake me-1"></i>Cold Storage</span>
                            @endif
                        </div>
                        <small class="text-muted">Registered: {{ $warehouse->created_at->format('F d, Y') }}</small>
                    </div>
                </div>
            </div>

            <!-- Basic Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle text-blue-500 me-2"></i>Basic Information
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Warehouse Name:</strong> {{ $warehouse->name }}</p>
                            <p><strong>Location:</strong> {{ $warehouse->location }}</p>
                            <p><strong>Address:</strong> {{ $warehouse->address ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Contact Number:</strong> {{ $warehouse->contact_number ?? 'N/A' }}</p>
                            <p><strong>Area:</strong> {{ number_format($warehouse->area_sqft ?? 0) }} sq ft ({{ number_format($warehouse->area_sqm ?? 0, 2) }} sq m)</p>
                            <p><strong>Price:</strong> रू {{ number_format($warehouse->price_per_sqft ?? 0, 2) }} per sq ft</p>
                        </div>
                    </div>
                    @if($warehouse->description)
                        <div class="mt-3">
                            <strong>Description:</strong>
                            <p class="text-muted">{{ $warehouse->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Facilities -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list-check text-green-500 me-2"></i>Facilities
                </div>
                <div class="card-body">
                    @php
                        $facilities = is_string($warehouse->facilities) ? json_decode($warehouse->facilities, true) : $warehouse->facilities;
                    @endphp
                    @if($facilities && count($facilities) > 0)
                        <div class="d-flex flex-wrap">
                            @foreach($facilities as $facility)
                                <span class="badge bg-light text-dark border p-2 me-2 mb-2">
                                    <i class="fas fa-check-circle text-success me-1"></i>{{ $facility }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No facilities listed</p>
                    @endif
                </div>
            </div>

            <!-- Security Features -->
            @if($warehouse->cctv_count > 0 || $warehouse->guards_count > 0 || $warehouse->fire_extinguishers > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-shield-alt text-indigo-500 me-2"></i>Security Features
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($warehouse->cctv_count > 0)
                            <div class="col-md-4">
                                <p><i class="fas fa-video text-primary me-2"></i>CCTV Cameras: <strong>{{ $warehouse->cctv_count }}</strong></p>
                            </div>
                        @endif
                        @if($warehouse->guards_count > 0)
                            <div class="col-md-4">
                                <p><i class="fas fa-user-shield text-success me-2"></i>Security Guards: <strong>{{ $warehouse->guards_count }}</strong></p>
                            </div>
                        @endif
                        @if($warehouse->fire_extinguishers > 0)
                            <div class="col-md-4">
                                <p><i class="fas fa-fire-extinguisher text-danger me-2"></i>Fire Extinguishers: <strong>{{ $warehouse->fire_extinguishers }}</strong></p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Cold Storage -->
            @if($warehouse->cold_storage)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-snowflake text-blue-500 me-2"></i>Cold Storage Details
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <p><strong>Temperature Range:</strong> {{ $warehouse->temperature_min ?? 'N/A' }}°C - {{ $warehouse->temperature_max ?? 'N/A' }}°C</p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>Humidity Control:</strong> {{ $warehouse->humidity_control ? '✅ Available' : '❌ Not Available' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Nearby Facilities -->
            @if($warehouse->nearby_police || $warehouse->nearby_fire || $warehouse->nearby_hospital || $warehouse->nearby_bank || $warehouse->nearby_fuel || $warehouse->nearby_market)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-hospital text-purple-500 me-2"></i>Nearby Facilities
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($warehouse->nearby_police)
                            <div class="col-md-4"><i class="fas fa-shield-alt text-primary me-2"></i>Police: {{ $warehouse->nearby_police }}</div>
                        @endif
                        @if($warehouse->nearby_fire)
                            <div class="col-md-4"><i class="fas fa-fire text-danger me-2"></i>Fire: {{ $warehouse->nearby_fire }}</div>
                        @endif
                        @if($warehouse->nearby_hospital)
                            <div class="col-md-4"><i class="fas fa-hospital text-success me-2"></i>Hospital: {{ $warehouse->nearby_hospital }}</div>
                        @endif
                        @if($warehouse->nearby_bank)
                            <div class="col-md-4"><i class="fas fa-university text-warning me-2"></i>Bank: {{ $warehouse->nearby_bank }}</div>
                        @endif
                        @if($warehouse->nearby_fuel)
                            <div class="col-md-4"><i class="fas fa-gas-pump text-info me-2"></i>Fuel: {{ $warehouse->nearby_fuel }}</div>
                        @endif
                        @if($warehouse->nearby_market)
                            <div class="col-md-4"><i class="fas fa-shopping-cart text-success me-2"></i>Market: {{ $warehouse->nearby_market }}</div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Map -->
            @if($warehouse->latitude && $warehouse->longitude)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-map-marker-alt text-red-500 me-2"></i>Location Map
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 250px; border-radius: 0 0 12px 12px;"></div>
                </div>
            </div>
            @endif

            <!-- Photos -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-camera text-purple-500 me-2"></i>Property Photos
                </div>
                <div class="card-body">
                    @if($warehouse->front_image)
                        <div class="mb-2">
                            <strong>Front View</strong>
                            <img src="{{ asset('storage/' . $warehouse->front_image) }}" class="img-fluid rounded mt-1" alt="Front View">
                        </div>
                    @endif
                    @if($warehouse->interior_image)
                        <div class="mb-2">
                            <strong>Interior View</strong>
                            <img src="{{ asset('storage/' . $warehouse->interior_image) }}" class="img-fluid rounded mt-1" alt="Interior View">
                        </div>
                    @endif
                    @if($warehouse->exterior_image)
                        <div class="mb-2">
                            <strong>Exterior View</strong>
                            <img src="{{ asset('storage/' . $warehouse->exterior_image) }}" class="img-fluid rounded mt-1" alt="Exterior View">
                        </div>
                    @endif
                    @if(!$warehouse->front_image && !$warehouse->interior_image && !$warehouse->exterior_image)
                        <p class="text-muted text-center">No photos uploaded</p>
                    @endif
                </div>
            </div>

            <!-- Documents -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-pdf text-red-500 me-2"></i>Documents
                </div>
                <div class="card-body">
                    @if($warehouse->ownership_document)
                        <p><i class="fas fa-check-circle text-success me-2"></i>Ownership Document <a href="{{ route('documents.private.show', ['path' => $warehouse->ownership_document]) }}" target="_blank" class="btn btn-sm btn-outline-primary float-end">View</a></p>
                    @endif
                    @if($warehouse->tax_document)
                        <p><i class="fas fa-check-circle text-success me-2"></i>Tax Document <a href="{{ route('documents.private.show', ['path' => $warehouse->tax_document]) }}" target="_blank" class="btn btn-sm btn-outline-primary float-end">View</a></p>
                    @endif
                    @if($warehouse->fire_safety_document)
                        <p><i class="fas fa-check-circle text-success me-2"></i>Fire Safety Document <a href="{{ route('documents.private.show', ['path' => $warehouse->fire_safety_document]) }}" target="_blank" class="btn btn-sm btn-outline-primary float-end">View</a></p>
                    @endif
                    @if($warehouse->building_approval_document)
                        <p><i class="fas fa-check-circle text-success me-2"></i>Building Approval <a href="{{ route('documents.private.show', ['path' => $warehouse->building_approval_document]) }}" target="_blank" class="btn btn-sm btn-outline-primary float-end">View</a></p>
                    @endif
                    @if(!$warehouse->ownership_document && !$warehouse->tax_document && !$warehouse->fire_safety_document && !$warehouse->building_approval_document)
                        <p class="text-muted text-center">No documents uploaded</p>
                    @endif
                </div>
            </div>

            <!-- Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar text-orange-500 me-2"></i>Statistics
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Views:</span>
                        <strong>{{ $warehouse->views ?? 0 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Requests:</span>
                        <strong>{{ isset($requests) ? $requests->count() : 0 }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Last Updated:</span>
                        <strong>{{ $warehouse->updated_at->diffForHumans() }}</strong>
                    </div>
                </div>
            </div>

            <!-- Delete Button -->
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('property.warehouses.destroy', $warehouse->id) }}" 
                          method="POST" onsubmit="return confirm('Are you sure you want to delete this property? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash me-2"></i>Delete Property
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@if($warehouse->latitude && $warehouse->longitude)
<script>
(function() {
    function initPropertyWarehouseMap() {
        const mapEl = document.getElementById('map');
        if (!mapEl) return;
        if (typeof L === 'undefined') {
            setTimeout(initPropertyWarehouseMap, 100);
            return;
        }

        if (mapEl._leaflet_id) {
            mapEl._leaflet_id = null;
            mapEl.innerHTML = '';
        }

        const map = L.map(mapEl, {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView([{{ $warehouse->latitude }}, {{ $warehouse->longitude }}], 15);
        mapEl._leaflet_map = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        L.marker([{{ $warehouse->latitude }}, {{ $warehouse->longitude }}])
            .addTo(map)
            .bindPopup('<strong>{{ addslashes($warehouse->name) }}</strong><br>{{ addslashes($warehouse->address ?? $warehouse->location ?? "") }}')
            .openPopup();

        const resize = () => { map.invalidateSize(); };
        setTimeout(resize, 80);
        setTimeout(resize, 250);
        setTimeout(resize, 600);
        window.addEventListener('resize', resize);
    }

    if (document.readyState !== 'loading') {
        initPropertyWarehouseMap();
    } else {
        document.addEventListener('DOMContentLoaded', initPropertyWarehouseMap);
    }
    document.addEventListener('kwdc:page-loaded', initPropertyWarehouseMap);
})();
</script>
@endif
@endpush
@endsection
