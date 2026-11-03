<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Register Vehicle</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('driver.vehicles.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label class="block font-medium">Vehicle Type (e.g., Cycle, Bike, Van, Truck, Tractor)</label>
                        <input type="text" name="type" required class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label>Registration Number</label>
                        <input type="text" name="registration_number" required class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label>Driver Name</label>
                        <input type="text" name="driver_name" required class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label>Driver Phone</label>
                        <input type="text" name="driver_phone" required class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label>Capacity (boxes, optional)</label>
                        <input type="number" name="capacity_boxes" class="w-full border-gray-300 rounded">
                    </div>
                    <div class="mb-4">
                        <label>License Photo (optional)</label>
                        <input type="file" name="license_photo" accept="image/*">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Register Vehicle</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>