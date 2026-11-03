@extends('layouts.app')

@section('title', 'Edit Warehouse')
@section('header', 'Edit Warehouse Details')

@section('content')
<style>
    #map { height: 400px; border-radius: 12px; z-index: 1; }
    .image-preview { 
        width: 100px; 
        height: 100px; 
        object-fit: cover; 
        border-radius: 8px;
        margin-top: 8px;
    }
    .preview-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 10px;
    }
    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1f2937;
        border-bottom: 2px solid #f59e0b;
        padding-bottom: 8px;
        margin-bottom: 16px;
    }
</style>

<div class="bg-white rounded-xl shadow-md p-6">
    @if($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('warehouses.update', $warehouse->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <!-- ==================== BASIC INFORMATION ==================== -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="section-title">📋 Basic Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Warehouse Name *</label>
                    <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" required class="w-full px-4 py-2 border rounded-lg focus:border-orange-500">
                </div>
                
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Warehouse Type *</label>
                    <select name="warehouse_type" id="warehouse_type" required class="w-full px-4 py-2 border rounded-lg focus:border-orange-500">
                        <option value="building" {{ $warehouse->warehouse_type == 'building' ? 'selected' : '' }}>🏢 Building/Warehouse</option>
                        <option value="plot_land" {{ $warehouse->warehouse_type == 'plot_land' ? 'selected' : '' }}>🌿 Plot of Land/Open Space</option>
                        <option value="cold_storage" {{ $warehouse->warehouse_type == 'cold_storage' ? 'selected' : '' }}>❄️ Cold Storage Facility</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- ==================== LOCATION & MAP ==================== -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="section-title">📍 Location & Map</h3>
            
            <!-- Search and Current Location -->
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Find Location</label>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" id="search_location" placeholder="Search by address, landmark, or area..." 
                           class="flex-1 px-4 py-2 border rounded-lg focus:border-orange-500">
                    <button type="button" id="search_btn" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">
                        <i class="fas fa-search mr-1"></i> Search
                    </button>
                    <button type="button" id="current_location_btn" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition">
                        <i class="fas fa-location-dot mr-1"></i> Current Location
                    </button>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Address/Location *</label>
                <input type="text" name="location" id="address" value="{{ old('location', $warehouse->location ?? $warehouse->address) }}" required class="w-full px-4 py-2 border rounded-lg focus:border-orange-500">
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Latitude</label>
                    <input type="text" name="latitude" id="latitude" value="{{ old('latitude', $warehouse->latitude) }}" readonly class="w-full px-4 py-2 border rounded-lg bg-gray-100">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Longitude</label>
                    <input type="text" name="longitude" id="longitude" value="{{ old('longitude', $warehouse->longitude) }}" readonly class="w-full px-4 py-2 border rounded-lg bg-gray-100">
                </div>
            </div>
            
            <div id="map"></div>
            <p class="text-xs text-gray-500 mt-2">📍 Click on map to set location, drag marker to adjust, or use search/current location</p>
        </div>
        
        <!-- ==================== AREA & CAPACITY ==================== -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="section-title">📐 Area & Capacity</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Total Area (Sq. Ft.)</label>
                    <input type="number" step="0.01" name="total_area_sqft" id="total_area_sqft" value="{{ old('total_area_sqft', $warehouse->total_area_sqft) }}" class="w-full px-4 py-2 border rounded-lg">
                    <p class="text-xs text-gray-500 mt-1">Auto-converts to sq meters</p>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Total Area (Sq. Meters)</label>
                    <input type="number" step="0.01" name="total_area_sqm" id="total_area_sqm" value="{{ old('total_area_sqm', $warehouse->total_area_sqm) }}" class="w-full px-4 py-2 border rounded-lg bg-gray-50">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Built-up Area (Sq. Ft.)</label>
                    <input type="number" step="0.01" name="built_up_area" id="built_up_area" value="{{ old('built_up_area', $warehouse->built_up_area) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Built-up Area (Sq. Meters)</label>
                    <input type="number" step="0.01" id="built_up_area_sqm_display" value="{{ old('built_up_area_sqm', $warehouse->built_up_area_sqm) }}" class="w-full px-4 py-2 border rounded-lg bg-gray-50">
                    <input type="hidden" name="built_up_area_sqm" id="built_up_area_sqm" value="{{ old('built_up_area_sqm', $warehouse->built_up_area_sqm) }}">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Open Area (Sq. Ft.)</label>
                    <input type="number" step="0.01" name="open_area" id="open_area" value="{{ old('open_area', $warehouse->open_area) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Open Area (Sq. Meters)</label>
                    <input type="number" step="0.01" id="open_area_sqm_display" value="{{ old('open_area_sqm', $warehouse->open_area_sqm) }}" class="w-full px-4 py-2 border rounded-lg bg-gray-50">
                    <input type="hidden" name="open_area_sqm" id="open_area_sqm" value="{{ old('open_area_sqm', $warehouse->open_area_sqm) }}">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Usable/Allocatable Area (Sq. Ft.)</label>
                    <input type="number" step="0.01" name="usable_area" value="{{ old('usable_area', $warehouse->usable_area) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
            </div>
        </div>
        
        <!-- ==================== PRICING ==================== -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="section-title">💰 Pricing Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
    <label class="block text-gray-700 font-semibold mb-2">Price per Sq. Ft. (NPR) *</label>
    <input type="number" step="0.01" name="price" value="{{ old('price', $warehouse->price_per_unit) }}" required class="w-full px-4 py-2 border rounded-lg">
</div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Security Deposit (NPR)</label>
                    <input type="number" step="0.01" name="security_deposit" value="{{ old('security_deposit', $warehouse->security_deposit_fixed) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
            </div>
        </div>
        
        <!-- ==================== SECURITY FEATURES ==================== -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="section-title">🔒 Security Features</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Number of CCTV Cameras</label>
                    <input type="number" name="cctv_count" id="cctv_count" min="0" value="{{ old('cctv_count', $warehouse->cctv_count) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Number of Security Guards</label>
                    <input type="number" name="security_guard_count" min="0" value="{{ old('security_guard_count', $warehouse->security_guard_count) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Number of Fire Extinguishers</label>
                    <input type="number" name="fire_extinguisher_count" min="0" value="{{ old('fire_extinguisher_count', $warehouse->fire_extinguisher_count) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
            </div>
            
            <!-- Dynamic CCTV URL Fields -->
            <div id="cctv_urls_container" class="mt-4 {{ $warehouse->cctv_count > 0 ? '' : 'hidden' }}">
                <label class="block text-gray-700 font-semibold mb-2">📹 CCTV Camera Stream URLs</label>
                <div id="cctv_urls_list" class="space-y-2"></div>
                <p class="text-xs text-gray-500 mt-1">Enter RTSP or HTTP/HTTPS stream link for each CCTV camera</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="has_fire_alarm" value="1" {{ $warehouse->has_fire_alarm ? 'checked' : '' }} class="w-4 h-4">
                        <span class="ml-2">🔥 Fire Alarm System</span>
                    </label>
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="has_sprinkler_system" value="1" {{ $warehouse->has_sprinkler_system ? 'checked' : '' }} class="w-4 h-4">
                        <span class="ml-2">💧 Sprinkler System</span>
                    </label>
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="has_generator_backup" value="1" {{ $warehouse->has_generator_backup ? 'checked' : '' }} class="w-4 h-4">
                        <span class="ml-2">⚡ Generator Backup</span>
                    </label>
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="has_loading_dock" value="1" {{ $warehouse->has_loading_dock ? 'checked' : '' }} class="w-4 h-4">
                        <span class="ml-2">🚚 Loading Dock</span>
                    </label>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-gray-700 font-semibold mb-2">Additional Security Features</label>
                <textarea name="security_features" rows="2" class="w-full px-4 py-2 border rounded-lg">{{ old('security_features', $warehouse->security_features) }}</textarea>
            </div>
        </div>
        
        <!-- ==================== COLD STORAGE (conditional) ==================== -->
        <div id="cold_storage_fields" class="bg-gray-50 rounded-lg p-4 mb-6 {{ $warehouse->warehouse_type == 'cold_storage' ? '' : 'hidden' }}">
            <h3 class="section-title">❄️ Cold Storage Specifications</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Temperature Range (Min °C)</label>
                    <input type="number" step="0.1" name="temperature_range_min" value="{{ old('temperature_range_min', $warehouse->temperature_range_min) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Temperature Range (Max °C)</label>
                    <input type="number" step="0.1" name="temperature_range_max" value="{{ old('temperature_range_max', $warehouse->temperature_range_max) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="has_humidity_control" value="1" {{ $warehouse->has_humidity_control ? 'checked' : '' }} class="w-4 h-4">
                        <span class="ml-2">💧 Humidity Control</span>
                    </label>
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" name="has_backup_cooling" value="1" {{ $warehouse->has_backup_cooling ? 'checked' : '' }} class="w-4 h-4">
                        <span class="ml-2">❄️ Backup Cooling System</span>
                    </label>
                </div>
            </div>
        </div>
        
        <!-- ==================== NEARBY FACILITIES ==================== -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="section-title">🏥 Nearby Facilities</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">👮 Nearest Police Station</label>
                    <input type="text" name="nearest_police" value="{{ old('nearest_police', $warehouse->nearest_police) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">🚒 Nearest Fire Station</label>
                    <input type="text" name="nearest_fire_station" value="{{ old('nearest_fire_station', $warehouse->nearest_fire_station) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">🏥 Nearest Hospital</label>
                    <input type="text" name="nearest_hospital" value="{{ old('nearest_hospital', $warehouse->nearest_hospital) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">🏦 Nearest Bank</label>
                    <input type="text" name="nearest_bank" value="{{ old('nearest_bank', $warehouse->nearest_bank) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">⛽ Nearest Fuel Station</label>
                    <input type="text" name="nearest_fuel_station" value="{{ old('nearest_fuel_station', $warehouse->nearest_fuel_station) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">🍽️ Nearest Market/Restaurant</label>
                    <input type="text" name="nearest_market" value="{{ old('nearest_market', $warehouse->nearest_market) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
            </div>
        </div>
        
        <!-- ==================== CONTACT INFORMATION ==================== -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="section-title">📞 Contact Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Contact Person</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $warehouse->contact_person) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Contact Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $warehouse->contact_phone) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Contact Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $warehouse->contact_email) }}" class="w-full px-4 py-2 border rounded-lg">
                </div>
            </div>
        </div>
        
        <!-- ==================== ADDITIONAL INFORMATION ==================== -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="section-title">📝 Additional Information</h3>
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-gray-700 font-semibold mb-0">Description</label>
                    <button type="button" class="text-xs px-2.5 py-1 rounded bg-orange-100 hover:bg-orange-200 text-orange-800 font-semibold transition border border-orange-200" id="btnAiWarehouseEditCopy" onclick="generateAiWarehouseEditCopy()">
                        <i class="fas fa-wand-magic-sparkles me-1 text-orange-600"></i> AI Generate Description
                    </button>
                </div>
                <textarea name="description" id="warehouse_edit_description" rows="5" class="w-full px-4 py-2 border rounded-lg">{{ old('description', $warehouse->description) }}</textarea>
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-2">⭐ Special Features / Amenities</label>
                <textarea name="special_features" rows="2" class="w-full px-4 py-2 border rounded-lg">{{ old('special_features', $warehouse->special_features) }}</textarea>
            </div>
            <div class="mt-4">
                <label class="block text-gray-700 font-semibold mb-2">⚠️ Restrictions (if any)</label>
                <textarea name="restrictions" rows="2" class="w-full px-4 py-2 border rounded-lg">{{ old('restrictions', $warehouse->restrictions) }}</textarea>
            </div>
        </div>
        
        <!-- Status -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="section-title">📊 Status</h3>
            <div>
                <label class="block text-gray-700 font-semibold mb-2">Status</label>
                <select name="status" class="w-full px-4 py-2 border rounded-lg">
                    <option value="pending" {{ $warehouse->status == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                    <option value="approved" {{ $warehouse->status == 'approved' ? 'selected' : '' }}>✅ Approved</option>
                    <option value="rejected" {{ $warehouse->status == 'rejected' ? 'selected' : '' }}>❌ Rejected</option>
                </select>
            </div>
        </div>
        
        <div class="flex justify-end space-x-4">
            <a href="{{ route('warehouses.index') }}" class="px-6 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
                <i class="fas fa-save mr-2"></i> Update Warehouse
            </button>
        </div>
    </form>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script>
    let map, marker;
    
    // Initialize map with existing coordinates or default
    const existingLat = {{ $warehouse->latitude ?? 27.7172 }};
    const existingLng = {{ $warehouse->longitude ?? 85.3240 }};
    
    function initMap(lat = existingLat, lng = existingLng) {
        const mapEl = document.getElementById('map');
        if (!mapEl) return;
        if (typeof L === 'undefined') {
            setTimeout(() => initMap(lat, lng), 100);
            return;
        }

        if (mapEl._leaflet_id && !map) {
            mapEl._leaflet_id = null;
            mapEl.innerHTML = '';
        }

        if (map) {
            map.setView([lat, lng], 15);
        } else {
            map = L.map(mapEl, {
                zoomControl: true,
                scrollWheelZoom: false
            }).setView([lat, lng], 15);
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
        }
        
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        
        marker.on('dragend', async function(e) {
            const pos = marker.getLatLng();
            document.getElementById('latitude').value = pos.lat.toFixed(6);
            document.getElementById('longitude').value = pos.lng.toFixed(6);
            await getAddress(pos.lat, pos.lng);
        });
        
        map.on('click', async function(e) {
            const lat = e.latlng.lat, lng = e.latlng.lng;
            document.getElementById('latitude').value = lat.toFixed(6);
            document.getElementById('longitude').value = lng.toFixed(6);
            if (marker) map.removeLayer(marker);
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            await getAddress(lat, lng);
        });
    }
    
    async function getAddress(lat, lng) {
        try {
            const res = await fetch(`/maps/reverse?lat=${lat}&lon=${lng}&format=json`);
            const data = await res.json();
            if (data.display_name) document.getElementById('address').value = data.display_name;
        } catch(e) { console.error(e); }
    }
    
    async function geocodeAddress(address) {
        try {
            const response = await fetch(`/maps/search?q=${encodeURIComponent(address)},Nepal&format=json&limit=1`);
            const data = await response.json();
            if (data && data.length > 0) {
                return { lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon) };
            }
            return null;
        } catch (error) {
            console.error('Geocoding error:', error);
            return null;
        }
    }
    
    // Search functionality
    const searchBtn = document.getElementById('search_btn');
    const currentLocationBtn = document.getElementById('current_location_btn');
    const searchInput = document.getElementById('search_location');
    
    if (searchBtn) {
        searchBtn.addEventListener('click', async () => {
            const address = searchInput.value.trim();
            if (!address) {
                alert('Please enter a location to search');
                return;
            }
            
            const coords = await geocodeAddress(address);
            if (coords) {
                document.getElementById('latitude').value = coords.lat.toFixed(6);
                document.getElementById('longitude').value = coords.lng.toFixed(6);
                initMap(coords.lat, coords.lng);
                await getAddress(coords.lat, coords.lng);
            } else {
                alert('Location not found. Please try a different address.');
            }
        });
    }
    
    if (currentLocationBtn) {
        currentLocationBtn.addEventListener('click', () => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(async (position) => {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    document.getElementById('latitude').value = lat.toFixed(6);
                    document.getElementById('longitude').value = lng.toFixed(6);
                    initMap(lat, lng);
                    await getAddress(lat, lng);
                    if (document.getElementById('address').value) {
                        searchInput.value = document.getElementById('address').value.substring(0, 50);
                    }
                }, (error) => {
                    alert('Unable to get your location. Please check your browser permissions.');
                });
            } else {
                alert('Geolocation is not supported by your browser');
            }
        });
    }
    
    // Area conversion
    const SQFT_TO_SQM = 0.092903;
    const totalSqft = document.getElementById('total_area_sqft');
    const totalSqm = document.getElementById('total_area_sqm');
    
    if (totalSqft) {
        totalSqft.addEventListener('input', function() {
            const sqft = parseFloat(this.value);
            if (!isNaN(sqft) && sqft > 0 && totalSqm) {
                totalSqm.value = (sqft * SQFT_TO_SQM).toFixed(2);
            }
        });
    }
    
    // Dynamic CCTV URLs
    const cctvCountInput = document.getElementById('cctv_count');
    const cctvUrlsContainer = document.getElementById('cctv_urls_container');
    const cctvUrlsList = document.getElementById('cctv_urls_list');
    
    function generateCCTVUrlFields() {
        const count = parseInt(cctvCountInput.value) || 0;
        const existingUrls = @json($warehouse->cctv_urls ? json_decode($warehouse->cctv_urls, true) : []);
        
        if (count > 0) {
            cctvUrlsContainer.classList.remove('hidden');
            cctvUrlsList.innerHTML = '';
            
            for (let i = 1; i <= count; i++) {
                const existingValue = existingUrls[i] || '';
                const div = document.createElement('div');
                div.className = 'flex flex-col sm:flex-row gap-2 items-start sm:items-center';
                div.innerHTML = `
                    <label class="text-gray-700 font-medium text-sm w-24">Camera ${i}:</label>
                    <input type="url" name="cctv_urls[${i}]" class="flex-1 px-4 py-2 border rounded-lg" 
                           placeholder="rtsp:// or https:// camera ${i} stream URL" value="${existingValue}">
                    <button type="button" class="test-camera-btn bg-blue-100 text-blue-600 px-3 py-2 rounded-lg hover:bg-blue-200 transition text-sm" data-url-input="cctv_urls_${i}">
                        <i class="fas fa-video"></i> Test
                    </button>
                `;
                div.querySelector('input').id = `cctv_urls_${i}`;
                cctvUrlsList.appendChild(div);
            }
            
            document.querySelectorAll('.test-camera-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const inputId = this.getAttribute('data-url-input');
                    const urlInput = document.getElementById(inputId);
                    if (urlInput && urlInput.value) {
                        testCameraStream(urlInput.value);
                    } else {
                        alert('Please enter a camera URL first');
                    }
                });
            });
        } else {
            cctvUrlsContainer.classList.add('hidden');
            cctvUrlsList.innerHTML = '';
        }
    }
    
    function testCameraStream(url) {
        if (url.startsWith('rtsp://')) {
            alert('RTSP stream detected.\n\nThis requires a media player like VLC to view.\n\nStream URL: ' + url);
        } else if (url.startsWith('http://') || url.startsWith('https://')) {
            window.open(url, '_blank', 'width=800,height=600');
        } else {
            alert('Invalid URL format. Please enter a valid RTSP or HTTP/HTTPS URL.');
        }
    }
    
    if (cctvCountInput) {
        cctvCountInput.addEventListener('input', generateCCTVUrlFields);
        // Generate initial fields if count > 0
        if (parseInt(cctvCountInput.value) > 0) {
            generateCCTVUrlFields();
        }
    }
    
    // Cold storage toggle
    document.getElementById('warehouse_type').addEventListener('change', function() {
        const coldFields = document.getElementById('cold_storage_fields');
        if (this.value === 'cold_storage') {
            coldFields.classList.remove('hidden');
        } else {
            coldFields.classList.add('hidden');
        }
    });

    async function generateAiWarehouseEditCopy() {
        const name = document.querySelector('input[name="name"]')?.value || 'Kathmandu Warehouse';
        const address = document.querySelector('input[name="address"]')?.value || 'Kathmandu';
        const city = document.querySelector('input[name="city"]')?.value || '';
        const sqft = document.querySelector('input[name="total_sqft"]')?.value || document.querySelector('input[name="area_sqft"]')?.value || 5000;
        const price = document.querySelector('input[name="price_per_sqft"]')?.value || document.querySelector('input[name="price"]')?.value || 35;

        const btn = document.getElementById('btnAiWarehouseEditCopy');
        const descArea = document.getElementById('warehouse_edit_description') || document.querySelector('textarea[name="description"]');
        
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
                btn.innerHTML = '<i class="fas fa-wand-magic-sparkles me-1 text-orange-600"></i> AI Generate Description';
            }
        }
    }
    
    initMap();
    document.addEventListener('kwdc:page-loaded', function() {
        map = null;
        initMap();
    });
</script>
@endpush
@endsection
