@extends('layouts.app')

@section('title', 'Add New Warehouse')
@section('header', 'Add New Warehouse')

@push('styles')
<style>
    .map-container {
        height: 400px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
    }
    .section-divider {
        border-top: 2px dashed #e5e7eb;
        margin: 30px 0;
    }
    .photo-preview {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px dashed #d1d5db;
        background: #f9fafb;
        transition: all 0.3s ease;
    }
    .upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f9fafb;
        min-height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .upload-area:hover {
        border-color: #f59e0b;
        background: #fffbeb;
    }
    .upload-area.dragover {
        border-color: #f59e0b;
        background: #fffbeb;
    }
    .facility-tag {
        display: inline-block;
        padding: 6px 14px;
        background: #f3f4f6;
        border-radius: 20px;
        margin: 4px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
        user-select: none;
    }
    .facility-tag.selected {
        background: #f59e0b;
        color: white;
        border-color: #d97706;
    }
    .facility-tag:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
    }
    .cctv-url-input {
        margin-top: 8px;
        padding: 8px;
        background: #f9fafb;
        border-radius: 6px;
    }
    .form-group {
        margin-bottom: 16px;
    }
    .form-group label {
        font-weight: 500;
        font-size: 14px;
        color: #374151;
        margin-bottom: 6px;
        display: block;
    }
    .form-group .required {
        color: #ef4444;
    }
    .form-control:focus {
        border-color: #f59e0b;
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
    }
    .cold-storage-fields {
        background: #f0fdf4;
        border-radius: 8px;
        padding: 16px;
        margin-top: 12px;
        border: 1px solid #bbf7d0;
    }
    .help-text {
        font-size: 12px;
        color: #6b7280;
        margin-top: 4px;
    }
    .sticky-sidebar {
        position: sticky;
        top: 20px;
    }
    @media (max-width: 768px) {
        .sticky-sidebar {
            position: static;
        }
    }
    .preview-image {
        max-height: 200px;
        object-fit: cover;
        border-radius: 8px;
        width: 100%;
    }
    .file-input-wrapper {
        position: relative;
        overflow: hidden;
    }
    .file-input-wrapper input[type="file"] {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-gray-800">
                <i class="fas fa-warehouse text-orange-500 me-2"></i>Add New Warehouse
            </h1>
            <p class="text-muted small">Register a new warehouse with complete details, photos, and documents</p>
        </div>
        <a href="{{ route('admin.all-warehouses') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Warehouses
        </a>
    </div>

    <form action="{{ route('admin.warehouses.store') }}" method="POST" enctype="multipart/form-data" id="warehouseForm">
        @csrf

        <div class="row g-4">
            <!-- Main Form - 8 columns -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-info-circle text-blue-500 me-2"></i>
                        Basic Information
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Warehouse Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" 
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="Enter warehouse name" 
                                       value="{{ old('name') }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Location Address <span class="text-danger">*</span></label>
                                <input type="text" name="location" id="location" 
                                       class="form-control @error('location') is-invalid @enderror"
                                       placeholder="Enter full address" 
                                       value="{{ old('location') }}" required>
                                @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Property Owner <span class="text-danger">*</span></label>
                                <select name="owner_id" id="owner_id" 
                                        class="form-select @error('owner_id') is-invalid @enderror" required>
                                    <option value="">Select Property Owner</option>
                                    @foreach($propertyOwners as $owner)
                                        <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                                            {{ $owner->name }} ({{ $owner->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('owner_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Contact Number</label>
                                <input type="text" name="contact_number" id="contact_number" 
                                       class="form-control"
                                       placeholder="Enter contact number" 
                                       value="{{ old('contact_number') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warehouse Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-warehouse text-green-500 me-2"></i>
                        Warehouse Details
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Total Area (sq ft)</label>
                                <input type="number" name="area_sqft" id="area_sqft" step="0.01"
                                       class="form-control @error('area_sqft') is-invalid @enderror"
                                       placeholder="Enter area in sq ft" 
                                       value="{{ old('area_sqft') }}"
                                       oninput="convertArea(this.value)">
                                @error('area_sqft') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Area (sq meters)</label>
                                <input type="number" name="area_sqm" id="area_sqm" step="0.01"
                                       class="form-control"
                                       placeholder="Auto-calculated" 
                                       value="{{ old('area_sqm') }}" readonly>
                                <small class="text-muted">Auto-converted from sq ft</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Price per sq ft (NPR)</label>
                                <input type="number" name="price_per_sqft" id="price_per_sqft" step="0.01"
                                       class="form-control @error('price_per_sqft') is-invalid @enderror"
                                       placeholder="Enter price" 
                                       value="{{ old('price_per_sqft') }}">
                                @error('price_per_sqft') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" id="description" rows="4" 
                                          class="form-control @error('description') is-invalid @enderror"
                                          placeholder="Describe the warehouse, special features, etc.">{{ old('description') }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Location -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-map-marker-alt text-red-500 me-2"></i>
                        Location Map
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Latitude</label>
                                <input type="text" name="latitude" id="latitude" 
                                       class="form-control"
                                       placeholder="Latitude" 
                                       value="{{ old('latitude', '27.7172') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Longitude</label>
                                <input type="text" name="longitude" id="longitude" 
                                       class="form-control"
                                       placeholder="Longitude" 
                                       value="{{ old('longitude', '85.3240') }}" readonly>
                            </div>
                        </div>
                        
                        <div id="map" class="map-container"></div>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Click on the map to select location. Drag the marker to adjust.
                        </small>
                    </div>
                </div>

                <!-- Security Features -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-shield-alt text-indigo-500 me-2"></i>
                        Security Features
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">CCTV Cameras</label>
                                <input type="number" name="cctv_count" id="cctv_count" min="0"
                                       class="form-control"
                                       placeholder="Number of CCTV" 
                                       value="{{ old('cctv_count', 0) }}"
                                       onchange="toggleCCTVUrls(this.value)">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Security Guards</label>
                                <input type="number" name="guards_count" id="guards_count" min="0"
                                       class="form-control"
                                       placeholder="Number of guards" 
                                       value="{{ old('guards_count', 0) }}">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fire Extinguishers</label>
                                <input type="number" name="fire_extinguishers" id="fire_extinguishers" min="0"
                                       class="form-control"
                                       placeholder="Number of extinguishers" 
                                       value="{{ old('fire_extinguishers', 0) }}">
                            </div>
                        </div>
                        
                        <!-- CCTV Stream URLs -->
                        <div id="cctvUrlsContainer" class="mt-3 {{ old('cctv_count', 0) > 0 ? '' : 'd-none' }}">
                            <label class="form-label fw-bold">CCTV Stream URLs</label>
                            <div id="cctvUrlList">
                                @if(old('cctv_count', 0) > 0)
                                    @for($i = 0; $i < old('cctv_count', 0); $i++)
                                        <div class="cctv-url-input">
                                            <label class="small text-muted">Camera {{ $i + 1 }} Stream URL</label>
                                            <input type="url" name="cctv_stream_urls[]" 
                                                   class="form-control form-control-sm"
                                                   placeholder="https://cctv-stream-url.com/camera-{{ $i + 1 }}"
                                                   value="{{ old('cctv_stream_urls.'.$i) }}">
                                        </div>
                                    @endfor
                                @endif
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Enter the stream URLs for each CCTV camera (optional)
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Nearby Facilities -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-hospital text-purple-500 me-2"></i>
                        Nearby Facilities
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Police Station</label>
                                <input type="text" name="nearby_police" 
                                       class="form-control"
                                       placeholder="Distance/Name" 
                                       value="{{ old('nearby_police') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fire Station</label>
                                <input type="text" name="nearby_fire" 
                                       class="form-control"
                                       placeholder="Distance/Name" 
                                       value="{{ old('nearby_fire') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Hospital</label>
                                <input type="text" name="nearby_hospital" 
                                       class="form-control"
                                       placeholder="Distance/Name" 
                                       value="{{ old('nearby_hospital') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Bank/ATM</label>
                                <input type="text" name="nearby_bank" 
                                       class="form-control"
                                       placeholder="Distance/Name" 
                                       value="{{ old('nearby_bank') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fuel Station</label>
                                <input type="text" name="nearby_fuel" 
                                       class="form-control"
                                       placeholder="Distance/Name" 
                                       value="{{ old('nearby_fuel') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Market/Shopping</label>
                                <input type="text" name="nearby_market" 
                                       class="form-control"
                                       placeholder="Distance/Name" 
                                       value="{{ old('nearby_market') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cold Storage -->
                <div class="card mb-4 border-0 shadow-sm rounded-xl overflow-hidden">
                    <div class="card-header bg-white border-bottom border-slate-100 py-3 px-4 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-sky-50 text-sky-600" style="width: 38px; height: 38px; font-size: 16px;">
                                <i class="fas fa-snowflake"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-bold text-slate-800" style="font-size: 15px;">Cold Storage Facility</h6>
                                <p class="mb-0 text-muted small" style="font-size: 12px;">Temperature-controlled environment for food, produce, or pharmaceuticals</p>
                            </div>
                        </div>
                        
                        <!-- Modern Switch Toggle -->
                        <label class="kwdc-switch-toggle" for="cold_storage" title="Toggle Cold Storage">
                            <input type="checkbox" name="cold_storage" id="cold_storage" value="1"
                                   onchange="toggleColdStorage(this.checked)" 
                                   {{ old('cold_storage') ? 'checked' : '' }}>
                            <span class="kwdc-switch-slider"></span>
                        </label>
                    </div>
                    <div class="card-body p-4 bg-white">
                        <div id="coldStorageFields" class="{{ old('cold_storage') ? '' : 'd-none' }}">
                            <div class="p-3.5 rounded-xl border border-sky-200 bg-sky-50/60 mb-1">
                                <div class="row g-3">
                                    <div class="col-md-5">
                                        <label class="form-label small fw-bold text-slate-700 mb-1.5 d-flex align-items-center gap-1.5">
                                            <i class="fas fa-temperature-low text-sky-600"></i>
                                            Min Temperature (°C)
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="temperature_min" 
                                                   class="form-control rounded-start"
                                                   placeholder="e.g., -20" 
                                                   value="{{ old('temperature_min') }}" step="0.1">
                                            <span class="input-group-text bg-white text-muted">°C</span>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small fw-bold text-slate-700 mb-1.5 d-flex align-items-center gap-1.5">
                                            <i class="fas fa-temperature-high text-amber-600"></i>
                                            Max Temperature (°C)
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" name="temperature_max" 
                                                   class="form-control rounded-start"
                                                   placeholder="e.g., 10" 
                                                   value="{{ old('temperature_max') }}" step="0.1">
                                            <span class="input-group-text bg-white text-muted">°C</span>
                                        </div>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <label class="kwdc-checkbox-pill w-100 mb-0 d-flex align-items-center justify-content-center gap-2 p-2 rounded-lg {{ old('humidity_control') ? 'active' : '' }}" for="humidity_control">
                                            <input type="checkbox" name="humidity_control" id="humidity_control" value="1" class="d-none"
                                                   onchange="this.parentElement.classList.toggle('active', this.checked)"
                                                   {{ old('humidity_control') ? 'checked' : '' }}>
                                            <i class="fas fa-droplet text-sky-500"></i>
                                            <span class="small fw-semibold">Humidity</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Facilities -->
                <div class="card mb-4 border-0 shadow-sm rounded-xl overflow-hidden">
                    <div class="card-header bg-white border-bottom border-slate-100 py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-emerald-50 text-emerald-600" style="width: 38px; height: 38px; font-size: 16px;">
                                <i class="fas fa-layer-group"></i>
                            </span>
                            <div>
                                <h6 class="mb-0 fw-bold text-slate-800" style="font-size: 15px;">Facilities & Amenities</h6>
                                <p class="mb-0 text-muted small" style="font-size: 12px;">Click chips to toggle available infrastructure features</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge rounded-pill bg-slate-100 text-slate-700 px-3 py-1.5 fw-semibold border border-slate-200">
                                <span id="facilityCount">{{ count($oldFacilities ?? []) }}</span> Selected
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-lg text-xs" onclick="selectAllFacilities(true)">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-lg text-xs" onclick="selectAllFacilities(false)">Clear</button>
                        </div>
                    </div>
                    <div class="card-body p-4 bg-white">
                        @php
                            $facilities = [
                                '24/7 Security', 'CCTV Surveillance', 'Loading Dock', 'Forklift',
                                'Pallet Jacks', 'Racking System', 'Fire Protection', 'Sprinkler System',
                                'Generator Backup', 'High Speed Internet', 'Office Space', 'Staff Room',
                                'Restroom Facilities', 'Parking Area', 'Fenced Premises', 'Lighting System',
                                'Climate Control', 'Washroom', 'Canteen', 'Security Guard',
                                'Emergency Exit', 'First Aid Kit', 'Fire Alarm', 'Smoke Detector',
                                'Water Supply', 'Electricity Backup', 'Weight Bridge', 'Security Fence'
                            ];
                            $oldFacilities = old('facilities', []);
                            if (is_string($oldFacilities)) {
                                $oldFacilities = json_decode($oldFacilities, true) ?? [];
                            }
                        @endphp

                        <div class="mb-3 position-relative">
                            <i class="fas fa-search position-absolute top-50 translate-middle-y text-slate-400 ms-3" style="font-size: 13px;"></i>
                            <input type="text" id="facilitySearch" class="form-control form-control-sm ps-5 rounded-lg bg-slate-50 border-slate-200" 
                                   placeholder="Filter facilities by keyword (e.g. CCTV, Security, Loading)..." 
                                   oninput="filterFacilities(this.value)">
                        </div>
                        
                        <div id="facilitiesContainer" class="d-flex flex-wrap gap-2">
                            @foreach($facilities as $facility)
                                @php
                                    $isSelected = in_array($facility, $oldFacilities);
                                    $icon = match(true) {
                                        str_contains($facility, 'Security') || str_contains($facility, 'Guard') => 'fa-shield-halved',
                                        str_contains($facility, 'CCTV') => 'fa-video',
                                        str_contains($facility, 'Dock') => 'fa-truck-ramp-box',
                                        str_contains($facility, 'Forklift') => 'fa-dolly',
                                        str_contains($facility, 'Pallet') || str_contains($facility, 'Racking') => 'fa-boxes-stacked',
                                        str_contains($facility, 'Fire') || str_contains($facility, 'Smoke') => 'fa-fire-extinguisher',
                                        str_contains($facility, 'Sprinkler') => 'fa-faucet-drip',
                                        str_contains($facility, 'Generator') || str_contains($facility, 'Electricity') => 'fa-bolt',
                                        str_contains($facility, 'Internet') => 'fa-wifi',
                                        str_contains($facility, 'Office') => 'fa-briefcase',
                                        str_contains($facility, 'Staff') || str_contains($facility, 'Restroom') || str_contains($facility, 'Washroom') => 'fa-restroom',
                                        str_contains($facility, 'Parking') => 'fa-square-parking',
                                        str_contains($facility, 'Fence') || str_contains($facility, 'Premises') => 'fa-border-all',
                                        str_contains($facility, 'Lighting') => 'fa-lightbulb',
                                        str_contains($facility, 'Climate') => 'fa-temperature-half',
                                        str_contains($facility, 'Canteen') => 'fa-utensils',
                                        str_contains($facility, 'Exit') => 'fa-door-open',
                                        str_contains($facility, 'First Aid') => 'fa-kit-medical',
                                        str_contains($facility, 'Water') => 'fa-water',
                                        str_contains($facility, 'Weight') => 'fa-scale-balanced',
                                        default => 'fa-circle-check'
                                    };
                                @endphp
                                <button type="button" 
                                        class="kwdc-facility-pill {{ $isSelected ? 'selected' : '' }}"
                                        data-name="{{ $facility }}"
                                        onclick="toggleFacility(this, '{{ addslashes($facility) }}')">
                                    <span class="pill-check-indicator">
                                        <i class="fas fa-check"></i>
                                    </span>
                                    <i class="fas {{ $icon }} pill-type-icon"></i>
                                    <span class="pill-label">{{ $facility }}</span>
                                </button>
                            @endforeach
                        </div>
                        
                        <input type="hidden" name="facilities" id="facilities" value='{{ json_encode($oldFacilities) }}'>
                    </div>
                </div>

                <!-- Status -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-check-circle text-orange-500 me-2"></i>
                        Status
                    </div>
                    <div class="card-body">
                        <select name="status" class="form-select">
                            <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending Approval</option>
                            <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        <small class="text-muted">Default: Pending Approval</small>
                    </div>
                </div>
            </div>

            <!-- Sidebar - 4 columns -->
            <div class="col-lg-4">
                <div class="sticky-sidebar">
                    <!-- Photos -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-camera text-purple-500 me-2"></i>
                            Photos
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Front View</label>
                                <div class="upload-area" onclick="document.getElementById('front_image').click()">
                                    <div id="frontPreview" class="d-flex align-items-center justify-content-center" style="min-height:100px;">
                                        <span class="text-muted">
                                            <i class="fas fa-cloud-upload-alt fa-2x d-block"></i>
                                            <small>Click to upload</small>
                                        </span>
                                    </div>
                                    <input type="file" name="front_image" id="front_image" accept="image/*" class="d-none" onchange="previewImage(this, 'frontPreview')">
                                </div>
                                <small class="text-muted">Max 5MB. JPG, PNG, GIF</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Interior View</label>
                                <div class="upload-area" onclick="document.getElementById('interior_image').click()">
                                    <div id="interiorPreview" class="d-flex align-items-center justify-content-center" style="min-height:100px;">
                                        <span class="text-muted">
                                            <i class="fas fa-cloud-upload-alt fa-2x d-block"></i>
                                            <small>Click to upload</small>
                                        </span>
                                    </div>
                                    <input type="file" name="interior_image" id="interior_image" accept="image/*" class="d-none" onchange="previewImage(this, 'interiorPreview')">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Exterior View</label>
                                <div class="upload-area" onclick="document.getElementById('exterior_image').click()">
                                    <div id="exteriorPreview" class="d-flex align-items-center justify-content-center" style="min-height:100px;">
                                        <span class="text-muted">
                                            <i class="fas fa-cloud-upload-alt fa-2x d-block"></i>
                                            <small>Click to upload</small>
                                        </span>
                                    </div>
                                    <input type="file" name="exterior_image" id="exterior_image" accept="image/*" class="d-none" onchange="previewImage(this, 'exteriorPreview')">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-file-pdf text-red-500 me-2"></i>
                            Documents
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ownership Document</label>
                                <input type="file" name="ownership_document" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                                       class="form-control">
                                <small class="text-muted">Max 10MB. PDF, DOC, JPG, PNG</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tax Document</label>
                                <input type="file" name="tax_document" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                                       class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Fire Safety Document</label>
                                <input type="file" name="fire_safety_document" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                                       class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Building Approval Document</label>
                                <input type="file" name="building_approval_document" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                                       class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-orange w-100 py-3 fw-bold" 
                                    style="background: #f59e0b; color: white; border: none; border-radius: 8px;">
                                <i class="fas fa-save me-2"></i>Create Warehouse
                            </button>
                            <small class="text-muted d-block text-center mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                All fields marked with <span class="text-danger">*</span> are required
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ============================================================ -->
<!-- JAVASCRIPT -->
<!-- ============================================================ -->
<script>
// ============================================================
// AREA CONVERSION
// ============================================================
function convertArea(sqft) {
    if (sqft && parseFloat(sqft) > 0) {
        const sqm = parseFloat(sqft) / 10.764;
        document.getElementById('area_sqm').value = sqm.toFixed(2);
    } else {
        document.getElementById('area_sqm').value = '';
    }
}

// ============================================================
// COLD STORAGE TOGGLE
// ============================================================
function toggleColdStorage(checked) {
    const fields = document.getElementById('coldStorageFields');
    if (checked) {
        fields.classList.remove('d-none');
        fields.style.display = 'block';
    } else {
        fields.classList.add('d-none');
        fields.style.display = 'none';
    }
}

// ============================================================
// FACILITIES TOGGLE
// ============================================================
let selectedFacilities = [];

function toggleFacility(element, facility) {
    element.classList.toggle('selected');
    
    if (element.classList.contains('selected')) {
        if (!selectedFacilities.includes(facility)) {
            selectedFacilities.push(facility);
        }
    } else {
        selectedFacilities = selectedFacilities.filter(f => f !== facility);
    }
    
    document.getElementById('facilities').value = JSON.stringify(selectedFacilities);
    const countEl = document.getElementById('facilityCount');
    if (countEl) countEl.textContent = selectedFacilities.length;
}

function selectAllFacilities(selectAll) {
    const pills = document.querySelectorAll('.kwdc-facility-pill');
    selectedFacilities = [];
    pills.forEach(pill => {
        const name = pill.dataset.name;
        if (selectAll) {
            pill.classList.add('selected');
            selectedFacilities.push(name);
        } else {
            pill.classList.remove('selected');
        }
    });
    document.getElementById('facilities').value = JSON.stringify(selectedFacilities);
    const countEl = document.getElementById('facilityCount');
    if (countEl) countEl.textContent = selectedFacilities.length;
}

function filterFacilities(query) {
    const q = (query || '').toLowerCase().trim();
    document.querySelectorAll('.kwdc-facility-pill').forEach(pill => {
        const name = (pill.dataset.name || '').toLowerCase();
        if (!q || name.includes(q)) {
            pill.style.display = 'inline-flex';
        } else {
            pill.style.display = 'none';
        }
    });
}

// ============================================================
// CCTV URLS
// ============================================================
function toggleCCTVUrls(count) {
    const container = document.getElementById('cctvUrlsContainer');
    const list = document.getElementById('cctvUrlList');
    
    count = parseInt(count) || 0;
    
    if (count > 0) {
        container.classList.remove('d-none');
        container.style.display = 'block';
        list.innerHTML = '';
        
        for (let i = 0; i < count; i++) {
            list.innerHTML += `
                <div class="cctv-url-input">
                    <label class="small text-muted">Camera ${i + 1} Stream URL</label>
                    <input type="url" name="cctv_stream_urls[]" 
                           class="form-control form-control-sm"
                           placeholder="https://cctv-stream-url.com/camera-${i + 1}">
                </div>
            `;
        }
    } else {
        container.classList.add('d-none');
        container.style.display = 'none';
        list.innerHTML = '';
    }
}

// ============================================================
// IMAGE PREVIEW
// ============================================================
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" class="preview-image">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// ============================================================
// MAP FUNCTIONALITY (Leaflet)
// ============================================================
let map, marker;

function initAdminWarehouseCreateMap() {
    const mapEl = document.getElementById('map');
    if (!mapEl) return;
    if (typeof L === 'undefined') {
        setTimeout(initAdminWarehouseCreateMap, 100);
        return;
    }

    if (mapEl._leaflet_id) {
        mapEl._leaflet_id = null;
        mapEl.innerHTML = '';
    }
    
    const defaultLat = 27.7172;
    const defaultLng = 85.3240;
    
    // Initialize map
    map = L.map(mapEl, {
        zoomControl: true,
        scrollWheelZoom: false
    }).setView([defaultLat, defaultLng], 13);
    mapEl._leaflet_map = map;
    
    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const resize = () => { map.invalidateSize(); };
    setTimeout(resize, 80);
    setTimeout(resize, 250);
    setTimeout(resize, 600);
    window.addEventListener('resize', resize);
    
    // Add marker
    marker = L.marker([defaultLat, defaultLng], {
        draggable: true
    }).addTo(map);
    
    // On marker drag end - update coordinates
    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        document.getElementById('latitude').value = position.lat.toFixed(8);
        document.getElementById('longitude').value = position.lng.toFixed(8);
        
        // Reverse geocode to get address
        reverseGeocode(position.lat, position.lng);
    });
    
    // On map click - move marker
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        marker.setLatLng(e.latlng);
        document.getElementById('latitude').value = lat.toFixed(8);
        document.getElementById('longitude').value = lng.toFixed(8);
        
        // Reverse geocode to get address
        reverseGeocode(lat, lng);
    });
    
    // If there's an existing address, geocode it
    const locationInput = document.getElementById('location');
    if (locationInput && locationInput.value && locationInput.value.length > 5) {
        geocodeAddress(locationInput.value);
    }
}

if (document.readyState !== 'loading') {
    initAdminWarehouseCreateMap();
} else {
    document.addEventListener('DOMContentLoaded', initAdminWarehouseCreateMap);
}
document.addEventListener('kwdc:page-loaded', initAdminWarehouseCreateMap);

// ============================================================
// REVERSE GEOCODING
// ============================================================
function reverseGeocode(lat, lng) {
    const url = `/maps/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.display_name) {
                document.getElementById('location').value = data.display_name;
            }
        })
        .catch(error => console.error('Geocoding error:', error));
}

// ============================================================
// ADDRESS SEARCH & GEOCODING
// ============================================================
function geocodeAddress(address) {
    const url = `/maps/search?format=json&q=${encodeURIComponent(address)}&limit=1`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.length > 0) {
                const lat = parseFloat(data[0].lat);
                const lng = parseFloat(data[0].lon);
                document.getElementById('latitude').value = lat.toFixed(8);
                document.getElementById('longitude').value = lng.toFixed(8);
                
                // Update map
                if (map && marker) {
                    map.setView([lat, lng], 15);
                    marker.setLatLng([lat, lng]);
                }
            }
        })
        .catch(error => console.error('Geocoding error:', error));
}

// ============================================================
// ADDRESS INPUT EVENT
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const locationInput = document.getElementById('location');
    if (locationInput) {
        let typingTimer;
        const doneTypingInterval = 1000;
        
        locationInput.addEventListener('keyup', function() {
            clearTimeout(typingTimer);
            if (this.value.length > 5) {
                typingTimer = setTimeout(function() {
                    geocodeAddress(locationInput.value);
                }, doneTypingInterval);
            }
        });
        
        locationInput.addEventListener('blur', function() {
            if (this.value.length > 5) {
                geocodeAddress(this.value);
            }
        });
    }
});

// ============================================================
// FORM VALIDATION
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('warehouseForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const location = document.getElementById('location').value.trim();
            const ownerId = document.getElementById('owner_id').value;
            
            if (!name) {
                e.preventDefault();
                alert('Please enter the warehouse name.');
                document.getElementById('name').focus();
                return false;
            }
            
            if (!location) {
                e.preventDefault();
                alert('Please enter the location address.');
                document.getElementById('location').focus();
                return false;
            }
            
            if (!ownerId) {
                e.preventDefault();
                alert('Please select a property owner.');
                document.getElementById('owner_id').focus();
                return false;
            }
            
            return true;
        });
    }
});

// ============================================================
// INITIALIZE FACILITIES FROM OLD VALUES
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const facilitiesInput = document.getElementById('facilities');
    if (facilitiesInput && facilitiesInput.value) {
        try {
            const facilities = JSON.parse(facilitiesInput.value);
            selectedFacilities = facilities;
            
            document.querySelectorAll('.kwdc-facility-pill').forEach(tag => {
                const facilityName = tag.dataset.name || tag.querySelector('.pill-label')?.textContent.trim();
                if (facilities.includes(facilityName)) {
                    tag.classList.add('selected');
                }
            });
            const countEl = document.getElementById('facilityCount');
            if (countEl) countEl.textContent = facilities.length;
        } catch(e) {
            console.error('Error parsing facilities:', e);
        }
    }
    
    // Initialize CCTV URLs if count > 0
    const cctvCount = document.getElementById('cctv_count');
    if (cctvCount && parseInt(cctvCount.value) > 0) {
        toggleCCTVUrls(cctvCount.value);
    }
});
</script>
@endsection
