@extends('layouts.app')

@section('title', 'New Warehouse Request')
@section('header', 'Find Your Ideal Warehouse')

@section('content')
<!-- Location Search Bar -->
<div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-xl p-6 mb-6 border border-orange-200">
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex-1">
            <div class="flex items-center mb-2">
                <i class="fas fa-map-marker-alt text-orange-500 mr-2"></i>
                <h3 class="font-semibold text-gray-800">Find Warehouses Near You</h3>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" id="location_search" placeholder="Enter your preferred location (e.g., Kathmandu, Lalitpur, Bhaktapur)" 
                           class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-orange-500">
                </div>
                <div class="w-32">
                    <select id="radius_select" class="w-full px-4 py-2 border rounded-lg">
                        <option value="5">5 km</option>
                        <option value="10" selected>10 km</option>
                        <option value="20">20 km</option>
                        <option value="30">30 km</option>
                        <option value="50">50 km</option>
                    </select>
                </div>
                <button id="search_location" class="bg-orange-500 text-white px-6 py-2 rounded-lg hover:bg-orange-600 transition">
                    <i class="fas fa-search mr-2"></i> Search
                </button>
                <button id="use_current_location" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-location-dot mr-2"></i> Use My Location
                </button>
            </div>
            <p id="search_status" class="text-sm text-gray-500 mt-2 hidden"></p>
        </div>
        <div class="text-center md:text-right">
            <p class="text-sm text-gray-600">
                <i class="fas fa-warehouse mr-1"></i> 
                <span id="warehouse_count">{{ count($warehouses) }}</span> warehouses found
            </p>
        </div>
    </div>
</div>

