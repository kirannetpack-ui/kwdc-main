@extends('layouts.app')

@section('title', 'Request Equipment')
@section('header', 'Request Equipment')

@section('content')
<style>
    .map-container {
        height: 350px;
        border-radius: 12px;
        overflow: hidden;
        z-index: 1;
    }
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
    }
    .loading-content {
        background: white;
        padding: 30px;
        border-radius: 15px;
        text-align: center;
    }
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Request Equipment</h1>
            <p class="text-gray-500 mt-1">Choose equipment, dates, and work location.</p>
        </div>
        <a href="{{ route('equipment-requests.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition">
            <i class="fas fa-arrow-left mr-2"></i>Back to Requests
        </a>
    </div>

    <form id="equipmentRequestForm" method="POST" action="{{ route('equipment-requests.store') }}">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Cargo & Logistics -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-boxes text-blue-500 mr-2"></i>
                        Cargo & Logistics Details
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Commodity Name</label>
                            <input type="text" name="commodity_name" id="commodity_name"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                   placeholder="What are you transporting? (e.g., Building materials, Machinery, etc)">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Approximate Weight (kg)</label>
                                <input type="number" name="weight" id="weight" min="0" step="0.1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                       placeholder="e.g., 5000">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cargo Dimensions</label>
                                <input type="text" name="dimensions" id="dimensions"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                       placeholder="e.g., 4m x 2m x 2m">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Loading Site</label>
                                <input type="text" name="loading_site" id="loading_site"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                       placeholder="Where will the equipment pick up the cargo?">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Unloading Site</label>
                                <input type="text" name="unloading_site" id="unloading_site"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                       placeholder="Where will the cargo be delivered?">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Recommendation -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold flex items-center">
                        <i class="fas fa-wand-magic-sparkles text-blue-500 mr-2"></i>
                            Recommendation
                        </h3>
                        <button type="button" id="recommendBtn" class="text-sm bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-lg transition">
                            <i class="fas fa-magic mr-1"></i> Suggest Model
                        </button>
                    </div>
                    <div id="ai-loading" class="hidden text-center py-3">
                        <div class="spinner-border text-blue-500" role="status"></div>
                        <p class="text-sm text-gray-500 mt-1">Checking the request...</p>
                    </div>
                    <div id="ai-results" class="space-y-3 hidden">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <p class="text-xs text-gray-500">Recommended Model</p>
                            <p class="font-bold text-lg text-blue-700" id="rec-model">-</p>
                        </div>
                        <!-- UPDATED: Display Hourly and Daily Price -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Price / Hour</p>
                                <p class="font-bold text-lg text-green-700" id="rec-price-hour">रू 0</p>
                            </div>
                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                                <p class="text-xs text-gray-500">Price / Day</p>
                                <p class="font-bold text-lg text-orange-700" id="rec-price-day">रू 0</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                            <p class="text-xs text-gray-500">Why this model?</p>
                            <p class="text-sm text-gray-700" id="rec-reason">Click "Suggest Model" to find out.</p>
                        </div>
                    </div>
                </div>

                <!-- Equipment Requirements -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-tools text-orange-500 mr-2"></i>
                        Equipment Requirements
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Equipment Type <span class="text-red-500">*</span></label>
                            <select name="equipment_type" id="equipment_type" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" required>
                                <option value="">Select Equipment Type</option>
                                <option value="excavator">Excavator</option>
                                <option value="backhoe loader">Backhoe Loader</option>
                                <option value="bulldozer">Bulldozer</option>
                                <option value="crane">Crane</option>
                                <option value="forklift">Forklift</option>
                                <option value="loader">Loader</option>
                                <option value="grader">Grader</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Specific Equipment (Optional)</label>
                            <input type="text" name="specific_equipment" id="specific_equipment"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                   placeholder="e.g., JCB 3DX">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Required From Date <span class="text-red-500">*</span></label>
                                <input type="date" name="start_date" id="start_date" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Required To Date <span class="text-red-500">*</span></label>
                                <input type="date" name="end_date" id="end_date" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Days) <span class="text-red-500">*</span></label>
                                <input type="number" name="duration" id="duration" min="1"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                       placeholder="Number of days" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Your Proposed Budget (NPR/Day)</label>
                                <input type="number" name="budget" id="budget" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                       placeholder="Your proposed price">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Work Location <span class="text-red-500">*</span></label>
                            <input type="text" name="location" id="location"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                   placeholder="Where will the equipment be used? e.g., Baneshwor, Kathmandu" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Description / Requirements <span class="text-red-500">*</span></label>
                            <textarea name="description" id="description" rows="3"
                                      class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                      placeholder="Describe your project requirements, terrain, and expected usage..." required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Work Site Map -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-bold mb-4 flex items-center">
                        <i class="fas fa-map text-purple-500 mr-2"></i>
                        Work Site Visualization
                    </h3>
                    <div id="map" class="map-container"></div>
                    <p id="mapStatus" class="text-xs text-gray-500 mt-2">Enter a location to place the marker.</p>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Price Summary -->
                <div class="bg-gradient-to-r from-orange-500 to-red-500 rounded-xl shadow-md p-6 text-white">
                    <h3 class="text-lg font-bold mb-4">Estimated Cost</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Price / Day:</span>
                            <span id="daily_price_display">रू 0</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Duration:</span>
                            <span id="duration_display">0 days</span>
                        </div>
                        <div class="border-t border-white/30 my-2"></div>
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total Estimate:</span>
                            <span id="total_cost_display">रू 0</span>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <button type="submit" id="submitBtn" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 px-4 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-paper-plane mr-2"></i>Submit Request
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-content">
        <i class="fas fa-spinner fa-spin text-orange-500 text-4xl mb-3"></i>
        <p class="text-gray-700">Submitting request...</p>
    </div>
