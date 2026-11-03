<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Equipment Jobs</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                @if(session('success'))
                    <div class="bg-green-100 text-green-700 p-3 mb-4">{{ session('success') }}</div>
                @endif

                @if($jobs->count())
                    <div class="overflow-x-auto">
                        <table class="min-w-full border">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border p-2">Request #</th>
                                    <th class="border p-2">Client</th>
                                    <th class="border p-2">Equipment Notes</th>
                                    <th class="border p-2">Assigned Equipment</th>
                                    <th class="border p-2">Status</th>
                                    <th class="border p-2">Agreed Price</th>
                                    <th class="border p-2">Partner Payment</th>
                                    <th class="border p-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jobs as $job)
                                <tr>
                                    <td class="border p-2">#{{ $job->warehouse_request_id }}</td>
                                    <td class="border p-2">{{ $job->warehouseRequest->client->name ?? 'N/A' }}<br>{{ $job->warehouseRequest->client->email ?? '' }}</td>
                                    <td class="border p-2">{{ $job->notes ?? '-' }}</td>
                                    <td class="border p-2">{{ $job->equipment->type ?? 'Not assigned' }} (@if($job->equipment) {{ $job->equipment->model }} @endif)</td>
                                    <td class="border p-2">{{ ucfirst($job->status) }}</td>
                                    <td class="border p-2">@if($job->agreed_price) Rs. {{ number_format($job->agreed_price, 2) }} @else - @endif</td>
                                    <td class="border p-2">
                                        @if($job->status == 'completed')
                                            @if($job->partner_paid)
                                                <span class="text-green-600 font-semibold">Paid</span>
                                            @else
                                                <form method="POST" action="{{ route('admin.jobs.mark-paid', ['type' => 'equipment', 'id' => $job->id]) }}">
                                                    @csrf
                                                    <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded">Mark Paid</button>
                                                </form>
                                            @endif
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="border p-2">
                                        @if(!$job->equipment_id && $job->status == 'pending')
                                            <form method="POST" action="{{ route('admin.equipment.jobs.assign', $job->id) }}">
                                                @csrf
                                                <select name="equipment_id" required>
                                                    <option value="">Select equipment</option>
                                                    @foreach($equipment as $eq)
                                                        <option value="{{ $eq->id }}">{{ $eq->type }} ({{ $eq->model }})</option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="bg-blue-500 text-white px-2 py-1 rounded">Assign</button>
                                            </form>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p>No equipment jobs.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>