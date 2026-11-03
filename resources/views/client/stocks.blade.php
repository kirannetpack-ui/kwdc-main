<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Stock for Request #{{ $warehouseRequest->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-4">
                    <p><strong>Assigned Warehouse:</strong> {{ $warehouseRequest->assignedWarehouse->name ?? 'N/A' }}</p>
                    <p><strong>Required Space:</strong> {{ $warehouseRequest->required_space }} m³</p>
                    <p><strong>Duration:</strong> {{ $warehouseRequest->duration_months }} months</p>
                </div>

                @if($stocks->count())
                    <table class="min-w-full border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border p-2">Product Name</th>
                                <th class="border p-2">Description</th>
                                <th class="border p-2">Quantity</th>
                                <th class="border p-2">SKU</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stocks as $stock)
                            <tr>
                                <td class="border p-2">{{ $stock->product_name }}</td>
                                <td class="border p-2">{{ $stock->description ?? '-' }}</td>
                                <td class="border p-2">{{ $stock->quantity }}</td>
                                <td class="border p-2">{{ $stock->sku ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-gray-500">No stock has been recorded for this request yet.</p>
                @endif

                <div class="mt-6">
                    <a href="{{ route('my-requests.index') }}" class="text-blue-600 hover:underline">← Back to My Requests</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>