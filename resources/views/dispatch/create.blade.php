@extends('layouts.app')

@section('title', 'Create Dispatch Order')
@section('header', 'Create Dispatch Order')

@section('content')
<style>
    .map-container { height: 400px; border-radius: 12px; margin-bottom: 16px; }
    .stop-card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 16px; margin-bottom: 16px; background: #f9fafb; }
    .driver-card { border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px; margin-bottom: 12px; cursor: pointer; transition: all 0.3s ease; }
    .driver-card:hover, .driver-card.selected { border-color: #f59e0b; background: #fffbeb; }
    .price-breakdown { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; }
</style>

<div class="bg-white rounded-xl shadow-md p-6">
    <form id="dispatchForm" method="POST" action="{{ route('dispatch.store') }}" enctype="multipart/form-data">
        @csrf
        
        <!-- Source Location -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">📍 Source Location</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-gray-700 font-semibold mb-2">Pickup Address *</label>
                    <div class="flex gap-2">
                        <input type="text" name="pickup_address" id="pickup_address" required class="flex-1 px-4 py-2 border rounded-lg">
                        <button type="button" id="searchPickup" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Search</button>
                        <button type="button" id="currentLocation" class="bg-green-500 text-white px-4 py-2 rounded-lg">Current</button>
                    </div>
                    <input type="hidden" name="pickup_latitude" id="pickup_latitude">
                    <input type="hidden" name="pickup_longitude" id="pickup_longitude">
                </div>
            </div>
        </div>
        
        <!-- Destination / Delivery Stops -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-800">📍 Destination / Delivery Stops</h3>
                <button type="button" id="addStopBtn" class="bg-orange-500 text-white px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-plus mr-2"></i> Add Stop
                </button>
            </div>
            <div id="stopsContainer">
                <div class="stop-card" data-stop-index="0">
                    <div class="flex justify-between items-center mb-3">
                        <span class="font-semibold">Stop 1 (Final Destination)</span>
                        <button type="button" class="remove-stop text-red-500 hover:text-red-700 hidden" data-stop="0">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-gray-600 text-sm mb-1">Delivery Address *</label>
                            <div class="flex gap-2">
                                <input type="text" name="stops[0][address]" required class="flex-1 px-3 py-2 border rounded-lg">
                                <button type="button" class="search-address bg-blue-500 text-white px-3 py-2 rounded-lg text-sm">Search</button>
                            </div>
                            <input type="hidden" name="stops[0][latitude]">
                            <input type="hidden" name="stops[0][longitude]">
                        </div>
                        <div>
                            <label class="block text-gray-600 text-sm mb-1">Recipient Name</label>
                            <input type="text" name="stops[0][recipient_name]" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-gray-600 text-sm mb-1">Contact Number</label>
                            <input type="text" name="stops[0][recipient_phone]" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-gray-600 text-sm mb-1">Number of Boxes</label>
                            <input type="number" name="stops[0][boxes_count]" value="1" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-gray-600 text-sm mb-1">Delivery Notes</label>
                            <textarea name="stops[0][notes]" rows="2" class="w-full px-3 py-2 border rounded-lg" placeholder="Special instructions..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Map for Route Visualization -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">🗺️ Route Map</h3>
            <div id="routeMap" class="map-container"></div>
            <p class="text-sm text-gray-500 mt-2">Click on map to set pickup/delivery locations</p>
        </div>
        
        <!-- Invoice & Documents -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">📄 Invoice & Documents</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Invoice Number *</label>
                    <input type="text" name="invoice_number" required class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Bill Type</label>
                    <select name="bill_type" id="bill_type" class="w-full px-4 py-2 border rounded-lg">
                        <option value="regular">Regular Bill</option>
                        <option value="vat">VAT Bill</option>
                        <option value="pan">PAN Bill</option>
                    </select>
                </div>
                <div id="panField" style="display: none;">
                    <label class="block text-gray-700 font-semibold mb-2">PAN/VAT Number</label>
                    <input type="text" name="pan_number" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-2">Upload Bill Document *</label>
                    <input type="file" name="bill_document" accept=".pdf,.jpg,.jpeg,.png" required class="w-full">
                </div>
            </div>
        </div>
        
        <!-- Driver Selection -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h3 class="text-lg font-bold text-gray-800 mb-4">👨‍✈️ Select Driver</h3>
            <div id="driversContainer" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($driverRates as $rate)
                <div class="driver-card" data-driver-id="{{ $rate->id }}" data-rate='{{ json_encode($rate) }}'>
                    <div class="flex justify-between">
                        <div>
                            <p class="font-semibold">{{ $rate->driver->name ?? 'Unknown Driver' }}</p>
                            <p class="text-sm text-gray-600">📞 {{ $rate->driver->phone ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">⭐ 4.8 (150+ deliveries)</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold">Rates:</p>
                            <p class="text-xs">0-5km: रु {{ number_format($rate->flat_rate_0_5) }}</p>
                            <p class="text-xs">6-10km: रु {{ number_format($rate->flat_rate_6_10) }}</p>
                            <p class="text-xs">11-20km: रु {{ number_format($rate->flat_rate_11_20) }}</p>
                            <p class="text-xs">21+km: रु {{ number_format($rate->rate_per_km_21_plus) }}/km</p>
                        </div>
                    </div>
                    <div class="mt-2 pt-2 border-t">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="driver_rate_id" value="{{ $rate->id }}" class="driver-radio mr-2">
                            <span class="text-sm text-green-600">Select this driver</span>
                        </label>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        
        <!-- Price Display -->
        <div id="priceDisplay" style="display: none;" class="price-breakdown mb-6">
            <h4 class="font-bold text-green-800 mb-3">💰 Price Breakdown</h4>
            <div id="breakdownContent"></div>
            <div class="mt-3 pt-2 border-t border-green-200">
                <p class="font-bold text-lg">Total Delivery Cost: <span id="totalPrice" class="text-orange-600">रु 0</span></p>
                <p class="text-sm text-gray-600 mt-1">This price will be locked upon confirmation</p>
            </div>
        </div>
        
        <div class="flex justify-end space-x-4">
            <a href="{{ route('dispatch.index') }}" class="px-6 py-2 bg-gray-300 rounded-lg">Cancel</a>
            <button type="submit" id="submitBtn" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600" disabled>
                <i class="fas fa-check-circle mr-2"></i> Confirm & Create Dispatch
            </button>
        </div>
    </form>
</div>

<!-- Hidden Maps API -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    let routeMap, pickupMarker, deliveryMarkers = [];
    let stopCount = 1;
    let selectedDriver = null;
    let currentRoute = null;
    
    // Initialize Map
    function initMap(lat = 27.7172, lng = 85.3240) {
        const mapEl = document.getElementById('routeMap');
        if (!mapEl) return;
        if (typeof L === 'undefined') {
            setTimeout(() => initMap(lat, lng), 100);
            return;
        }

        if (mapEl._leaflet_id && !routeMap) {
            mapEl._leaflet_id = null;
            mapEl.innerHTML = '';
        }

        if (routeMap) {
            routeMap.setView([lat, lng], 13);
        } else {
            routeMap = L.map(mapEl, {
                zoomControl: true,
                scrollWheelZoom: false
            }).setView([lat, lng], 13);
            mapEl._leaflet_map = routeMap;
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(routeMap);

            const resize = () => { if (routeMap) routeMap.invalidateSize(); };
            setTimeout(resize, 80);
            setTimeout(resize, 250);
            setTimeout(resize, 600);
            window.addEventListener('resize', resize);
        }
        
        // Click on map to set pickup
        routeMap.on('click', async function(e) {
            if (!pickupMarker) {
                pickupMarker = L.marker([e.latlng.lat, e.latlng.lng], { draggable: true }).addTo(routeMap);
                pickupMarker.on('dragend', updateRoute);
                document.getElementById('pickup_latitude').value = e.latlng.lat;
                document.getElementById('pickup_longitude').value = e.latlng.lng;
                await reverseGeocode(e.latlng.lat, e.latlng.lng, 'pickup_address');
                updateRoute();
            }
        });
    }
    
    // Geocoding
    async function geocodeAddress(address) {
        try {
            const res = await fetch(`/maps/search?q=${encodeURIComponent(address)},Nepal&format=json&limit=1`);
            const data = await res.json();
            if (data && data.length > 0) {
                return { lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon) };
            }
            return null;
        } catch (e) { console.error(e); return null; }
    }
    
    async function reverseGeocode(lat, lng, fieldId) {
        try {
            const res = await fetch(`/maps/reverse?lat=${lat}&lon=${lng}&format=json`);
            const data = await res.json();
            if (data.display_name) {
                document.getElementById(fieldId).value = data.display_name;
            }
        } catch (e) { console.error(e); }
    }
    
    // Update Route and Calculate Price
    async function updateRoute() {
        const pickupLat = parseFloat(document.getElementById('pickup_latitude').value);
        const pickupLng = parseFloat(document.getElementById('pickup_longitude').value);
        
        if (!pickupLat || !pickupLng) return;
        
        // Collect all stop coordinates
        const stops = [];
        let totalDistance = 0;
        let prevPoint = { lat: pickupLat, lng: pickupLng };
        
        for (let i = 0; i < stopCount; i++) {
            const latInput = document.querySelector(`input[name="stops[${i}][latitude]"]`);
            const lngInput = document.querySelector(`input[name="stops[${i}][longitude]"]`);
            if (latInput && latInput.value && lngInput && lngInput.value) {
                const stopPoint = { lat: parseFloat(latInput.value), lng: parseFloat(lngInput.value) };
                const dist = calculateDistance(prevPoint.lat, prevPoint.lng, stopPoint.lat, stopPoint.lng);
                totalDistance += dist;
                prevPoint = stopPoint;
                stops.push(stopPoint);
            }
        }
        
        // Draw route on map
        if (currentRoute) routeMap.removeLayer(currentRoute);
        if (pickupMarker && stops.length > 0) {
            const points = [{ lat: pickupLat, lng: pickupLng }, ...stops];
            currentRoute = L.polyline(points.map(p => [p.lat, p.lng]), { color: '#f59e0b', weight: 4 }).addTo(routeMap);
            routeMap.fitBounds(currentRoute.getBounds());
        }
        
        // Calculate price
        if (selectedDriver) {
            $.ajax({
                url: '{{ route("dispatch.calculate-price") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    distance: totalDistance,
                    driver_rate_id: selectedDriver
                },
                success: function(res) {
                    document.getElementById('priceDisplay').style.display = 'block';
                    document.getElementById('submitBtn').disabled = false;
                    document.getElementById('breakdownContent').innerHTML = `
                        <p>📏 Total Distance: <strong>${res.distance.toFixed(2)} km</strong></p>
                        <p>🚚 Number of Stops: <strong>${stopCount}</strong></p>
                        <p>💰 Base Price: <strong>रु ${res.price.toLocaleString()}</strong></p>
                        <p>📊 Admin Margin: <strong>रु ${res.admin_margin.toLocaleString()}</strong></p>
                        <p>👨‍✈️ Driver Earnings: <strong>रु ${res.driver_earning.toLocaleString()}</strong></p>
                    `;
                    document.getElementById('totalPrice').innerHTML = 'रु ' + res.price.toLocaleString();
                }
            });
        }
    }
    
    function calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI/180) * Math.cos(lat2 * Math.PI/180) *
                  Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
    
    // Add Stop
    document.getElementById('addStopBtn').addEventListener('click', function() {
        const container = document.getElementById('stopsContainer');
        const newStop = document.createElement('div');
        newStop.className = 'stop-card';
        newStop.innerHTML = `
            <div class="flex justify-between items-center mb-3">
                <span class="font-semibold">Stop ${stopCount + 1}</span>
                <button type="button" class="remove-stop text-red-500 hover:text-red-700">Remove</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-gray-600 text-sm mb-1">Delivery Address *</label>
                    <div class="flex gap-2">
                        <input type="text" name="stops[${stopCount}][address]" required class="flex-1 px-3 py-2 border rounded-lg">
                        <button type="button" class="search-address bg-blue-500 text-white px-3 py-2 rounded-lg text-sm">Search</button>
                    </div>
                    <input type="hidden" name="stops[${stopCount}][latitude]">
                    <input type="hidden" name="stops[${stopCount}][longitude]">
                </div>
                <div>
                    <label class="block text-gray-600 text-sm mb-1">Recipient Name</label>
                    <input type="text" name="stops[${stopCount}][recipient_name]" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-600 text-sm mb-1">Contact Number</label>
                    <input type="text" name="stops[${stopCount}][recipient_phone]" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-gray-600 text-sm mb-1">Number of Boxes</label>
                    <input type="number" name="stops[${stopCount}][boxes_count]" value="1" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div class="col-span-2">
                    <label class="block text-gray-600 text-sm mb-1">Delivery Notes</label>
                    <textarea name="stops[${stopCount}][notes]" rows="2" class="w-full px-3 py-2 border rounded-lg"></textarea>
                </div>
            </div>
        `;
        container.appendChild(newStop);
        stopCount++;
        attachStopEvents();
        updateRoute();
    });
    
    function attachStopEvents() {
        document.querySelectorAll('.search-address').forEach(btn => {
            btn.removeEventListener('click', handleAddressSearch);
            btn.addEventListener('click', handleAddressSearch);
        });
        document.querySelectorAll('.remove-stop').forEach(btn => {
            btn.removeEventListener('click', handleRemoveStop);
            btn.addEventListener('click', handleRemoveStop);
        });
    }
    
    async function handleAddressSearch(e) {
        const addressInput = this.previousElementSibling;
        const coords = await geocodeAddress(addressInput.value);
        if (coords) {
            const latInput = addressInput.parentElement.nextElementSibling;
            const lngInput = latInput.nextElementSibling;
            latInput.value = coords.lat;
            lngInput.value = coords.lng;
            updateRoute();
        }
    }
    
    function handleRemoveStop(e) {
        const stopDiv = this.closest('.stop-card');
        if (stopDiv && document.querySelectorAll('.stop-card').length > 1) {
            stopDiv.remove();
            stopCount--;
            updateRoute();
        }
    }
    
    // Driver selection
    document.querySelectorAll('.driver-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.driver-card').forEach(c => c.classList.remove('selected'));
            this.closest('.driver-card').classList.add('selected');
            selectedDriver = this.value;
            updateRoute();
        });
    });
    
    // Search pickup
    document.getElementById('searchPickup').addEventListener('click', async () => {
        const addr = document.getElementById('pickup_address').value;
        const coords = await geocodeAddress(addr);
        if (coords) {
            document.getElementById('pickup_latitude').value = coords.lat;
            document.getElementById('pickup_longitude').value = coords.lng;
            if (pickupMarker) routeMap.removeLayer(pickupMarker);
            pickupMarker = L.marker([coords.lat, coords.lng], { draggable: true }).addTo(routeMap);
            pickupMarker.on('dragend', updateRoute);
            routeMap.setView([coords.lat, coords.lng], 15);
            updateRoute();
        }
    });
    
    document.getElementById('currentLocation').addEventListener('click', () => {
        navigator.geolocation.getCurrentPosition(pos => {
            document.getElementById('pickup_latitude').value = pos.coords.latitude;
            document.getElementById('pickup_longitude').value = pos.coords.longitude;
            if (pickupMarker) routeMap.removeLayer(pickupMarker);
            pickupMarker = L.marker([pos.coords.latitude, pos.coords.longitude], { draggable: true }).addTo(routeMap);
            pickupMarker.on('dragend', updateRoute);
            routeMap.setView([pos.coords.latitude, pos.coords.longitude], 15);
            reverseGeocode(pos.coords.latitude, pos.coords.longitude, 'pickup_address');
            updateRoute();
        });
    });
    
    document.getElementById('bill_type').addEventListener('change', function() {
        document.getElementById('panField').style.display = (this.value === 'vat' || this.value === 'pan') ? 'block' : 'none';
    });
    
    initMap();
    attachStopEvents();
    document.addEventListener('kwdc:page-loaded', function() {
        routeMap = null;
        pickupMarker = null;
        initMap();
    });
</script>
@endsection
