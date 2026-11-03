<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Assign Warehouses to Request #{{ $warehouseRequest->id }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-4">
                    <strong>Client:</strong> {{ $warehouseRequest->client->name }} ({{ $warehouseRequest->client->email }})<br>
                    <strong>Required space:</strong> {{ $warehouseRequest->required_space }} m³
                </div>

                <h3 class="text-lg font-bold mb-2">Suggested Combination (closest warehouses first)</h3>
                <div class="overflow-x-auto mb-6">
                    <table class="min-w-full border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border p-2">Warehouse</th>
                                <th class="border p-2">Available space</th>
                                <th class="border p-2">Suggested allocation</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suggestion as $item)
                            <tr>
                                <td class="border p-2">{{ $item['warehouse']->name }}</td>
                                <td class="border p-2">{{ number_format($item['warehouse']->total_capacity - $item['warehouse']->allocated_space, 2) }} {{ $item['unit'] }}</td>
                                <td class="border p-2">{{ number_format($item['allocated'], 2) }} {{ $item['unit'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="{{ route('admin.requests.assign-multi.store', $warehouseRequest->id) }}">
                    @csrf

                    {{-- Pricing and Contract Fields --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block font-medium">Agreed Price per unit (NPR)</label>
                            <input type="number" step="0.01" name="agreed_price_per_unit" value="{{ $suggestion[0]['warehouse']->price_per_unit ?? '' }}" class="w-full border-gray-300 rounded">
                        </div>
                        <div>
                            <label class="block font-medium">Security Deposit (NPR)</label>
                            <input type="number" step="0.01" name="security_deposit" class="w-full border-gray-300 rounded">
                        </div>
                        <div>
                            <label class="block font-medium">Contract End Date</label>
                            <input type="date" name="contract_end_date" value="{{ now()->addMonths($warehouseRequest->duration_months)->format('Y-m-d') }}" class="w-full border-gray-300 rounded">
                        </div>
                    </div>

                    <h3 class="text-lg font-bold mb-2">Manual assignment (adjust or confirm)</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full border">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border p-2">Warehouse</th>
                                    <th class="border p-2">Available space</th>
                                    <th class="border p-2">Allocate ({{ $warehouseRequest->required_space }} total)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allWarehouses as $wh)
                                @php
                                    $free = $wh->total_capacity - $wh->allocated_space;
                                    $suggested = null;
                                    foreach ($suggestion as $item) {
                                        if ($item['warehouse']->id == $wh->id) {
                                            $suggested = $item['allocated'];
                                            break;
                                        }
                                    }
                                @endphp
                                <tr class="border-b">
                                    <td class="border p-2">{{ $wh->name }} ({{ $wh->address ?? 'No address' }})</td>
                                    <td class="border p-2">{{ number_format($free, 2) }} {{ $wh->type === 'building' ? 'm³' : 'm²' }}</td>
                                    <td class="border p-2">
                                        <input type="number" name="allocations[{{ $wh->id }}]" value="{{ $suggested ? number_format($suggested, 2) : 0 }}" step="0.01" min="0" max="{{ $free }}" class="w-32 border rounded p-1">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end mt-4">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Confirm Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>