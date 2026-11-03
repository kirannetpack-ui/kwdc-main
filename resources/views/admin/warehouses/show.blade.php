@extends('layouts.app')

@section('title', 'Warehouse Details')
@section('header', 'Warehouse Details - Review Mode')

@section('content')
<div class="space-y-6">
    <!-- Status Banner -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-info-circle text-blue-500 text-xl mr-3"></i>
            <div>
                <p class="text-blue-800 font-semibold">Review Mode - Read Only</p>
                <p class="text-blue-600 text-sm">You are viewing warehouse details. Use Approve/Reject buttons below to take action.</p>
            </div>
        </div>
    </div>

    <!-- Basic Information -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-3 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-info-circle mr-2 text-orange-500"></i> Basic Information</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Warehouse Name</label>
                    <p class="text-gray-800 font-medium">{{ $warehouse->name }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Warehouse Type</label>
                    <p class="text-gray-800 font-medium">
                        @if($warehouse->warehouse_type == 'building')
                            🏢 Building/Warehouse
                        @elseif($warehouse->warehouse_type == 'plot_land')
                            🌿 Plot of Land
                        @else
                            ❄️ Cold Storage
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Owner</label>
                    <p class="text-gray-800 font-medium">{{ $warehouse->owner->name ?? 'N/A' }}</p>
                    <p class="text-gray-500 text-sm">{{ $warehouse->owner->email ?? 'N/A' }} | {{ $warehouse->owner->phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Submitted On</label>
                    <p class="text-gray-800 font-medium">{{ $warehouse->created_at->format('F j, Y, g:i a') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Location & Map -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-3 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-map-marker-alt mr-2 text-orange-500"></i> Location & Map</h3>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <label class="block text-gray-500 text-sm font-semibold">Address/Location</label>
                <p class="text-gray-800">{{ $warehouse->location ?? $warehouse->address }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Latitude</label>
                    <p class="text-gray-800">{{ $warehouse->latitude ?? 'Not set' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Longitude</label>
                    <p class="text-gray-800">{{ $warehouse->longitude ?? 'Not set' }}</p>
                </div>
            </div>
            @if($warehouse->latitude && $warehouse->longitude)
            <div class="mt-4">
                <div id="map" style="height: 300px; border-radius: 8px;"></div>
            </div>
            @endif
        </div>
    </div>

    <!-- Area & Capacity -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-3 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-arrows-alt mr-2 text-orange-500"></i> Area & Capacity</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Total Area (Sq. Ft.)</label>
                    <p class="text-gray-800">{{ number_format($warehouse->total_area_sqft ?? 0) }} sq ft</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Total Area (Sq. Meters)</label>
                    <p class="text-gray-800">{{ number_format($warehouse->total_area_sqm ?? 0) }} sq m</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Built-up Area (Sq. Ft.)</label>
                    <p class="text-gray-800">{{ number_format($warehouse->built_up_area ?? 0) }} sq ft</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Open Area (Sq. Ft.)</label>
                    <p class="text-gray-800">{{ number_format($warehouse->open_area ?? 0) }} sq ft</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Usable/Allocatable Area</label>
                    <p class="text-gray-800">{{ number_format($warehouse->usable_area ?? 0) }} sq ft</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-3 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-tag mr-2 text-orange-500"></i> Pricing</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Price per Sq. Ft.</label>
                    <p class="text-gray-800 font-bold text-lg text-orange-600">रु {{ number_format($warehouse->price_per_unit ?? 0) }}/sq ft</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Security Deposit</label>
                    <p class="text-gray-800">रु {{ number_format($warehouse->security_deposit_fixed ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Features -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-3 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-shield-alt mr-2 text-orange-500"></i> Security Features</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">CCTV Cameras</label>
                    <p class="text-gray-800">{{ $warehouse->cctv_count ?? 0 }} cameras</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Security Guards</label>
                    <p class="text-gray-800">{{ $warehouse->security_guard_count ?? 0 }} guards</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Fire Extinguishers</label>
                    <p class="text-gray-800">{{ $warehouse->fire_extinguisher_count ?? 0 }} units</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Additional Features</label>
                    <p class="text-gray-800">{{ $warehouse->security_features ?? 'None specified' }}</p>
                </div>
            </div>
            
            <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="flex items-center">
                    <i class="fas {{ $warehouse->has_fire_alarm ? 'fa-check-circle text-green-500' : 'fa-times-circle text-gray-400' }} mr-2"></i>
                    <span class="text-sm">Fire Alarm</span>
                </div>
                <div class="flex items-center">
                    <i class="fas {{ $warehouse->has_sprinkler_system ? 'fa-check-circle text-green-500' : 'fa-times-circle text-gray-400' }} mr-2"></i>
                    <span class="text-sm">Sprinkler System</span>
                </div>
                <div class="flex items-center">
                    <i class="fas {{ $warehouse->has_generator_backup ? 'fa-check-circle text-green-500' : 'fa-times-circle text-gray-400' }} mr-2"></i>
                    <span class="text-sm">Generator Backup</span>
                </div>
                <div class="flex items-center">
                    <i class="fas {{ $warehouse->has_loading_dock ? 'fa-check-circle text-green-500' : 'fa-times-circle text-gray-400' }} mr-2"></i>
                    <span class="text-sm">Loading Dock</span>
                </div>
            </div>
            
            @if($warehouse->cctv_urls)
            <div class="mt-4">
                <label class="block text-gray-500 text-sm font-semibold mb-2">CCTV Stream URLs</label>
                @php $cctvUrls = json_decode($warehouse->cctv_urls, true); @endphp
                @if($cctvUrls && is_array($cctvUrls))
                    @foreach($cctvUrls as $key => $url)
                        @if($url)
                        <div class="bg-gray-50 p-2 rounded mb-2">
                            <span class="text-sm font-medium">Camera {{ $key }}:</span>
                            <a href="{{ $url }}" target="_blank" class="text-blue-500 hover:underline text-sm ml-2">{{ $url }}</a>
                        </div>
                        @endif
                    @endforeach
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Cold Storage Details (if applicable) -->
    @if($warehouse->warehouse_type == 'cold_storage')
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-3 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-thermometer-half mr-2 text-orange-500"></i> Cold Storage Specifications</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Temperature Range</label>
                    <p class="text-gray-800">{{ $warehouse->temperature_range_min ?? 'N/A' }}°C - {{ $warehouse->temperature_range_max ?? 'N/A' }}°C</p>
                </div>
                <div class="flex items-center">
                    <i class="fas {{ $warehouse->has_humidity_control ? 'fa-check-circle text-green-500' : 'fa-times-circle text-gray-400' }} mr-2"></i>
                    <span>Humidity Control</span>
                </div>
                <div class="flex items-center">
                    <i class="fas {{ $warehouse->has_backup_cooling ? 'fa-check-circle text-green-500' : 'fa-times-circle text-gray-400' }} mr-2"></i>
                    <span>Backup Cooling</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Nearby Facilities -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-3 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-hospital mr-2 text-orange-500"></i> Nearby Facilities</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Police Station</label>
                    <p class="text-gray-800">{{ $warehouse->nearest_police ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Fire Station</label>
                    <p class="text-gray-800">{{ $warehouse->nearest_fire_station ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Hospital</label>
                    <p class="text-gray-800">{{ $warehouse->nearest_hospital ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Bank</label>
                    <p class="text-gray-800">{{ $warehouse->nearest_bank ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Fuel Station</label>
                    <p class="text-gray-800">{{ $warehouse->nearest_fuel_station ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Market/Restaurant</label>
                    <p class="text-gray-800">{{ $warehouse->nearest_market ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-3 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-phone-alt mr-2 text-orange-500"></i> Contact Information</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Contact Person</label>
                    <p class="text-gray-800">{{ $warehouse->contact_person ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Contact Phone</label>
                    <p class="text-gray-800">{{ $warehouse->contact_phone ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-gray-500 text-sm font-semibold">Contact Email</label>
                    <p class="text-gray-800">{{ $warehouse->contact_email ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Photos -->
    @if($warehouse->front_photo || $warehouse->interior_photo_1 || $warehouse->interior_photo_2 || $warehouse->exterior_photo_1)
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-3 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-camera mr-2 text-orange-500"></i> Property Photos</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @if($warehouse->front_photo)
                <div>
                    <label class="block text-gray-500 text-sm font-semibold mb-2">Front View</label>
                    <img src="{{ Storage::url($warehouse->front_photo) }}" alt="Front View" class="w-full h-48 object-cover rounded-lg">
                </div>
                @endif
                @if($warehouse->interior_photo_1)
                <div>
                    <label class="block text-gray-500 text-sm font-semibold mb-2">Interior 1</label>
                    <img src="{{ Storage::url($warehouse->interior_photo_1) }}" alt="Interior 1" class="w-full h-48 object-cover rounded-lg">
                </div>
                @endif
                @if($warehouse->interior_photo_2)
                <div>
                    <label class="block text-gray-500 text-sm font-semibold mb-2">Interior 2</label>
                    <img src="{{ Storage::url($warehouse->interior_photo_2) }}" alt="Interior 2" class="w-full h-48 object-cover rounded-lg">
                </div>
                @endif
                @if($warehouse->exterior_photo_1)
                <div>
                    <label class="block text-gray-500 text-sm font-semibold mb-2">Exterior View</label>
                    <img src="{{ Storage::url($warehouse->exterior_photo_1) }}" alt="Exterior View" class="w-full h-48 object-cover rounded-lg">
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Documents -->
    @if($warehouse->ownership_document || $warehouse->tax_document || $warehouse->fire_safety_document || $warehouse->building_approval_document)
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-3 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-file-alt mr-2 text-orange-500"></i> Legal Documents</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($warehouse->ownership_document)
                <div>
                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                    <a href="{{ route('documents.private.show', ['path' => $warehouse->ownership_document]) }}" target="_blank" class="text-blue-500 hover:underline">Ownership Document</a>
                </div>
                @endif
                @if($warehouse->tax_document)
                <div>
                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                    <a href="{{ route('documents.private.show', ['path' => $warehouse->tax_document]) }}" target="_blank" class="text-blue-500 hover:underline">Tax Clearance Certificate</a>
                </div>
                @endif
                @if($warehouse->fire_safety_document)
                <div>
                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                    <a href="{{ route('documents.private.show', ['path' => $warehouse->fire_safety_document]) }}" target="_blank" class="text-blue-500 hover:underline">Fire Safety Certificate</a>
                </div>
                @endif
                @if($warehouse->building_approval_document)
                <div>
                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                    <a href="{{ route('documents.private.show', ['path' => $warehouse->building_approval_document]) }}" target="_blank" class="text-blue-500 hover:underline">Building Approval Document</a>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Description & Additional Info -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="bg-gray-50 px-6 py-3 border-b">
            <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-align-left mr-2 text-orange-500"></i> Additional Information</h3>
        </div>
        <div class="p-6">
            <div class="mb-4">
                <label class="block text-gray-500 text-sm font-semibold">Description</label>
                <p class="text-gray-800">{{ $warehouse->description ?? 'No description provided' }}</p>
            </div>
            <div class="mb-4">
                <label class="block text-gray-500 text-sm font-semibold">Special Features</label>
                <p class="text-gray-800">{{ $warehouse->special_features ?? 'None specified' }}</p>
            </div>
            <div>
                <label class="block text-gray-500 text-sm font-semibold">Restrictions</label>
                <p class="text-gray-800">{{ $warehouse->restrictions ?? 'None specified' }}</p>
            </div>
        </div>
    </div>

    <!-- Action Buttons (PDF Download Button Added Here) -->
    <div class="flex justify-end space-x-4 pb-6">
        
        <!-- 👇 NEW: PDF DOWNLOAD BUTTON 👇 -->
        <a href="{{ route('pdf.warehouse', $warehouse->id) }}" target="_blank" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition flex items-center">
            <i class="fas fa-file-pdf mr-2"></i> Download A4 PDF
        </a>
        <!-- 👆 END OF NEW BUTTON 👆 -->

        <a href="{{ route('admin.pending') }}" class="px-6 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Pending
        </a>
        <form action="{{ route('admin.reject', $warehouse->id) }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="px-6 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition" onclick="return confirm('Reject this warehouse?')">
                <i class="fas fa-times mr-2"></i> Reject
            </button>
        </form>
        <form action="{{ route('admin.approve', $warehouse->id) }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="px-6 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                <i class="fas fa-check mr-2"></i> Approve Warehouse
            </button>
        </form>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script>
(function() {
    function initAdminWarehouseMap() {
        const mapEl = document.getElementById('map');
        if (!mapEl) return;
        if (typeof L === 'undefined') {
            setTimeout(initAdminWarehouseMap, 100);
            return;
        }

        @if($warehouse->latitude && $warehouse->longitude)
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

        L.marker([{{ $warehouse->latitude }}, {{ $warehouse->longitude }}]).addTo(map)
            .bindPopup('<b>{{ addslashes($warehouse->name) }}</b><br>{{ addslashes($warehouse->location ?? "") }}')
            .openPopup();

        const resize = () => { map.invalidateSize(); };
        setTimeout(resize, 80);
        setTimeout(resize, 250);
        setTimeout(resize, 600);
        window.addEventListener('resize', resize);
        @endif
    }

    if (document.readyState !== 'loading') {
        initAdminWarehouseMap();
    } else {
        document.addEventListener('DOMContentLoaded', initAdminWarehouseMap);
    }
    document.addEventListener('kwdc:page-loaded', initAdminWarehouseMap);
})();
</script>
@endpush
@endsection
