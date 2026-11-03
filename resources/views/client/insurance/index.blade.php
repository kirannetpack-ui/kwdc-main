<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Insurance Coverage</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if($insurances->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full border">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border p-2">Request #</th>
                                    <th class="border p-2">Provider</th>
                                    <th class="border p-2">Policy Number</th>
                                    <th class="border p-2">Premium</th>
                                    <th class="border p-2">Start Date</th>
                                    <th class="border p-2">End Date</th>
                                    <th class="border p-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($insurances as $ins)
                                <tr>
                                    <td class="border p-2">#{{ $ins->warehouse_request_id }}</td>
                                    <td class="border p-2">{{ $ins->provider ?? 'Not set' }}</td>
                                    <td class="border p-2">{{ $ins->policy_number ?? 'Not set' }}</td>
                                    <td class="border p-2">{{ $ins->premium ? 'Rs. '.number_format($ins->premium, 2) : 'Not set' }}</td>
                                    <td class="border p-2">{{ \Carbon\Carbon::parse($ins->start_date)->format('d M Y') }}</td>
                                    <td class="border p-2">{{ \Carbon\Carbon::parse($ins->end_date)->format('d M Y') }}</td>
                                    <td class="border p-2">
                                        <span class="px-2 py-1 rounded text-xs 
                                            @if($ins->status == 'active') bg-green-200 text-green-800
                                            @elseif($ins->status == 'expired') bg-red-200 text-red-800
                                            @else bg-yellow-200 text-yellow-800 @endif">
                                            {{ ucfirst($ins->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p>No insurance records found. Insurance is automatically created when you submit a warehouse request.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>