</div>

<script>
let mapInstance = null;
let mapMarker = null;

function setupEquipmentCreatePage() {
    initMap();
    setupAutoComplete();
    autoCalculateDuration();
}

if (document.readyState !== 'loading') {
    setupEquipmentCreatePage();
} else {
    document.addEventListener('DOMContentLoaded', setupEquipmentCreatePage);
}
document.addEventListener('kwdc:page-loaded', function() {
    initMap();
});

function initMap() {
    const mapEl = document.getElementById('map');
    if (!mapEl) return;
    if (typeof L === 'undefined') {
        setTimeout(initMap, 100);
        return;
    }

    if (mapEl._leaflet_id) {
        mapEl._leaflet_id = null;
        mapEl.innerHTML = '';
    }

    mapInstance = L.map(mapEl, {
        zoomControl: true,
        scrollWheelZoom: false
    }).setView([27.7172, 85.3240], 13);
    mapEl._leaflet_map = mapInstance;

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(mapInstance);

    const resize = () => { if (mapInstance) mapInstance.invalidateSize(); };
    setTimeout(resize, 80);
    setTimeout(resize, 250);
    setTimeout(resize, 600);
    window.addEventListener('resize', resize);
}

async function geocodeAddress(address) {
    try {
        if (window.KwdcMaps) {
            return await window.KwdcMaps.search(address);
        }
        const response = await fetch(`/maps/search?q=${encodeURIComponent(address)}&limit=1`);
        if (!response.ok) throw new Error('Location lookup failed');
        const data = await response.json();
        if (Array.isArray(data) && data.length > 0) {
            return { lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon), label: data[0].display_name };
        }
    } catch (e) {
        console.warn('Location lookup failed:', e);
    }
    return null;
}

async function renderMapMarker() {
    if (!mapInstance) return;
    if (mapMarker) {
        mapInstance.removeLayer(mapMarker);
        mapMarker = null;
    }

    const address = document.getElementById('location').value;
    const status = document.getElementById('mapStatus');
    if (!address.trim()) return;

    if (status) status.textContent = 'Finding location...';
    const coords = await geocodeAddress(address);
    if (!coords) {
        if (status) status.textContent = 'Location not found. Try a nearby landmark or city.';
        return;
    }

    const popup = document.createElement('div');
    const title = document.createElement('strong');
    title.textContent = 'Work Location';
    const line = document.createElement('div');
    line.textContent = coords.label || address;
    popup.append(title, line);

    mapMarker = L.marker([coords.lat, coords.lng]).addTo(mapInstance).bindPopup(popup);
    
    mapInstance.setView([coords.lat, coords.lng], 15);
    if (status) status.textContent = coords.label || 'Location found.';
}

function setupAutoComplete() {
    const locationInput = document.getElementById('location');
    if (!locationInput) return;

    let lookupTimer = null;
    const scheduleLookup = () => {
        window.clearTimeout(lookupTimer);
        lookupTimer = window.setTimeout(renderMapMarker, 650);
    };
    locationInput.addEventListener('input', scheduleLookup);
    locationInput.addEventListener('change', renderMapMarker);

    if (locationInput && typeof google !== 'undefined' && google.maps) {
        const autocomplete = new google.maps.places.Autocomplete(locationInput);
        autocomplete.addListener('place_changed', function() { renderMapMarker(); });
    }
}

function autoCalculateDuration() {
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');
    const durationInput = document.getElementById('duration');

    startInput.addEventListener('change', updateDuration);
    endInput.addEventListener('change', updateDuration);

    function updateDuration() {
        if (startInput.value && endInput.value) {
            const start = new Date(startInput.value);
            const end = new Date(endInput.value);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            durationInput.value = diffDays;
            document.getElementById('duration_display').innerText = diffDays + ' days';
        }
    }
}

