@extends('layouts.app')

@section('title', 'Register Vehicle')

@section('header', 'Register New Vehicle')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    
    <form method="POST" action="{{ route('driver.vehicles.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- Basic Vehicle Information -->
        <div class="border-b pb-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-truck text-red-500 mr-2"></i> Basic Vehicle Information
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Vehicle Number *</label>
                    <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                        placeholder="e.g., BA 1 KHA 1234">
                    @error('vehicle_number') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Vehicle Type *</label>
                    <select name="vehicle_type" id="vehicle_type" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                        <option value="">Select Vehicle Type</option>
                        <option value="Bike" {{ old('vehicle_type') == 'Bike' ? 'selected' : '' }}>Bike / Motorcycle</option>
                        <option value="Car" {{ old('vehicle_type') == 'Car' ? 'selected' : '' }}>Car / SUV</option>
                        <option value="Van" {{ old('vehicle_type') == 'Van' ? 'selected' : '' }}>Van</option>
                        <option value="Pickup" {{ old('vehicle_type') == 'Pickup' ? 'selected' : '' }}>Pickup Truck</option>
                        <option value="Mini Truck" {{ old('vehicle_type') == 'Mini Truck' ? 'selected' : '' }}>Mini Truck</option>
                        <option value="Truck" {{ old('vehicle_type') == 'Truck' ? 'selected' : '' }}>Truck / Lorry</option>
                        <option value="Container" {{ old('vehicle_type') == 'Container' ? 'selected' : '' }}>Container Truck</option>
                        <option value="Tractor" {{ old('vehicle_type') == 'Tractor' ? 'selected' : '' }}>Tractor</option>
                        <option value="other" {{ old('vehicle_type') == 'other' ? 'selected' : '' }}>Other (Please specify)</option>
                    </select>
                    @error('vehicle_type') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div id="custom_vehicle_div" style="display: {{ old('vehicle_type') == 'other' ? 'block' : 'none' }};">
                    <label class="block text-gray-700 font-medium mb-2">Specify Vehicle Type *</label>
                    <input type="text" name="custom_vehicle_type" value="{{ old('custom_vehicle_type') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                        placeholder="e.g., Electric Rickshaw, Forklift, etc.">
                    @error('custom_vehicle_type') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Capacity *</label>
                    <input type="number" name="capacity" value="{{ old('capacity') }}" step="0.01" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                        placeholder="e.g., 1000">
                    @error('capacity') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2">Capacity Unit *</label>
                    <select name="capacity_unit" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                        <option value="">Select Unit</option>
                        <option value="kg" {{ old('capacity_unit') == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                        <option value="tons" {{ old('capacity_unit') == 'tons' ? 'selected' : '' }}>Tons (metric)</option>
                        <option value="cubic_meters" {{ old('capacity_unit') == 'cubic_meters' ? 'selected' : '' }}>Cubic Meters (m³)</option>
                        <option value="liters" {{ old('capacity_unit') == 'liters' ? 'selected' : '' }}>Liters</option>
                        <option value="pieces" {{ old('capacity_unit') == 'pieces' ? 'selected' : '' }}>Pieces</option>
                    </select>
                    @error('capacity_unit') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Vehicle Details -->
        <div class="border-b pb-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-car text-blue-500 mr-2"></i> Vehicle Specifications
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Manufacturer</label>
                    <input type="text" name="manufacturer" value="{{ old('manufacturer') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                        placeholder="e.g., Toyota, Tata, Mahindra">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Model</label>
                    <input type="text" name="model" value="{{ old('model') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                        placeholder="e.g., Hilux, Ace, Super">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Year</label>
                    <input type="number" name="year" value="{{ old('year') }}" min="1980" max="{{ date('Y') + 1 }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                        placeholder="e.g., 2020">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Color</label>
                    <input type="text" name="color" value="{{ old('color') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                        placeholder="e.g., White, Red, Blue">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Fuel Type</label>
                    <select name="fuel_type" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                        <option value="">Select Fuel Type</option>
                        <option value="petrol" {{ old('fuel_type') == 'petrol' ? 'selected' : '' }}>Petrol</option>
                        <option value="diesel" {{ old('fuel_type') == 'diesel' ? 'selected' : '' }}>Diesel</option>
                        <option value="electric" {{ old('fuel_type') == 'electric' ? 'selected' : '' }}>Electric</option>
                        <option value="cng" {{ old('fuel_type') == 'cng' ? 'selected' : '' }}>CNG</option>
                        <option value="hybrid" {{ old('fuel_type') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Registration Date</label>
                    <input type="date" name="registration_date" value="{{ old('registration_date') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- Insurance Details -->
        <div class="border-b pb-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-shield-alt text-green-500 mr-2"></i> Insurance Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Insurance Policy Number</label>
                    <input type="text" name="insurance_number" value="{{ old('insurance_number') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                        placeholder="e.g., INS-2024-12345">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Insurance Valid Until</label>
                    <input type="date" name="insurance_valid_until" value="{{ old('insurance_valid_until') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">Upload Insurance Certificate (PDF/Image)</label>
                    <input type="file" name="insurance_file" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                    <p class="text-xs text-gray-500 mt-1">Supported formats: PDF, JPG, JPEG, PNG (Max 5MB)</p>
                </div>
            </div>
        </div>

        <!-- Fitness Certificate -->
        <div class="border-b pb-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-file-alt text-yellow-500 mr-2"></i> Fitness Certificate
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Fitness Certificate Number</label>
                    <input type="text" name="fitness_certificate_number" value="{{ old('fitness_certificate_number') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                        placeholder="e.g., FIT-2024-12345">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Fitness Valid Until</label>
                    <input type="date" name="fitness_valid_until" value="{{ old('fitness_valid_until') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">Upload Fitness Certificate (PDF/Image)</label>
                    <input type="file" name="fitness_file" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- Pollution Certificate -->
        <div class="border-b pb-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-leaf text-green-600 mr-2"></i> Pollution / Emission Certificate
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Pollution Certificate Number</label>
                    <input type="text" name="pollution_certificate_number" value="{{ old('pollution_certificate_number') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                        placeholder="e.g., PUC-2024-12345">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Pollution Valid Until</label>
                    <input type="date" name="pollution_valid_until" value="{{ old('pollution_valid_until') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">Upload Pollution Certificate (PDF/Image)</label>
                    <input type="file" name="pollution_file" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- Permit Details -->
        <div class="border-b pb-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-passport text-purple-500 mr-2"></i> Permit Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Permit Number</label>
                    <input type="text" name="permit_number" value="{{ old('permit_number') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                        placeholder="e.g., PRMT-2024-12345">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Permit Valid Until</label>
                    <input type="date" name="permit_valid_until" value="{{ old('permit_valid_until') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">Upload Permit Document (PDF/Image)</label>
                    <input type="file" name="permit_file" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- Blue Book / Registration Certificate -->
        <div class="border-b pb-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-book text-blue-600 mr-2"></i> Blue Book / Registration Certificate
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Blue Book Number</label>
                    <input type="text" name="blue_book_number" value="{{ old('blue_book_number') }}"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                        placeholder="e.g., BB-123456">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2">Upload Blue Book (PDF/Image)</label>
                    <input type="file" name="blue_book_file" accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- Vehicle Photos -->
        <div class="border-b pb-4 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-camera text-pink-500 mr-2"></i> Vehicle Photos
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Front View Photo</label>
                    <input type="file" name="front_photo" accept=".jpg,.jpeg,.png"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Back View Photo</label>
                    <input type="file" name="back_photo" accept=".jpg,.jpeg,.png"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Left Side Photo</label>
                    <input type="file" name="left_photo" accept=".jpg,.jpeg,.png"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Right Side Photo</label>
                    <input type="file" name="right_photo" accept=".jpg,.jpeg,.png"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Interior Photo</label>
                    <input type="file" name="interior_photo" accept=".jpg,.jpeg,.png"
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-align-left text-gray-500 mr-2"></i> Additional Information
            </h3>
            <div>
                <label class="block text-gray-700 font-medium mb-2">Vehicle Description / Special Notes</label>
                <textarea name="description" rows="4" 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                    placeholder="Any additional information about the vehicle...">{{ old('description') }}</textarea>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="mt-6 flex justify-end space-x-3 pt-4 border-t">
            <a href="{{ route('driver.vehicles.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                <i class="fas fa-save mr-2"></i> Register Vehicle
            </button>
        </div>
    </form>
</div>

<script>
    // Show/hide custom vehicle type field
    const vehicleTypeSelect = document.getElementById('vehicle_type');
    const customVehicleDiv = document.getElementById('custom_vehicle_div');
    
    vehicleTypeSelect.addEventListener('change', function() {
        if (this.value === 'other') {
            customVehicleDiv.style.display = 'block';
        } else {
            customVehicleDiv.style.display = 'none';
        }
    });
</script>
@endsection