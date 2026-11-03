@extends('layouts.app')

@section('title', 'Edit Warehouse')
@section('header', 'Edit Warehouse')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-xl shadow-md overflow-hidden max-w-6xl mx-auto">
        <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800"><i class="fas fa-edit mr-2 text-orange-500"></i> Edit Warehouse</h3>
            <a href="{{ route('admin.warehouses.show', $warehouse->id) }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-1"></i> Back to Details
            </a>
        </div>

        <div class="p-6">
            <form action="{{ route('admin.warehouses.update', $warehouse->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="mb-6">
                    <h4 class="font-bold text-gray-700 border-b pb-2 mb-4">Basic Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Warehouse Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $warehouse->name) }}" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location <span class="text-red-500">*</span></label>
                            <input type="text" name="location" value="{{ old('location', $warehouse->location) }}" required
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <input type="text" name="address" value="{{ old('address', $warehouse->address) }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Latitude</label>
                                <input type="number" step="any" name="latitude" value="{{ old('latitude', $warehouse->latitude) }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Longitude</label>
                                <input type="number" step="any" name="longitude" value="{{ old('longitude', $warehouse->longitude) }}"
                                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Owner <span class="text-red-500">*</span></label>
                            <select name="owner_id" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                @foreach($propertyOwners as $owner)
                                    <option value="{{ $owner->id }}" {{ old('owner_id', $warehouse->user_id) == $owner->id ? 'selected' : '' }}>
                                        {{ $owner->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Number</label>
                            <input type="text" name="contact_number" value="{{ old('contact_number', $warehouse->contact_number) }}"
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option value="pending" {{ old('status', $warehouse->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ old('status', $warehouse->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ old('status', $warehouse->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Area & Capacity -->
                <div class="mb-6">
                    <h4 class="font-bold text-gray-700 border-b pb-2 mb-4">Area & Capacity</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Total Area (Sq. Ft.)</label>
                            <input type="number" step="0.01" name="area_sqft" value="{{ old('area_sqft', $warehouse->area_sqft) }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Total Area (Sq. Meters)</label>
                            <input type="number" step="0.01" name="area_sqm" value="{{ old('area_sqm', $warehouse->area_sqm) }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price per Sq. Ft. (NPR)</label>
                            <input type="number" step="0.01" name="price_per_sqft" value="{{ old('price_per_sqft', $warehouse->price_per_sqft) }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                    </div>
                </div>

                <!-- Security Features -->
                <div class="mb-6">
                    <h4 class="font-bold text-gray-700 border-b pb-2 mb-4">Security Features</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">CCTV Count</label>
                            <input type="number" name="cctv_count" value="{{ old('cctv_count', $warehouse->cctv_count) }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Security Guards</label>
                            <input type="number" name="guards_count" value="{{ old('guards_count', $warehouse->guards_count) }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fire Extinguishers</label>
                            <input type="number" name="fire_extinguishers" value="{{ old('fire_extinguishers', $warehouse->fire_extinguishers) }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">CCTV Stream URLs (Optional)</label>
                        <div id="cctv-urls-container">
                            @php
                                $urls = is_array($warehouse->cctv_stream_urls) ? $warehouse->cctv_stream_urls : [];
                            @endphp
                            @forelse($urls as $index => $url)
                                <div class="flex gap-2 mb-2">
                                    <input type="text" name="cctv_stream_urls[{{ $index }}]" value="{{ $url }}" 
                                           class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="https://...">
                                    <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 px-2">×</button>
                                </div>
                            @empty
                                <div class="flex gap-2 mb-2">
                                    <input type="text" name="cctv_stream_urls[0]" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="https://...">
                                    <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 px-2">×</button>
                                </div>
                            @endforelse
                        </div>
                        <button type="button" onclick="addCCTVUrl()" class="text-sm text-blue-500 hover:text-blue-700 mt-1">+ Add another URL</button>
                    </div>
                </div>

                <!-- Nearby Facilities -->
                <div class="mb-6">
                    <h4 class="font-bold text-gray-700 border-b pb-2 mb-4">Nearby Facilities</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Police Station</label><input type="text" name="nearby_police" value="{{ old('nearby_police', $warehouse->nearby_police) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Fire Station</label><input type="text" name="nearby_fire" value="{{ old('nearby_fire', $warehouse->nearby_fire) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Hospital</label><input type="text" name="nearby_hospital" value="{{ old('nearby_hospital', $warehouse->nearby_hospital) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Bank</label><input type="text" name="nearby_bank" value="{{ old('nearby_bank', $warehouse->nearby_bank) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Fuel Station</label><input type="text" name="nearby_fuel" value="{{ old('nearby_fuel', $warehouse->nearby_fuel) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-2">Market/Restaurant</label><input type="text" name="nearby_market" value="{{ old('nearby_market', $warehouse->nearby_market) }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"></div>
                    </div>
                </div>

                <!-- Cold Storage -->
                <div class="mb-6 p-4 rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-sky-50 text-sky-600 text-base">
                                <i class="fas fa-snowflake"></i>
                            </span>
                            <div>
                                <h4 class="font-bold text-gray-800 text-sm mb-0">Cold Storage Facility</h4>
                                <p class="text-xs text-gray-500 mb-0">Temperature-controlled storage specifications</p>
                            </div>
                        </div>
                        <label class="kwdc-switch-toggle" for="cold_storage" title="Toggle Cold Storage">
                            <input type="checkbox" name="cold_storage" id="cold_storage" value="1" 
                                   onchange="document.getElementById('cold-storage-fields').style.display = this.checked ? 'grid' : 'none'"
                                   {{ old('cold_storage', $warehouse->cold_storage) ? 'checked' : '' }}>
                            <span class="kwdc-switch-slider"></span>
                        </label>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="cold-storage-fields" style="{{ old('cold_storage', $warehouse->cold_storage) ? '' : 'display: none;' }}">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Min Temperature (°C)</label>
                            <input type="number" step="0.1" name="temperature_min" value="{{ old('temperature_min', $warehouse->temperature_min) }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Temperature (°C)</label>
                            <input type="number" step="0.1" name="temperature_max" value="{{ old('temperature_max', $warehouse->temperature_max) }}" 
                                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div class="flex items-center mt-6">
                            <label class="kwdc-checkbox-pill w-full flex items-center justify-center gap-2 p-2 rounded-lg {{ old('humidity_control', $warehouse->humidity_control) ? 'active' : '' }}" for="humidity_control">
                                <input type="checkbox" name="humidity_control" id="humidity_control" value="1" class="hidden"
                                       onchange="this.parentElement.classList.toggle('active', this.checked)"
                                       {{ old('humidity_control', $warehouse->humidity_control) ? 'checked' : '' }}>
                                <i class="fas fa-droplet text-sky-500"></i>
                                <span class="text-sm font-semibold">Humidity Control</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Photos & Documents -->
                <div class="mb-6">
                    <h4 class="font-bold text-gray-700 border-b pb-2 mb-4">Photos & Documents</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach(['front_image' => 'Front Image', 'interior_image' => 'Interior Image', 'exterior_image' => 'Exterior Image'] as $field => $label)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ $label }}</label>
                                @if($warehouse->$field)
                                    <div class="mb-2">
                                        <img src="{{ Storage::url($warehouse->$field) }}" alt="{{ $label }}" class="h-24 w-auto rounded object-cover border">
                                        <p class="text-xs text-gray-500 mt-1">Current file</p>
                                    </div>
                                @endif
                                <input type="file" name="{{ $field }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <p class="text-xs text-gray-500">Leave empty to keep current image</p>
                            </div>
                        @endforeach

                        @foreach(['ownership_document' => 'Ownership Document', 'tax_document' => 'Tax Document', 'fire_safety_document' => 'Fire Safety Document', 'building_approval_document' => 'Building Approval Document'] as $field => $label)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">{{ $label }}</label>
                                @if($warehouse->$field)
                                    <div class="mb-2 text-sm text-blue-600">
                                        <i class="fas fa-file-pdf mr-1"></i> <a href="{{ Storage::url($warehouse->$field) }}" target="_blank">View Current</a>
                                    </div>
                                @endif
                                <input type="file" name="{{ $field }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <p class="text-xs text-gray-500">Leave empty to keep current document</p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Facilities Array -->
                <div class="mb-6">
                    <h4 class="font-bold text-gray-700 border-b pb-2 mb-4">Facilities</h4>
                    <div id="facilities-container">
                        @php
                            $facilities = is_array($warehouse->facilities) ? $warehouse->facilities : (json_decode($warehouse->facilities, true) ?? []);
                        @endphp
                        @forelse($facilities as $index => $facility)
                            <div class="flex gap-2 mb-2">
                                <input type="text" name="facilities[{{ $index }}]" value="{{ $facility }}" 
                                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="e.g. 24/7 Security">
                                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 px-2">×</button>
                            </div>
                        @empty
                            <div class="flex gap-2 mb-2">
                                <input type="text" name="facilities[0]" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="e.g. 24/7 Security">
                                <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 px-2">×</button>
                            </div>
                        @endforelse
                    </div>
                    <button type="button" onclick="addFacility()" class="text-sm text-blue-500 hover:text-blue-700 mt-1">+ Add another facility</button>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <h4 class="font-bold text-gray-700 border-b pb-2 mb-4">Description</h4>
                    <textarea name="description" rows="4" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">{{ old('description', $warehouse->description) }}</textarea>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="{{ route('admin.warehouses.show', $warehouse->id) }}" class="px-6 py-2 bg-gray-300 rounded-lg hover:bg-gray-400 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 transition">
                        <i class="fas fa-save mr-2"></i> Update Warehouse
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function addFacility() {
        const container = document.getElementById('facilities-container');
        const index = container.children.length;
        const div = document.createElement('div');
        div.className = 'flex gap-2 mb-2';
        div.innerHTML = `
            <input type="text" name="facilities[${index}]" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="e.g. 24/7 Security">
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 px-2">×</button>
        `;
        container.appendChild(div);
    }

    function addCCTVUrl() {
        const container = document.getElementById('cctv-urls-container');
        const index = container.children.length;
        const div = document.createElement('div');
        div.className = 'flex gap-2 mb-2';
        div.innerHTML = `
            <input type="text" name="cctv_stream_urls[${index}]" class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500" placeholder="https://...">
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700 px-2">×</button>
        `;
        container.appendChild(div);
    }
</script>
@endsection