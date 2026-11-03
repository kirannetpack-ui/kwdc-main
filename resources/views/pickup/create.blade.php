<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Request Pickup</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('pickup.store') }}">
                    @csrf

                    <!-- Pickup Address -->
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Pickup Address</label>
                        <input type="text" name="pickup_address" required class="w-full border-gray-300 rounded">
                        @error('pickup_address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Destination Warehouse (optional) -->
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Destination Warehouse (optional)</label>
                        <select name="destination_warehouse_id" class="w-full border-gray-300 rounded">
                            <option value="">-- None --</option>
                            @foreach($warehouses as $wh)
                                <option value="{{ $wh->id }}">{{ $wh->name }} ({{ $wh->address ?? 'No address' }})</option>
                            @endforeach
                        </select>
                        @error('destination_warehouse_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Description (what to pick)</label>
                        <textarea name="description" rows="3" class="w-full border-gray-300 rounded"></textarea>
                    </div>

                    <!-- Estimated Boxes -->
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Estimated Boxes</label>
                        <input type="number" name="estimated_boxes" required class="w-full border-gray-300 rounded">
                        @error('estimated_boxes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Contact Person -->
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Contact Person</label>
                        <input type="text" name="contact_person" required class="w-full border-gray-300 rounded">
                        @error('contact_person') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Contact Phone -->
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Contact Phone</label>
                        <input type="text" name="contact_phone" required class="w-full border-gray-300 rounded">
                        @error('contact_phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Submit Pickup Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>