document.getElementById('recommendBtn').addEventListener('click', function() {
    const type = document.getElementById('equipment_type').value;
    const duration = document.getElementById('duration').value;
    const location = document.getElementById('location').value;
    const description = document.getElementById('description').value;
    const budget = document.getElementById('budget').value;
    const commodity_name = document.getElementById('commodity_name').value;
    const weight = document.getElementById('weight').value;
    const dimensions = document.getElementById('dimensions').value;
    const loading_site = document.getElementById('loading_site').value;
    const unloading_site = document.getElementById('unloading_site').value;

    const hasCargo = commodity_name || weight || dimensions;
    const hasSite = loading_site || unloading_site;

    if (!hasCargo || !hasSite) {
        alert('Please provide cargo details and at least one site.');
        return;
    }

    const loadingDiv = document.getElementById('ai-loading');
    const resultsDiv = document.getElementById('ai-results');
    const recModel = document.getElementById('rec-model');
    const recPriceHour = document.getElementById('rec-price-hour');
    const recPriceDay = document.getElementById('rec-price-day');
    const recReason = document.getElementById('rec-reason');

    loadingDiv.classList.remove('hidden');
    resultsDiv.classList.add('hidden');

    fetch('/equipment-requests/recommend', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ 
            equipment_type: type, 
            duration: duration, 
            location: location, 
            description: description, 
            budget: budget,
            commodity_name: commodity_name,
            weight: weight,
            dimensions: dimensions,
            loading_site: loading_site,
            unloading_site: unloading_site
        })
    })
    .then(response => response.json())
    .then(data => {
        loadingDiv.classList.add('hidden');

        resultsDiv.classList.remove('hidden');
        resultsDiv.style.display = 'block';

        if (data.success && data.recommendation) {
            const rec = data.recommendation;
            
            recModel.innerText = rec.recommended_model || 'Not found';
            
            recPriceHour.innerText = rec.estimated_price_per_hour ? 'रू ' + parseInt(rec.estimated_price_per_hour).toLocaleString() : 'रू 0';
            recPriceDay.innerText = rec.estimated_price_per_day ? 'रू ' + parseInt(rec.estimated_price_per_day).toLocaleString() : 'रू 0';
            
            if (rec.estimated_price_per_day) {
                document.getElementById('daily_price_display').innerText = 'रू ' + parseInt(rec.estimated_price_per_day).toLocaleString();
                const durationVal = parseInt(duration) || 1;
                document.getElementById('total_cost_display').innerText = 'रू ' + (parseInt(rec.estimated_price_per_day) * durationVal).toLocaleString();
                document.getElementById('duration_display').innerText = durationVal + ' days';
            }

            recReason.innerText = rec.reason || 'Matched from cargo size, weight, and site details.';

        } else {
            recModel.innerText = 'Recommendation unavailable';
            recPriceHour.innerText = 'रू 0';
            recPriceDay.innerText = 'रू 0';
            recReason.innerText = 'The recommendation service is not available right now.';
        }
    })
    .catch(error => {
        loadingDiv.classList.add('hidden');
        console.error('🔴 Fetch Error:', error);
        
        resultsDiv.classList.remove('hidden');
        resultsDiv.style.display = 'block';
        recModel.innerText = 'Connection Error';
        recPriceHour.innerText = 'रू 0';
        recPriceDay.innerText = 'रू 0';
        recReason.innerText = 'Network error. Please try again.';
    });
});


// Form submission
document.getElementById('equipmentRequestForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const type = document.getElementById('equipment_type').value;
    const location = document.getElementById('location').value;
    const description = document.getElementById('description').value;
    
    if (!type) { showToast('Please select an equipment type', 'error'); return; }
    if (!location) { showToast('Please enter a work location', 'error'); return; }
    if (!description) { showToast('Please describe your requirements', 'error'); return; }

    document.getElementById('loadingOverlay').style.display = 'flex';
    const formData = new FormData(this);
    
    try {
        const response = await fetch(this.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: formData
        });
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            setTimeout(() => window.location.href = result.redirect_url || '{{ route("equipment-requests.index") }}', 1500);
        } else {
            document.getElementById('loadingOverlay').style.display = 'none';
            showToast(result.message || 'Failed to submit request', 'error');
        }
    } catch (error) {
        document.getElementById('loadingOverlay').style.display = 'none';
        showToast('Network error. Please try again.', 'error');
        console.error('Error:', error);
    }
});

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 animate-slide-up ' + 
        (type === 'success' ? 'bg-green-500 text-white' : 
         type === 'error' ? 'bg-red-500 text-white' : 
         'bg-blue-500 text-white');
    const icon = document.createElement('i');
    icon.className = 'fas fa-' + (type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle') + ' mr-2';
    toast.append(icon, document.createTextNode(message));
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
}

const style = document.createElement('style');
style.textContent = `@keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } } .animate-slide-up { animation: slideUp 0.3s ease-out; }`;
document.head.appendChild(style);
</script>

@endsection
