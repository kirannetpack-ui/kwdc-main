@extends('layouts.app')

@section('title', 'Edit Vehicle')

@section('header', 'Edit Vehicle')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    
    <form method="POST" action="{{ route('driver.vehicles.update', $vehicle->id) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-gray-700 font-medium mb-2">Vehicle Number *</label>
                <input type="text" name="vehicle_number" value="{{ old('vehicle_number', $vehicle->vehicle_number) }}" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                @error('vehicle_number')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Vehicle Type *</label>
                <select name="vehicle_type" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                    <option value="">Select Vehicle Type</option>
                    <option value="Bike" {{ old('vehicle_type', $vehicle->vehicle_type) == 'Bike' ? 'selected' : '' }}>Bike</option>
                    <option value="Car" {{ old('vehicle_type', $vehicle->vehicle_type) == 'Car' ? 'selected' : '' }}>Car</option>
                    <option value="Van" {{ old('vehicle_type', $vehicle->vehicle_type) == 'Van' ? 'selected' : '' }}>Van</option>
                    <option value="Truck" {{ old('vehicle_type', $vehicle->vehicle_type) == 'Truck' ? 'selected' : '' }}>Truck</option>
                    <option value="Pickup" {{ old('vehicle_type', $vehicle->vehicle_type) == 'Pickup' ? 'selected' : '' }}>Pickup</option>
                    <option value="Mini Truck" {{ old('vehicle_type', $vehicle->vehicle_type) == 'Mini Truck' ? 'selected' : '' }}>Mini Truck</option>
                    <option value="Container" {{ old('vehicle_type', $vehicle->vehicle_type) == 'Container' ? 'selected' : '' }}>Container</option>
                </select>
                @error('vehicle_type')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Capacity *</label>
                <input type="number" name="capacity" value="{{ old('capacity', $vehicle->capacity) }}" step="0.01" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                @error('capacity')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Capacity Unit *</label>
                <select name="capacity_unit" required
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                    <option value="">Select Unit</option>
                    <option value="kg" {{ old('capacity_unit', $vehicle->capacity_unit) == 'kg' ? 'selected' : '' }}>Kilograms (kg)</option>
                    <option value="tons" {{ old('capacity_unit', $vehicle->capacity_unit) == 'tons' ? 'selected' : '' }}>Tons</option>
                    <option value="cubic_meters" {{ old('capacity_unit', $vehicle->capacity_unit) == 'cubic_meters' ? 'selected' : '' }}>Cubic Meters</option>
                </select>
                @error('capacity_unit')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">Status</label>
                <select name="status" 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                    <option value="active" {{ old('status', $vehicle->status) == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $vehicle->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-gray-700 font-medium mb-2">Description (Optional)</label>
                <textarea name="description" rows="3" 
                    class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">{{ old('description', $vehicle->description) }}</textarea>
            </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
            <a href="{{ route('driver.vehicles.index') }}" class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-save mr-2"></i> Update Vehicle
            </button>
        </div>
    </form>
</div>
@endsection