<!-- Save Preferred Location Checkbox -->
<div class="bg-white rounded-xl shadow-md p-4 mb-6">
    <label class="flex items-center cursor-pointer">
        <input type="checkbox" id="save_preferred_location" class="w-4 h-4 text-orange-500 rounded">
        <span class="ml-2 text-gray-700">
            <i class="fas fa-star text-orange-500 mr-1"></i> 
            Save this as my preferred location for future searches
        </span>
    </label>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Left Side: Warehouse Selection & Details -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">1. Select Warehouse</h3>
            
            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Choose a Warehouse</label>
                <select name="warehouse_id" id="warehouse_select" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-orange-500">
                    <option value="">-- Select a warehouse --</option>
                    @foreach($warehouses as $warehouse)
                    <option value="{{ $warehouse->id }}" 
                            data-name="{{ $warehouse->name }}"
                            data-location="{{ $warehouse->location }}"
                            data-price="{{ $warehouse->price }}"
                            data-area="{{ $warehouse->total_area }}"
                            data-security="{{ $warehouse->security_features }}"
                            data-description="{{ $warehouse->description }}"
                            data-lat="{{ $warehouse->latitude ?? 27.7172 }}"
                            data-lng="{{ $warehouse->longitude ?? 85.3240 }}"
                            data-police="{{ $warehouse->nearest_police ?? 'N/A' }}"
                            data-fire="{{ $warehouse->nearest_fire_station ?? 'N/A' }}"
                            data-hospital="{{ $warehouse->nearest_hospital ?? 'N/A' }}">
                        {{ $warehouse->name }} - {{ $warehouse->location }} 
                        @if(isset($warehouse->distance))
                        <span class="text-xs text-orange-500">({{ number_format($warehouse->distance, 1) }} km away)</span>
                        @endif
                        (रु {{ number_format($warehouse->price ?? 0) }}/sq ft)
                    </option>
                    @endforeach
                </select>
                @error('warehouse_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            
            @if($warehouses->isEmpty())
            <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4 text-center">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-2xl mb-2"></i>
                <p class="text-gray-700">No warehouses found in your preferred location.</p>
                <p class="text-sm text-gray-500 mt-1">Try increasing search radius or check back later.</p>
            </div>
            @endif
        </div>
        
        <!-- Warehouse Details Card -->
        <div id="warehouse_details" class="bg-white rounded-xl shadow-md p-6 hidden">
            <h3 class="text-lg font-bold text-gray-800 mb-4">2. Warehouse Details</h3>
            <div id="warehouse_info"></div>
        </div>
        
        <!-- Request Form -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">3. Request Details</h3>
            <form method="POST" action="{{ route('my-requests.store') }}" id="request_form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="warehouse_id" id="selected_warehouse_id">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Required Area (sq ft) *</label>
                        <input type="number" name="required_area" id="required_area" required class="w-full px-4 py-2 border rounded-lg" placeholder="Enter required area">
                        <p id="area_warning" class="text-yellow-600 text-sm mt-1 hidden"></p>
                        @error('required_area') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Duration (months) *</label>
                        <input type="number" name="duration_months" required class="w-full px-4 py-2 border rounded-lg" placeholder="Number of months">
                        @error('duration_months') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Estimated Budget (NPR)</label>
                        <input type="text" id="estimated_budget" readonly class="w-full px-4 py-2 border rounded-lg bg-gray-100">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Preferred Start Date</label>
                        <input type="date" name="preferred_start_date" class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div class="col-span-2 mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Purpose *</label>
                        <textarea name="purpose" rows="3" required class="w-full px-4 py-2 border rounded-lg" placeholder="Describe what you'll store and any special requirements..."></textarea>
                        @error('purpose') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Contact Person Name</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person', Auth::user()->name) }}" class="w-full px-4 py-2 border rounded-lg">
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Contact Phone</label>
                        <input type="text" name="contact_phone" value="{{ old('contact_phone', Auth::user()->phone) }}" class="w-full px-4 py-2 border rounded-lg">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Invoice Document</label>
                        <input type="file" name="invoice" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2 border rounded-lg bg-white">
                        @error('invoice') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Packing List</label>
                        <input type="file" name="packing_list" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2 border rounded-lg bg-white">
                        @error('packing_list') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Insurance Document</label>
                        <input type="file" name="insurance" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-4 py-2 border rounded-lg bg-white">
                        @error('insurance') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4 mt-4">
                    <a href="{{ route('my-requests.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
                        <i class="fas fa-paper-plane mr-2"></i> Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Right Side: Map & Nearby Facilities -->
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-md p-6 sticky top-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">📍 Warehouse Location Map</h3>
            <div id="map" style="height: 400px; border-radius: 12px; z-index: 1;"></div>
            <p class="text-xs text-gray-500 mt-2 text-center">📍 Showing warehouses near your preferred location</p>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">🏥 Nearby Emergency Services</h3>
            <div id="nearby_facilities" class="space-y-3">
                <p class="text-gray-500 text-center">Select a warehouse to view nearby facilities</p>
            </div>
        </div>
        
        <div class="bg-blue-50 rounded-xl p-4 border-l-4 border-blue-500">
            <div class="flex items-start">
                <i class="fas fa-shield-alt text-blue-500 text-xl mr-3 mt-1"></i>
                <div>
                    <p class="font-semibold text-blue-800">Safety Tips</p>
                    <p class="text-sm text-blue-700">Always verify warehouse documents before finalizing. KTM-WDC verifies all listed warehouses.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .facility-item {
        padding: 10px;
        border-radius: 8px;
        background: #f9fafb;
    }
    .facility-icon {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .police { background: #dbeafe; color: #2563eb; }
    .fire { background: #fee2e2; color: #dc2626; }
    .hospital { background: #dcfce7; color: #16a34a; }
</style>
@endpush

@push('scripts')
<script>
    let map;
    let currentMarker = null;
    
    // Initialize map
    function initMap(lat = 27.7172, lng = 85.3240) {
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
            map.setView([lat, lng], 13);
        } else {
            map = L.map(mapEl, {
                zoomControl: true,
                scrollWheelZoom: false
            }).setView([lat, lng], 13);
            mapEl._leaflet_map = map;
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            const resize = () => { map.invalidateSize(); };
            setTimeout(resize, 80);
            setTimeout(resize, 250);
            setTimeout(resize, 600);
            window.addEventListener('resize', resize);
        }
    }
    
    // Location Search Elements
    const locationSearch = document.getElementById('location_search');
    const radiusSelect = document.getElementById('radius_select');
    const searchBtn = document.getElementById('search_location');
    const useCurrentBtn = document.getElementById('use_current_location');
    const searchStatus = document.getElementById('search_status');
    const savePreferredCheckbox = document.getElementById('save_preferred_location');
    
    // Geocoding function
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
    
    // Search warehouses by location
    async function searchByLocation(lat, lng, radius, locationName) {
        if (searchStatus) {
            searchStatus.classList.remove('hidden');
            searchStatus.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Searching for warehouses...';
        }
        
        const url = new URL(window.location.href);
        url.searchParams.set('lat', lat);
        url.searchParams.set('lng', lng);
        url.searchParams.set('radius', radius);
        
        if (savePreferredCheckbox && savePreferredCheckbox.checked && locationName) {
            await savePreferredLocation(lat, lng, locationName);
        }
        
        window.location.href = url.toString();
    }
    
    // Save preferred location
    async function savePreferredLocation(lat, lng, locationName) {
        try {
            await fetch('{{ route("save-preferred-location") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    latitude: lat,
                    longitude: lng,
                    location_name: locationName,
                    radius: parseInt(radiusSelect ? radiusSelect.value : 10)
                })
            });
        } catch (error) {
            console.error('Error saving preferred location:', error);
        }
    }
    
    // Search button click
    if (searchBtn) {
        searchBtn.addEventListener('click', async () => {
            const address = locationSearch ? locationSearch.value.trim() : '';
            if (!address) {
                alert('Please enter a location');
                return;
            }
            
            const coords = await geocodeAddress(address);
            if (coords) {
                searchByLocation(coords.lat, coords.lng, radiusSelect ? radiusSelect.value : 10, address);
            } else {
                alert('Location not found. Please try a different location.');
            }
        });
    }
    
    // Use current location
    if (useCurrentBtn) {
        useCurrentBtn.addEventListener('click', () => {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        try {
                            const response = await fetch(`/maps/reverse?lat=${lat}&lon=${lng}&format=json`);
                            const data = await response.json();
                            const locationName = data.display_name || 'Current Location';
                            if (locationSearch) locationSearch.value = locationName.substring(0, 50);
                            searchByLocation(lat, lng, radiusSelect ? radiusSelect.value : 10, locationName);
                        } catch (error) {
                            searchByLocation(lat, lng, radiusSelect ? radiusSelect.value : 10, 'Current Location');
                        }
                    },
                    (error) => {
                        alert('Unable to get your location. Please enter location manually.');
                    }
                );
            } else {
                alert('Geolocation is not supported by your browser.');
            }
        });
    }
    
    // Load user's preferred location
    @if($userPreferredLat && $userPreferredLng)
    setTimeout(() => {
        if (locationSearch) locationSearch.value = "{{ auth()->user()->preferred_location_name ?? 'Saved Location' }}";
        if (savePreferredCheckbox) savePreferredCheckbox.checked = true;
    }, 500);
    @endif
    
    // Warehouse selection handler
    const warehouseSelect = document.getElementById('warehouse_select');
    if (warehouseSelect) {
        warehouseSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const warehouseId = this.value;
            const detailsDiv = document.getElementById('warehouse_details');
            const infoDiv = document.getElementById('warehouse_info');
            const facilitiesDiv = document.getElementById('nearby_facilities');
            
            if (!warehouseId) {
                if (detailsDiv) detailsDiv.classList.add('hidden');
                document.getElementById('selected_warehouse_id').value = '';
                return;
            }
            
            document.getElementById('selected_warehouse_id').value = warehouseId;
            
            const name = selected.dataset.name;
            const location = selected.dataset.location;
            const price = selected.dataset.price;
            const area = selected.dataset.area;
            const security = selected.dataset.security;
            const description = selected.dataset.description;
            const lat = parseFloat(selected.dataset.lat);
            const lng = parseFloat(selected.dataset.lng);
            const police = selected.dataset.police;
            const fire = selected.dataset.fire;
            const hospital = selected.dataset.hospital;
            
            initMap(lat, lng);
            if (currentMarker) {
                map.removeLayer(currentMarker);
            }
            currentMarker = L.marker([lat, lng]).addTo(map)
                .bindPopup(`<b>${name}</b><br>${location}<br>रु ${parseInt(price).toLocaleString()}/sq ft`)
                .openPopup();
            
            if (infoDiv) {
                infoDiv.innerHTML = `
                    <div class="border-b pb-3 mb-3">
                        <p class="font-bold text-lg">${name}</p>
                        <p class="text-gray-600"><i class="fas fa-map-marker-alt mr-1"></i> ${location}</p>
                        <p class="text-orange-600 font-bold mt-1">रु ${parseInt(price).toLocaleString()}/sq ft</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-3">
                        <div>
                            <p class="text-gray-500 text-sm">Total Area</p>
                            <p class="font-semibold">${parseInt(area).toLocaleString()} sq ft</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Est. Monthly Rent</p>
                            <p class="font-semibold text-orange-600" id="est_rent">रु 0</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <p class="text-gray-500 text-sm">Security Features</p>
                        <p class="text-sm">${security || '24/7 CCTV, Security Guard, Fire Safety'}</p>
                    </div>
                    <div class="mb-3">
                        <p class="text-gray-500 text-sm">Description</p>
                        <p class="text-sm">${description || 'Modern warehouse with easy access'}</p>
                    </div>
                `;
            }
            
            if (facilitiesDiv) {
                facilitiesDiv.innerHTML = `
                    <div class="facility-item flex items-center space-x-3">
                        <div class="facility-icon police flex items-center justify-center">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Nearest Police Station</p>
                            <p class="text-sm text-gray-600">${police}</p>
                        </div>
                    </div>
                    <div class="facility-item flex items-center space-x-3">
                        <div class="facility-icon fire flex items-center justify-center">
                            <i class="fas fa-fire-extinguisher"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Nearest Fire Station</p>
                            <p class="text-sm text-gray-600">${fire}</p>
                        </div>
                    </div>
                    <div class="facility-item flex items-center space-x-3">
                        <div class="facility-icon hospital flex items-center justify-center">
                            <i class="fas fa-hospital"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Nearest Hospital</p>
                            <p class="text-sm text-gray-600">${hospital}</p>
                        </div>
                    </div>
                `;
            }
            
            if (detailsDiv) detailsDiv.classList.remove('hidden');
            
            // Budget calculation
            const areaInput = document.getElementById('required_area');
            const budgetSpan = document.getElementById('estimated_budget');
            const warningSpan = document.getElementById('area_warning');
            
            if (areaInput) {
                areaInput.addEventListener('input', function() {
                    const areaVal = parseFloat(this.value);
                    if (areaVal && price) {
                        const estimated = areaVal * parseFloat(price);
                        if (budgetSpan) budgetSpan.value = 'रु ' + estimated.toLocaleString() + '/month';
                        
                        if (warningSpan && area && areaVal > parseFloat(area)) {
                            warningSpan.classList.remove('hidden');
                            warningSpan.innerHTML = `⚠️ Requested area exceeds available area`;
                        } else if (warningSpan) {
                            warningSpan.classList.add('hidden');
                        }
                    }
                });
            }
        });
    }
    
    if (document.readyState !== 'loading') {
        initMap();
    } else {
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
        });
    }
    document.addEventListener('kwdc:page-loaded', function() {
        map = null;
        initMap();
    });
</script>
@endpush
