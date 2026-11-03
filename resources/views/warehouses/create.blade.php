@extends('layouts.app')

@section('title', 'Add New Warehouse')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-warehouse me-2"></i>Register New Warehouse
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('warehouses.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- ============================================================ -->
                        <!-- BASIC INFORMATION SECTION -->
                        <!-- ============================================================ -->
                        <div class="bg-light p-3 rounded mb-4">
                            <h6 class="border-bottom pb-2 mb-3 fw-bold">
                                <i class="fas fa-info-circle text-primary me-2"></i>Basic Information
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Warehouse Name <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control" 
                                               value="{{ old('name') }}" placeholder="e.g., Central Warehouse" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                                        <input type="text" name="location" class="form-control" 
                                               value="{{ old('location') }}" placeholder="e.g., Kathmandu" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Property Owner <span class="text-danger">*</span></label>
                                        <select name="owner_id" class="form-select" required>
                                            <option value="">-- Select Property Owner --</option>
                                            @foreach($propertyOwners ?? [] as $owner)
                                                <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                                                    {{ $owner->name }} ({{ $owner->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Contact Number</label>
                                        <input type="text" name="contact_phone" class="form-control" 
                                               value="{{ old('contact_phone') }}" placeholder="e.g., 98XXXXXXXX">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================================ -->
                        <!-- ADDRESS DETAILS SECTION -->
                        <!-- ============================================================ -->
                        <div class="bg-light p-3 rounded mb-4">
                            <h6 class="border-bottom pb-2 mb-3 fw-bold">
                                <i class="fas fa-map-marker-alt text-success me-2"></i>Address Details
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Street Address <span class="text-danger">*</span></label>
                                        <input type="text" name="address" class="form-control" 
                                               value="{{ old('address') }}" placeholder="e.g., Baneshwor-10" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">City <span class="text-danger">*</span></label>
                                        <input type="text" name="city" class="form-control" 
                                               value="{{ old('city') }}" placeholder="e.g., Kathmandu" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">State/Province</label>
                                        <input type="text" name="state" class="form-control" 
                                               value="{{ old('state') }}" placeholder="e.g., Bagmati">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Country</label>
                                        <input type="text" name="country" class="form-control" value="Nepal" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Postal Code</label>
                                        <input type="text" name="postal_code" class="form-control" 
                                               value="{{ old('postal_code') }}" placeholder="e.g., 44600">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================================ -->
                        <!-- KATAHO LOCATION SECTION -->
                        <!-- ============================================================ -->
                        <div class="border border-warning p-3 rounded mb-4 bg-white">
                            <h6 class="border-bottom pb-2 mb-3 fw-bold text-warning">
                                <i class="fas fa-map-pin text-warning me-2"></i>📍 Kataho Location (Recommended)
                                <span class="badge bg-info text-white ms-2">Precise Location</span>
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Kataho Code</label>
                                        <div class="input-group">
                                            <input type="text" name="kataho_code" id="kataho_code" 
                                                   class="form-control" 
                                                   value="{{ old('kataho_code') }}" 
                                                   placeholder="e.g., KH-1234-5678">
                                            <button type="button" class="btn btn-warning text-white" id="verifyKatahoBtn">
                                                <i class="fas fa-check-circle me-1"></i>Verify
                                            </button>
                                            <a href="https://kataho.app" target="_blank" class="btn btn-outline-secondary">
                                                <i class="fas fa-external-link-alt me-1"></i>Find
                                            </a>
                                        </div>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Each 3.5m x 2.8m grid in Nepal has a unique Kataho code.
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Grid ID</label>
                                        <input type="text" name="kataho_grid_id" id="kataho_grid_id" 
                                               class="form-control" readonly
                                               value="{{ old('kataho_grid_id') }}" 
                                               placeholder="Auto-filled">
                                    </div>
                                </div>
                            </div>

                            <div id="katahoVerificationResult" style="display: none;">
                                <div class="alert" role="alert">
                                    <i class="fas me-2"></i>
                                    <span id="verificationMessage"></span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Latitude</label>
                                        <input type="number" name="latitude" id="latitude" 
                                               class="form-control" 
                                               value="{{ old('latitude') }}" step="any" placeholder="27.7172">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Longitude</label>
                                        <input type="number" name="longitude" id="longitude" 
                                               class="form-control" 
                                               value="{{ old('longitude') }}" step="any" placeholder="85.3240">
                                    </div>
                                </div>
                            </div>

                            <div class="kwdc-map-panel mt-2">
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <div>
                                        <strong>Map Preview</strong>
                                        <small class="d-block text-muted">Search by address, click the map, or drag the pin.</small>
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" id="locateWarehouseBtn">
                                        <i class="fas fa-location-crosshairs me-1"></i>Use my location
                                    </button>
                                </div>
                                <div id="warehouseCreateMap" class="kwdc-location-map"></div>
                            </div>
                        </div>

                        <!-- ============================================================ -->
                        <!-- WAREHOUSE DETAILS SECTION -->
                        <!-- ============================================================ -->
                        <div class="bg-light p-3 rounded mb-4">
                            <h6 class="border-bottom pb-2 mb-3 fw-bold">
                                <i class="fas fa-warehouse text-primary me-2"></i>Warehouse Details
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Total Area (sq ft) <span class="text-danger">*</span></label>
                                        <input type="number" name="area_sqft" class="form-control" 
                                               value="{{ old('area_sqft') }}" step="0.01" placeholder="500" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Usable Area (sq ft)</label>
                                        <input type="number" name="usable_area" class="form-control" 
                                               value="{{ old('usable_area') }}" step="0.01" placeholder="450">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Price per sq ft (NPR) <span class="text-danger">*</span></label>
                                        <input type="number" name="price" class="form-control" 
                                               value="{{ old('price') }}" step="0.01" placeholder="30" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                        <select name="status" class="form-select" required>
                                            <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Facilities</label>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input type="checkbox" name="cold_storage" class="form-check-input" id="cold_storage" value="1">
                                                    <label class="form-check-label" for="cold_storage">
                                                        <i class="fas fa-snowflake text-primary"></i> Cold Storage
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-check">
                                                    <input type="checkbox" name="humidity_control" class="form-check-input" id="humidity_control" value="1">
                                                    <label class="form-check-label" for="humidity_control">
                                                        <i class="fas fa-tint text-info"></i> Humidity Control
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================================ -->
                        <!-- DESCRIPTION SECTION -->
                        <!-- ============================================================ -->
                        <div class="bg-light p-3 rounded mb-4">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                <h6 class="mb-0 fw-bold">
                                    <i class="fas fa-file-alt text-warning me-2"></i>Description & Highlights
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary" id="btnAiWarehouseCopy" onclick="generateAiWarehouseCopy()">
                                    <i class="fas fa-wand-magic-sparkles me-1"></i> AI Generate Description
                                </button>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" id="warehouse_description" class="form-control" 
                                          rows="6" placeholder="Describe the warehouse, including any special features...">{{ old('description') }}</textarea>
                                <small class="text-muted">Tip: Click 'AI Generate Description' to automatically compose a commercial real estate listing from your location and specs.</small>
                            </div>
                        </div>

                        <!-- ============================================================ -->
                        <!-- SUBMIT BUTTONS -->
                        <!-- ============================================================ -->
                        <div class="mt-4 border-top pt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-2"></i>Add Warehouse
                            </button>
                            <a href="{{ route('admin.all-warehouses') }}" class="btn btn-secondary px-4">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .bg-light {
        background-color: #f8f9fa !important;
    }
    .border-warning {
        border-color: #f59e0b !important;
        border-width: 2px !important;
    }
    .btn-warning {
        background: #f59e0b !important;
        border-color: #f59e0b !important;
        color: white !important;
    }
    .btn-warning:hover {
        background: #d97706 !important;
        border-color: #d97706 !important;
        color: white !important;
    }
    .text-warning {
        color: #f59e0b !important;
    }
    .form-label.fw-semibold {
        font-weight: 600;
    }
    .card-header {
        border-bottom: 2px solid #f59e0b;
    }
    .text-primary {
        color: #f59e0b !important;
    }
    .btn-primary {
        background: #f59e0b;
        border-color: #f59e0b;
    }
    .btn-primary:hover {
        background: #d97706;
        border-color: #d97706;
    }
    .kwdc-map-panel {
        border: 1px solid #e4eaf2;
        border-radius: 24px;
        padding: 18px;
        background: #ffffff;
    }
    .kwdc-location-map {
        height: 360px;
        min-height: 320px;
        border-radius: 22px;
        overflow: hidden;
        border: 1px solid #dbe4ef;
        background: #eef2f7;
        z-index: 1;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const verifyBtn = document.getElementById('verifyKatahoBtn');
    const katahoCodeInput = document.getElementById('kataho_code');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const mapEl = document.getElementById('warehouseCreateMap');
    const addressFields = [
        document.querySelector('[name="address"]'),
        document.querySelector('[name="city"]'),
        document.querySelector('[name="location"]')
    ].filter(Boolean);
    let warehouseMap = null;
    let warehouseMarker = null;
    let locationRevision = 0;

    function setWarehousePoint(lat, lng, zoom = 16) {
        if (!warehouseMap) return;
        if (!Number.isFinite(Number(lat)) || !Number.isFinite(Number(lng)) || Math.abs(lat) > 90 || Math.abs(lng) > 180) return;
        locationRevision++;
        latInput.value = Number(lat).toFixed(8);
        lngInput.value = Number(lng).toFixed(8);
        if (warehouseMarker) {
            warehouseMarker.setLatLng([lat, lng]);
        } else {
            warehouseMarker = L.marker([lat, lng], { draggable: true }).addTo(warehouseMap);
            warehouseMarker.on('dragend', function(event) {
                const point = event.target.getLatLng();
                setWarehousePoint(point.lat, point.lng, warehouseMap.getZoom());
            });
        }
        warehouseMap.setView([lat, lng], zoom);
    }

    async function geocodeWarehouse() {
        const revision = ++locationRevision;
        const query = addressFields.map(field => field.value.trim()).filter(Boolean).join(', ');
        latInput.value = '';
        lngInput.value = '';
        if (warehouseMarker) { warehouseMap.removeLayer(warehouseMarker); warehouseMarker = null; }
        if (query.length < 4) return;
        try {
            const point = await KwdcMaps.search(query);
            if (revision !== locationRevision) return;
            if (point) setWarehousePoint(point.lat, point.lng);
            else mapEl.setAttribute('aria-label', 'Location not found. Choose a point on the map.');
        } catch (error) {
            console.warn('Warehouse geocoding failed:', error);
        }
    }

    function debounce(callback, wait = 700) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(callback, wait);
        };
    }

    if (mapEl && typeof L !== 'undefined') {
        if (mapEl._leaflet_id) {
            mapEl._leaflet_id = null;
            mapEl.innerHTML = '';
        }
        const startLat = parseFloat(latInput.value) || 27.7172;
        const startLng = parseFloat(lngInput.value) || 85.3240;
        warehouseMap = L.map(mapEl, {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView([startLat, startLng], 13);
        mapEl._leaflet_map = warehouseMap;
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(warehouseMap);

        const resize = () => { warehouseMap.invalidateSize(); };
        setTimeout(resize, 80);
        setTimeout(resize, 250);
        setTimeout(resize, 600);
        window.addEventListener('resize', resize);

        if (latInput.value && lngInput.value) {
            setWarehousePoint(startLat, startLng, 15);
        }

        warehouseMap.on('click', function(event) {
            setWarehousePoint(event.latlng.lat, event.latlng.lng);
        });

        addressFields.forEach(field => {
            field.addEventListener('input', debounce(geocodeWarehouse));
            field.addEventListener('blur', geocodeWarehouse);
        });

        latInput.addEventListener('change', function() {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!Number.isNaN(lat) && !Number.isNaN(lng)) setWarehousePoint(lat, lng);
        });
        lngInput.addEventListener('change', function() {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            if (!Number.isNaN(lat) && !Number.isNaN(lng)) setWarehousePoint(lat, lng);
        });

        document.getElementById('locateWarehouseBtn')?.addEventListener('click', function() {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition(function(position) {
                setWarehousePoint(position.coords.latitude, position.coords.longitude);
            }, function() {
                const message = document.createElement('p');
                message.setAttribute('role', 'status');
                message.textContent = 'Location access is unavailable. Enter an address or choose a point on the map.';
                mapEl.after(message);
            }, {timeout: 10000});
        });
    }
    
    if (verifyBtn) {
        verifyBtn.addEventListener('click', function() {
            const code = katahoCodeInput.value.trim();
            if (!code) {
                alert('Please enter a Kataho code.');
                return;
            }
            
            verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Verifying...';
            verifyBtn.disabled = true;
            
            fetch('{{ route("warehouses.verify-kataho") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ kataho_code: code })
            })
            .then(response => response.json())
            .then(data => {
                const resultDiv = document.getElementById('katahoVerificationResult');
                const messageEl = document.getElementById('verificationMessage');
                const alertDiv = resultDiv.querySelector('.alert');
                
                resultDiv.style.display = 'block';
                
                if (data.valid) {
                    alertDiv.className = 'alert alert-success';
                    alertDiv.querySelector('i').className = 'fas fa-check-circle me-2';
                    messageEl.textContent = '✅ Verified: ' + (data.location?.address || data.location?.place_name || 'Location found');
                    
                    if (data.location?.grid_id) {
                        document.getElementById('kataho_grid_id').value = data.location.grid_id;
                    }
                    if (data.location?.latitude) {
                        document.getElementById('latitude').value = data.location.latitude;
                    }
                    if (data.location?.longitude) {
                        document.getElementById('longitude').value = data.location.longitude;
                    }
                    if (data.location?.latitude && data.location?.longitude) {
                        setWarehousePoint(data.location.latitude, data.location.longitude);
                    }
                } else {
                    alertDiv.className = 'alert alert-danger';
                    alertDiv.querySelector('i').className = 'fas fa-exclamation-circle me-2';
                    messageEl.textContent = '❌ Invalid Kataho code. Please verify and try again.';
                }
            })
            .catch(() => {
                const resultDiv = document.getElementById('katahoVerificationResult');
                const messageEl = document.getElementById('verificationMessage');
                const alertDiv = resultDiv.querySelector('.alert');
                
                resultDiv.style.display = 'block';
                alertDiv.className = 'alert alert-danger';
                alertDiv.querySelector('i').className = 'fas fa-exclamation-circle me-2';
                messageEl.textContent = '❌ Error verifying Kataho code. Please try again.';
            })
            .finally(() => {
                verifyBtn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Verify';
                verifyBtn.disabled = false;
            });
        });
    }
});

async function generateAiWarehouseCopy() {
    const name = document.querySelector('input[name="name"]')?.value || 'Kathmandu Logistics Hub';
    const city = document.querySelector('input[name="city"]')?.value || 'Kathmandu';
    const address = document.querySelector('input[name="address"]')?.value || city;
    const sqft = document.querySelector('input[name="area_sqft"]')?.value || 5000;
    const price = document.querySelector('input[name="price"]')?.value || 35;

    const btn = document.getElementById('btnAiWarehouseCopy');
    const descArea = document.getElementById('warehouse_description') || document.querySelector('textarea[name="description"]');
    
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Drafting...';
    }

    try {
        const response = await fetch('/ai/warehouse-copy', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: name,
                location: address ? `${address}, ${city}` : city,
                total_sqft: parseFloat(sqft) || 5000,
                price_per_sqft: parseFloat(price) || 35
            })
        });

        const res = await response.json();
        if (res.success && res.copy) {
            let fullText = `${res.copy.title}\n\n${res.copy.description}\n\nKey Facility Highlights:\n`;
            if (Array.isArray(res.copy.highlights)) {
                fullText += res.copy.highlights.map(h => `• ${h}`).join('\n');
            }
            if (res.copy.ideal_for) {
                fullText += `\n\nIdeal For: ${res.copy.ideal_for}`;
            }
            if (descArea) {
                descArea.value = fullText;
                descArea.focus();
            }
        } else {
            alert('Unable to generate copy right now.');
        }
    } catch (e) {
        console.error(e);
        alert('Failed to generate copy.');
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-wand-magic-sparkles me-1"></i> AI Generate Description';
        }
    }
}
</script>
@endpush
