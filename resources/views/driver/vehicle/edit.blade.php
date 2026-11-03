<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Vehicle</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('driver.vehicles.update', $vehicle->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label>Vehicle Type</label>
                        <input type="text" name="type" value="{{ old('type', $vehicle->type) }}" required class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label>Registration Number</label>
                        <input type="text" name="registration_number" value="{{ old('registration_number', $vehicle->registration_number) }}" required class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label>Driver Name</label>
                        <input type="text" name="driver_name" value="{{ old('driver_name', $vehicle->driver_name) }}" required class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label>Driver Phone</label>
                        <input type="text" name="driver_phone" value="{{ old('driver_phone', $vehicle->driver_phone) }}" required class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label>Capacity (boxes)</label>
                        <input type="number" name="capacity_boxes" value="{{ old('capacity_boxes', $vehicle->capacity_boxes) }}" class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label>License Photo (leave empty to keep current)</label>
                        <input type="file" name="license_photo" accept="image/*">
                        @if($vehicle->driver_license_photo)
                            <p class="text-sm text-gray-500 mt-1">Current: <a href="{{ asset('storage/'.$vehicle->driver_license_photo) }}" target="_blank">View</a></p>
                        @endif
                    </div>
                    <div class="flex justify-end space-x-2">
                        <a href="{{ route('driver.vehicles.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</a>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Update Vehicle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>