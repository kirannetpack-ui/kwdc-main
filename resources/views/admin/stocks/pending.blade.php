<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Pending Stock Verifications</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if(session('success'))
                    <div class="bg-green-100 text-green-700 p-3 mb-4">{{ session('success') }}</div>
                @endif

                @if($pendingStocks->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full border">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border p-2">Client</th>
                                    <th class="border p-2">Product</th>
                                    <th class="border p-2">Quantity</th>
                                    <th class="border p-2">Submitted</th>
                                    <th class="border p-2">Verification</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingStocks as $stock)
                                <tr class="border-b">
                                    <td class="border p-2">{{ $stock->warehouseRequest->client->name ?? 'N/A' }}<br>
                                        <span class="text-xs text-gray-500">{{ $stock->warehouseRequest->client->email ?? '' }}</span>
                                    </td>
                                    <td class="border p-2">{{ $stock->product_name }}<br>
                                        @if($stock->sku)<span class="text-xs text-gray-500">SKU: {{ $stock->sku }}</span>@endif
                                    </td>
                                    <td class="border p-2">{{ $stock->quantity }} boxes</td>
                                    <td class="border p-2">{{ $stock->created_at->format('d M Y, h:i A') }}</td>
                                    <td class="border p-2">
                                        <form method="POST" action="{{ route('admin.verify.stock', $stock->id) }}" class="space-y-2">
                                            @csrf
                                            <select name="status" class="border rounded p-1 w-full" required>
                                                <option value="verified">✓ Verified (OK)</option>
                                                <option value="rejected">✗ Rejected (damage/mismatch)</option>
                                            </select>
                                            <textarea name="admin_notes" rows="2" class="border rounded p-1 w-full" placeholder="Notes (damages, missing boxes, etc.)"></textarea>
                                            <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded">Submit</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500 text-center py-4">No pending stock verifications.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>