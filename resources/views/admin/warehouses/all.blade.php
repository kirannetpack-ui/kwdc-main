<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">All Warehouses</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if($warehouses->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full border">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border p-2">Name</th>
                                    <th class="border p-2">Owner</th>
                                    <th class="border p-2">Type</th>
                                    <th class="border p-2">Total Capacity</th>
                                    <th class="border p-2">Usable (90%)</th>
                                    <th class="border p-2">Allocated (m²)</th>
                                    <th class="border p-2">Occupancy</th>
                                    <th class="border p-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($warehouses as $wh)
                                @php
                                    $total = $wh->total_capacity ?? 0;
                                    $usable = $wh->usable_capacity ?? 0;
                                    $allocated = $wh->allocated_space ?? 0;
                                    $percent = $total > 0 ? round(($allocated / $total) * 100) : 0;
                                @endphp
                                <tr>
                                    <td class="border p-2">
                                        {{ $wh->name }}<br>
                                        <span class="text-xs text-gray-500">{{ $wh->address ?? 'No address' }}</span>
                                    </td>
                                    <td class="border p-2">
                                        {{ $wh->owner->name ?? 'N/A' }}<br>
                                        {{ $wh->owner->email ?? '' }}
                                    </td>
                                    <td class="border p-2">{{ $wh->type === 'building' ? 'Building' : 'Open Field' }}</td>
                                    <td class="border p-2">
                                        {{ number_format($total, 2) }} {{ $wh->type === 'building' ? 'm³' : 'm²' }}
                                    </td>
                                    <td class="border p-2">
                                        {{ number_format($usable, 2) }} {{ $wh->type === 'building' ? 'm³' : 'm²' }}
                                    </td>
                                    <td class="border p-2">{{ number_format($allocated, 2) }} m²</td>
                                    <td class="border p-2">
                                        <div class="w-full bg-gray-200 rounded-full h-4">
                                            <div class="bg-blue-600 h-4 rounded-full" style="width: {{ $percent }}%"></div>
                                        </div>
                                        <span class="text-sm">{{ $percent }}%</span>
                                    </td>
                                    <td class="border p-2">
                                        <a href="{{ route('admin.warehouse.tenants', $wh->id) }}" class="text-blue-600">View Tenants</a>
                                        <a href="{{ route('warehouses.show', $wh->id) }}" class="text-green-600 ml-2">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p>No warehouses found.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>