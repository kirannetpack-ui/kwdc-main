<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Add Stock for Request #{{ $warehouseRequest->id }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('client.store-stock', $warehouseRequest->id) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Product Name</label>
                        <input type="text" name="product_name" required class="w-full border-gray-300 rounded">
                        @error('product_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Description (optional)</label>
                        <textarea name="description" class="w-full border-gray-300 rounded"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">Quantity (boxes)</label>
                        <input type="number" name="quantity" required class="w-full border-gray-300 rounded">
                        @error('quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block font-medium text-gray-700">SKU (optional)</label>
                        <input type="text" name="sku" class="w-full border-gray-300 rounded">
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Submit for Verification</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>