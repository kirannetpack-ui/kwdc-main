@extends('layouts.app')

@section('title', 'Edit Warehouse')
@section('header', 'Edit Warehouse')

@push('styles')
<style>
    .photo-preview {
        max-height: 150px;
        object-fit: cover;
        border-radius: 8px;
        width: 100%;
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
    .current-file {
        background: #f0fdf4;
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #bbf7d0;
        font-size: 13px;
    }
    .cold-storage-fields {
        background: #f0fdf4;
        border-radius: 8px;
        padding: 16px;
        margin-top: 12px;
        border: 1px solid #bbf7d0;
    }
    .cctv-url-input {
        margin-top: 8px;
        padding: 8px;
        background: #f9fafb;
        border-radius: 6px;
    }
    .map-container {
        height: 400px;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold text-gray-800">
                <i class="fas fa-edit text-warning me-2"></i>Edit Warehouse
            </h1>
            <p class="text-muted small">Update your property information</p>
        </div>
        <div>
            <a href="{{ route('warehouses.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Back to Properties
            </a>
            <a href="{{ route('property.warehouses.show', $warehouse->id) }}" class="btn btn-info">
                <i class="fas fa-eye me-2"></i>View Property
            </a>
        </div>
    </div>

    <form action="{{ route('property.warehouses.update', $warehouse->id) }}" method="POST" enctype="multipart/form-data" id="warehouseForm">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- Main Form - 8 columns -->
            <div class="col-lg-8">
                <!-- Status Alert -->
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Status:</strong> 
                    <span class="badge bg-{{ $warehouse->status == 'approved' ? 'success' : ($warehouse->status == 'pending' ? 'warning' : 'danger') }}">
                        {{ ucfirst($warehouse->status) }}
                    </span>
                    @if($warehouse->status == 'pending')
                        <span class="ms-2 text-muted">(Awaiting admin approval)</span>
                    @endif
                </div>

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
                                <input type="text" name="name" 
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $warehouse->name) }}" required>
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Location / Area <span class="text-danger">*</span></label>
                                <input type="text" name="location" 
                                       class="form-control @error('location') is-invalid @enderror"
                                       value="{{ old('location', $warehouse->location) }}" required>
                                @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Contact Number <span class="text-danger">*</span></label>
                                <input type="text" name="contact_number" 
                                       class="form-control @error('contact_number') is-invalid @enderror"
                                       value="{{ old('contact_number', $warehouse->contact_number) }}" required>
                                @error('contact_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Street Address</label>
                                <input type="text" name="address" 
                                       class="form-control @error('address') is-invalid @enderror"
                                       value="{{ old('address', $warehouse->address) }}">
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">City / Municipality</label>
                                <input type="text" name="city" 
                                       class="form-control"
                                       value="{{ old('city', $warehouse->city) }}">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">State / Province</label>
                                <input type="text" name="state" 
                                       class="form-control"
                                       value="{{ old('state', $warehouse->state) }}">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Postal Code</label>
                                <input type="text" name="postal_code" 
                                       class="form-control"
                                       value="{{ old('postal_code', $warehouse->postal_code) }}">
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Email Address</label>
                                <input type="email" name="email" 
                                       class="form-control"
                                       value="{{ old('email', $warehouse->email) }}">
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
                                       value="{{ old('latitude', $warehouse->latitude ?? '27.7172') }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Longitude</label>
                                <input type="text" name="longitude" id="longitude"
                                       class="form-control"
                                       value="{{ old('longitude', $warehouse->longitude ?? '85.3240') }}" readonly>
                            </div>
                        </div>
                        
                        <div id="map" class="map-container"></div>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle me-1"></i>
                            Click on the map to select location. Drag the marker to adjust.
                        </small>
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
                                <label class="form-label fw-bold">Total Area (sq ft) <span class="text-danger">*</span></label>
                                <input type="number" name="area_sqft" id="area_sqft" step="0.01"
                                       class="form-control @error('area_sqft') is-invalid @enderror"
                                       value="{{ old('area_sqft', $warehouse->area_sqft) }}" 
                                       oninput="convertArea(this.value)" required>
                                <small class="text-muted">Current: {{ number_format($warehouse->area_sqft ?? 0) }} sq ft</small>
                                @error('area_sqft') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Area (sq meters)</label>
                                <input type="number" name="area_sqm" id="area_sqm" step="0.01"
                                       class="form-control"
                                       value="{{ old('area_sqm', $warehouse->area_sqm) }}" readonly>
                                <small class="text-muted">Auto-converted from sq ft</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Price per sq ft (NPR) <span class="text-danger">*</span></label>
                                <input type="number" name="price_per_sqft" step="0.01"
                                       class="form-control @error('price_per_sqft') is-invalid @enderror"
                                       value="{{ old('price_per_sqft', $warehouse->price_per_sqft) }}" required>
                                <small class="text-muted">Current: रू {{ number_format($warehouse->price_per_sqft ?? 0, 2) }}</small>
                                @error('price_per_sqft') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" rows="4" 
                                          class="form-control @error('description') is-invalid @enderror"
                                          placeholder="Describe your warehouse">{{ old('description', $warehouse->description) }}</textarea>
                                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rental & Availability Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-calendar-alt text-teal-500 me-2"></i>
                        Rental & Availability Details
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Available From</label>
                                <input type="date" name="available_from" 
                                       class="form-control @error('available_from') is-invalid @enderror"
                                       value="{{ old('available_from', $warehouse->available_from ? date('Y-m-d', strtotime($warehouse->available_from)) : '') }}">
                                <small class="text-muted">Current: {{ $warehouse->available_from ? date('F d, Y', strtotime($warehouse->available_from)) : 'Not set' }}</small>
                                @error('available_from') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Minimum Rental Period</label>
                                <select name="minimum_rental_period" 
                                        class="form-select @error('minimum_rental_period') is-invalid @enderror">
                                    <option value="1" {{ old('minimum_rental_period', $warehouse->minimum_rental_period ?? 1) == 1 ? 'selected' : '' }}>1 Month</option>
                                    <option value="2" {{ old('minimum_rental_period', $warehouse->minimum_rental_period) == 2 ? 'selected' : '' }}>2 Months</option>
                                    <option value="3" {{ old('minimum_rental_period', $warehouse->minimum_rental_period) == 3 ? 'selected' : '' }}>3 Months</option>
                                    <option value="6" {{ old('minimum_rental_period', $warehouse->minimum_rental_period) == 6 ? 'selected' : '' }}>6 Months</option>
                                    <option value="12" {{ old('minimum_rental_period', $warehouse->minimum_rental_period) == 12 ? 'selected' : '' }}>1 Year</option>
                                    <option value="24" {{ old('minimum_rental_period', $warehouse->minimum_rental_period) == 24 ? 'selected' : '' }}>2 Years</option>
                                    <option value="36" {{ old('minimum_rental_period', $warehouse->minimum_rental_period) == 36 ? 'selected' : '' }}>3 Years</option>
                                    <option value="0" {{ old('minimum_rental_period', $warehouse->minimum_rental_period) == 0 ? 'selected' : '' }}>No Minimum</option>
                                </select>
                                <small class="text-muted">Current: {{ $warehouse->minimum_rental_period == 0 ? 'No Minimum' : ($warehouse->minimum_rental_period . ' Month(s)') }}</small>
                                @error('minimum_rental_period') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Facilities -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-building text-orange-500 me-2"></i>
                        Additional Facilities
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="insurance_available" id="insurance_available" 
                                           class="form-check-input"
                                           {{ old('insurance_available', $warehouse->insurance_available) ? 'checked' : '' }}>
                                    <label for="insurance_available" class="form-check-label">
                                        <i class="fas fa-shield-alt text-green-500 me-1"></i>
                                        Insurance Available
                                    </label>
                                </div>
                                <small class="text-muted {{ old('insurance_available', $warehouse->insurance_available) ? 'text-success' : 'text-muted' }}">
                                    {{ old('insurance_available', $warehouse->insurance_available) ? '✅ Available' : '❌ Not Available' }}
                                </small>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="loading_dock" id="loading_dock" 
                                           class="form-check-input"
                                           {{ old('loading_dock', $warehouse->loading_dock) ? 'checked' : '' }}>
                                    <label for="loading_dock" class="form-check-label">
                                        <i class="fas fa-truck-loading text-blue-500 me-1"></i>
                                        Loading Dock
                                    </label>
                                </div>
                                <small class="text-muted {{ old('loading_dock', $warehouse->loading_dock) ? 'text-success' : 'text-muted' }}">
                                    {{ old('loading_dock', $warehouse->loading_dock) ? '✅ Available' : '❌ Not Available' }}
                                </small>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="office_space" id="office_space" 
                                           class="form-check-input"
                                           {{ old('office_space', $warehouse->office_space) ? 'checked' : '' }}>
                                    <label for="office_space" class="form-check-label">
                                        <i class="fas fa-chair text-purple-500 me-1"></i>
                                        Office Space
                                    </label>
                                </div>
                                <small class="text-muted {{ old('office_space', $warehouse->office_space) ? 'text-success' : 'text-muted' }}">
                                    {{ old('office_space', $warehouse->office_space) ? '✅ Available' : '❌ Not Available' }}
                                </small>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="staff_quarters" id="staff_quarters" 
                                           class="form-check-input"
                                           {{ old('staff_quarters', $warehouse->staff_quarters) ? 'checked' : '' }}>
                                    <label for="staff_quarters" class="form-check-label">
                                        <i class="fas fa-users text-indigo-500 me-1"></i>
                                        Staff Quarters
                                    </label>
                                </div>
                                <small class="text-muted {{ old('staff_quarters', $warehouse->staff_quarters) ? 'text-success' : 'text-muted' }}">
                                    {{ old('staff_quarters', $warehouse->staff_quarters) ? '✅ Available' : '❌ Not Available' }}
                                </small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Parking Space Count</label>
                                <input type="number" name="parking_spaces" min="0"
                                       class="form-control @error('parking_spaces') is-invalid @enderror"
                                       value="{{ old('parking_spaces', $warehouse->parking_spaces ?? 0) }}">
                                <small class="text-muted">Current: {{ $warehouse->parking_spaces ?? 0 }} spaces</small>
                                @error('parking_spaces') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Parking Type</label>
                                <select name="parking_type" class="form-select">
                                    <option value="open" {{ old('parking_type', $warehouse->parking_type ?? 'open') == 'open' ? 'selected' : '' }}>Open Parking</option>
                                    <option value="covered" {{ old('parking_type', $warehouse->parking_type) == 'covered' ? 'selected' : '' }}>Covered Parking</option>
                                    <option value="both" {{ old('parking_type', $warehouse->parking_type) == 'both' ? 'selected' : '' }}>Both Open & Covered</option>
                                    <option value="none" {{ old('parking_type', $warehouse->parking_type) == 'none' ? 'selected' : '' }}>No Parking</option>
                                </select>
                                <small class="text-muted">Current: {{ ucfirst(str_replace('_', ' ', $warehouse->parking_type ?? 'open')) }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-info-circle text-cyan-500 me-2"></i>
                        Additional Information
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-bold">Special Notes</label>
                                <textarea name="special_notes" rows="3" 
                                          class="form-control @error('special_notes') is-invalid @enderror"
                                          placeholder="Any additional information, restrictions, or special conditions...">{{ old('special_notes', $warehouse->special_notes) }}</textarea>
                                @error('special_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
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
                                       value="{{ old('cctv_count', $warehouse->cctv_count ?? 0) }}"
                                       onchange="toggleCCTVUrls(this.value)">
                                <small class="text-muted">Current: {{ $warehouse->cctv_count ?? 0 }}</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Security Guards</label>
                                <input type="number" name="guards_count" min="0"
                                       class="form-control"
                                       value="{{ old('guards_count', $warehouse->guards_count ?? 0) }}">
                                <small class="text-muted">Current: {{ $warehouse->guards_count ?? 0 }}</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Fire Extinguishers</label>
                                <input type="number" name="fire_extinguishers" min="0"
                                       class="form-control"
                                       value="{{ old('fire_extinguishers', $warehouse->fire_extinguishers ?? 0) }}">
                                <small class="text-muted">Current: {{ $warehouse->fire_extinguishers ?? 0 }}</small>
                            </div>
                        </div>
                        
                        <!-- CCTV Stream URLs -->
                        <div id="cctvUrlsContainer" class="mt-3 {{ old('cctv_count', $warehouse->cctv_count ?? 0) > 0 ? '' : 'd-none' }}">
                            <label class="form-label fw-bold">CCTV Stream URLs</label>
                            <div id="cctvUrlList">
                                @php
                                    $cctvUrls = old('cctv_stream_urls', $warehouse->cctv_stream_urls ?? []);
                                    if (is_string($cctvUrls)) {
                                        $cctvUrls = json_decode($cctvUrls, true) ?? [];
                                    }
                                    $cctvCount = max(old('cctv_count', $warehouse->cctv_count ?? 0), count($cctvUrls));
                                @endphp
                                @if($cctvCount > 0)
                                    @for($i = 0; $i < $cctvCount; $i++)
                                        <div class="cctv-url-input">
                                            <label class="small text-muted">Camera {{ $i + 1 }} Stream URL</label>
                                            <input type="url" name="cctv_stream_urls[]" 
                                                   class="form-control form-control-sm"
                                                   placeholder="https://cctv-stream-url.com/camera-{{ $i + 1 }}"
                                                   value="{{ $cctvUrls[$i] ?? '' }}">
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
                                       value="{{ old('nearby_police', $warehouse->nearby_police) }}">
                                <small class="text-muted">Current: {{ $warehouse->nearby_police ?? 'Not set' }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fire Station</label>
                                <input type="text" name="nearby_fire" 
                                       class="form-control"
                                       value="{{ old('nearby_fire', $warehouse->nearby_fire) }}">
                                <small class="text-muted">Current: {{ $warehouse->nearby_fire ?? 'Not set' }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Hospital</label>
                                <input type="text" name="nearby_hospital" 
                                       class="form-control"
                                       value="{{ old('nearby_hospital', $warehouse->nearby_hospital) }}">
                                <small class="text-muted">Current: {{ $warehouse->nearby_hospital ?? 'Not set' }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Bank/ATM</label>
                                <input type="text" name="nearby_bank" 
                                       class="form-control"
                                       value="{{ old('nearby_bank', $warehouse->nearby_bank) }}">
                                <small class="text-muted">Current: {{ $warehouse->nearby_bank ?? 'Not set' }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Fuel Station</label>
                                <input type="text" name="nearby_fuel" 
                                       class="form-control"
                                       value="{{ old('nearby_fuel', $warehouse->nearby_fuel) }}">
                                <small class="text-muted">Current: {{ $warehouse->nearby_fuel ?? 'Not set' }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Market/Shopping</label>
                                <input type="text" name="nearby_market" 
                                       class="form-control"
                                       value="{{ old('nearby_market', $warehouse->nearby_market) }}">
                                <small class="text-muted">Current: {{ $warehouse->nearby_market ?? 'Not set' }}</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cold Storage -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-snowflake text-sky-600 me-2"></i>
                        Cold Storage
                    </div>
                    <div class="card-body p-4 bg-white">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom border-slate-100">
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
                                       {{ old('cold_storage', $warehouse->cold_storage) ? 'checked' : '' }}>
                                <span class="kwdc-switch-slider"></span>
                            </label>
                        </div>
                        
                        <div id="coldStorageFields" class="{{ old('cold_storage', $warehouse->cold_storage) ? '' : 'd-none' }}">
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
                                                   value="{{ old('temperature_min', $warehouse->temperature_min) }}" step="0.1">
                                            <span class="input-group-text bg-white text-muted">°C</span>
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;">Current: {{ $warehouse->temperature_min ?? 'Not set' }}</small>
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
                                                   value="{{ old('temperature_max', $warehouse->temperature_max) }}" step="0.1">
                                            <span class="input-group-text bg-white text-muted">°C</span>
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;">Current: {{ $warehouse->temperature_max ?? 'Not set' }}</small>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <label class="kwdc-checkbox-pill w-100 mb-0 d-flex align-items-center justify-content-center gap-2 p-2 rounded-lg {{ old('humidity_control', $warehouse->humidity_control) ? 'active' : '' }}" for="humidity_control">
                                            <input type="checkbox" name="humidity_control" id="humidity_control" value="1" class="d-none"
                                                   onchange="this.parentElement.classList.toggle('active', this.checked)"
                                                   {{ old('humidity_control', $warehouse->humidity_control) ? 'checked' : '' }}>
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
                            $oldFacilities = old('facilities', $warehouse->facilities ?? []);
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

                <!-- Admin Only Fields -->
                @if(auth()->user()->role == 'admin')
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-user-cog text-danger me-2"></i>
                        Admin Controls
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Status</label>
                                <select name="status" class="form-select">
                                    <option value="pending" {{ old('status', $warehouse->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ old('status', $warehouse->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ old('status', $warehouse->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                                <small class="text-muted">Current: {{ ucfirst($warehouse->status) }}</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Change Owner</label>
                                <select name="user_id" class="form-select">
                                    <option value="{{ $warehouse->user_id }}">Current: {{ $warehouse->user->name ?? 'N/A' }}</option>
                                    @foreach($propertyOwners ?? [] as $owner)
                                        <option value="{{ $owner->id }}">{{ $owner->name }} ({{ $owner->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar - 4 columns -->
            <div class="col-lg-4">
                <div class="sticky-sidebar">
                    <!-- Status Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-check-circle text-orange-500 me-2"></i>
                            Property Status
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Status:</span>
                                <span class="badge bg-{{ $warehouse->status == 'approved' ? 'success' : ($warehouse->status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($warehouse->status) }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Registered:</span>
                                <span class="text-dark">{{ $warehouse->created_at->format('F d, Y') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted">Last Updated:</span>
                                <span class="text-dark">{{ $warehouse->updated_at->format('F d, Y') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Owner:</span>
                                <span class="text-dark">{{ $warehouse->user->name ?? 'N/A' }}</span>
                            </div>
                            <hr>
                            <small class="text-muted d-block">
                                <i class="fas fa-info-circle me-1"></i>
                                All changes will be saved immediately
                            </small>
                        </div>
                    </div>

                    <!-- Photos -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-camera text-purple-500 me-2"></i>
                            Property Photos
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Front View</label>
                                @if($warehouse->front_image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $warehouse->front_image) }}" class="photo-preview" alt="Front View">
                                        <small class="text-muted d-block">Current image</small>
                                    </div>
                                @endif
                                <div class="upload-area" onclick="document.getElementById('front_image').click()">
                                    <div id="frontPreview" class="d-flex align-items-center justify-content-center" style="min-height:80px;">
                                        <span class="text-muted">
                                            <i class="fas fa-cloud-upload-alt fa-2x d-block"></i>
                                            <small>Click to upload new image</small>
                                        </span>
                                    </div>
                                    <input type="file" name="front_image" id="front_image" accept="image/*" class="d-none" onchange="previewImage(this, 'frontPreview')">
                                </div>
                                <small class="text-muted">Max 5MB. JPG, PNG, GIF</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Interior View</label>
                                @if($warehouse->interior_image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $warehouse->interior_image) }}" class="photo-preview" alt="Interior View">
                                        <small class="text-muted d-block">Current image</small>
                                    </div>
                                @endif
                                <div class="upload-area" onclick="document.getElementById('interior_image').click()">
                                    <div id="interiorPreview" class="d-flex align-items-center justify-content-center" style="min-height:80px;">
                                        <span class="text-muted">
                                            <i class="fas fa-cloud-upload-alt fa-2x d-block"></i>
                                            <small>Click to upload new image</small>
                                        </span>
                                    </div>
                                    <input type="file" name="interior_image" id="interior_image" accept="image/*" class="d-none" onchange="previewImage(this, 'interiorPreview')">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Exterior View</label>
                                @if($warehouse->exterior_image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $warehouse->exterior_image) }}" class="photo-preview" alt="Exterior View">
                                        <small class="text-muted d-block">Current image</small>
                                    </div>
                                @endif
                                <div class="upload-area" onclick="document.getElementById('exterior_image').click()">
                                    <div id="exteriorPreview" class="d-flex align-items-center justify-content-center" style="min-height:80px;">
                                        <span class="text-muted">
                                            <i class="fas fa-cloud-upload-alt fa-2x d-block"></i>
                                            <small>Click to upload new image</small>
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
                            Legal Documents
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ownership Document</label>
                                @if($warehouse->ownership_document)
                                    <div class="current-file mb-2">
                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                        Current: {{ basename($warehouse->ownership_document) }}
                                        <a href="{{ route('documents.private.show', ['path' => $warehouse->ownership_document]) }}" target="_blank" class="btn btn-sm btn-outline-primary float-end">View</a>
                                    </div>
                                @endif
                                <input type="file" name="ownership_document" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                                       class="form-control">
                                <small class="text-muted">Max 10MB. PDF, DOC, JPG, PNG</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tax Document</label>
                                @if($warehouse->tax_document)
                                    <div class="current-file mb-2">
                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                        Current: {{ basename($warehouse->tax_document) }}
                                        <a href="{{ route('documents.private.show', ['path' => $warehouse->tax_document]) }}" target="_blank" class="btn btn-sm btn-outline-primary float-end">View</a>
                                    </div>
                                @endif
                                <input type="file" name="tax_document" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                                       class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Fire Safety Document</label>
                                @if($warehouse->fire_safety_document)
                                    <div class="current-file mb-2">
                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                        Current: {{ basename($warehouse->fire_safety_document) }}
                                        <a href="{{ route('documents.private.show', ['path' => $warehouse->fire_safety_document]) }}" target="_blank" class="btn btn-sm btn-outline-primary float-end">View</a>
                                    </div>
                                @endif
                                <input type="file" name="fire_safety_document" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                                       class="form-control">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Building Approval Document</label>
                                @if($warehouse->building_approval_document)
                                    <div class="current-file mb-2">
                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                        Current: {{ basename($warehouse->building_approval_document) }}
                                        <a href="{{ route('documents.private.show', ['path' => $warehouse->building_approval_document]) }}" target="_blank" class="btn btn-sm btn-outline-primary float-end">View</a>
                                    </div>
                                @endif
                                <input type="file" name="building_approval_document" 
                                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" 
                                       class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="card">
                        <div class="card-body">
                            <button type="submit" class="btn btn-warning w-100 py-3 fw-bold">
                                <i class="fas fa-save me-2"></i>Update Warehouse
                            </button>
                            <div class="d-flex justify-content-between mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Fields with <span class="text-danger">*</span> required
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-lock me-1"></i>Secure
                                </small>
                            </div>
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
            preview.innerHTML = `<img src="${e.target.result}" class="photo-preview">`;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// ============================================================
// MAP FUNCTIONALITY (Leaflet)
// ============================================================
let map, marker;

function initPropertyEditMap() {
    const mapEl = document.getElementById('map');
    if (!mapEl) return;
    if (typeof L === 'undefined') {
        setTimeout(initPropertyEditMap, 100);
        return;
    }

    if (mapEl._leaflet_id) {
        mapEl._leaflet_id = null;
        mapEl.innerHTML = '';
    }
    
    const defaultLat = parseFloat(document.getElementById('latitude').value) || 27.7172;
    const defaultLng = parseFloat(document.getElementById('longitude').value) || 85.3240;
    
    map = L.map(mapEl, {
        zoomControl: true,
        scrollWheelZoom: false
    }).setView([defaultLat, defaultLng], 13);
    mapEl._leaflet_map = map;
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const resize = () => { map.invalidateSize(); };
    setTimeout(resize, 80);
    setTimeout(resize, 250);
    setTimeout(resize, 600);
    window.addEventListener('resize', resize);
    
    marker = L.marker([defaultLat, defaultLng], {
        draggable: true
    }).addTo(map);
    
    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        document.getElementById('latitude').value = position.lat.toFixed(8);
        document.getElementById('longitude').value = position.lng.toFixed(8);
        reverseGeocode(position.lat, position.lng);
    });
    
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        marker.setLatLng(e.latlng);
        document.getElementById('latitude').value = lat.toFixed(8);
        document.getElementById('longitude').value = lng.toFixed(8);
        reverseGeocode(lat, lng);
    });
}

if (document.readyState !== 'loading') {
    initPropertyEditMap();
} else {
    document.addEventListener('DOMContentLoaded', initPropertyEditMap);
}
document.addEventListener('kwdc:page-loaded', initPropertyEditMap);

function reverseGeocode(lat, lng) {
    const url = `/maps/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data && data.display_name) {
                document.getElementById('location').value = data.display_name;
                document.getElementById('address').value = data.display_name;
            }
        })
        .catch(error => console.error('Geocoding error:', error));
}

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
    
    const cctvCount = document.getElementById('cctv_count');
    if (cctvCount && parseInt(cctvCount.value) > 0) {
        toggleCCTVUrls(cctvCount.value);
    }
});
</script>
@endsection
