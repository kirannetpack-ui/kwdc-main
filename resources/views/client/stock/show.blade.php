<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Stock for Request #{{ $warehouseRequest->id }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if($stocks->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full border">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th>Product</th>
                                    <th>Description</th>
                                    <th>Quantity</th>
                                    <th>SKU</th>
                                    <th>Status</th>
                                    <th>QR Code</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stocks as $stock)
                                <tr>
                                    <td class="border p-2">{{ $stock->product_name }}</td>
                                    <td class="border p-2">{{ $stock->description ?? '-' }}</td>
                                    <td class="border p-2">{{ $stock->quantity }} boxes</td>
                                    <td class="border p-2">{{ $stock->sku ?? '-' }}</td>
                                    <td class="border p-2">{{ ucfirst($stock->status) }}</td>
                                    <td class="border p-2">
                                        @if($stock->qr_code)
                                            <a href="{{ asset('storage/'.$stock->qr_code) }}" target="_blank">View QR</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p>No stock items for this request.</p>
                @endif
                <div class="mt-4">
                    <a href="{{ route('my-requests.index') }}" class="text-blue-600">← Back to My Requests</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>