<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Insurance Details for Request #{{ $warehouseRequest->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <dl class="grid grid-cols-1 gap-2">
                    <div class="grid grid-cols-2">
                        <dt class="font-bold">Client Name:</dt>
                        <dd>{{ $warehouseRequest->client->name ?? 'N/A' }}</dd>
                    </div>
                    <div class="grid grid-cols-2">
                        <dt class="font-bold">Client Email:</dt>
                        <dd>{{ $warehouseRequest->client->email ?? 'N/A' }}</dd>
                    </div>
                    <div class="grid grid-cols-2">
                        <dt class="font-bold">Required Space:</dt>
                        <dd>{{ $warehouseRequest->required_space }} m³</dd>
                    </div>
                    <div class="grid grid-cols-2">
                        <dt class="font-bold">Duration:</dt>
                        <dd>{{ $warehouseRequest->duration_months }} months</dd>
                    </div>
                    <div class="grid grid-cols-2">
                        <dt class="font-bold">Start Date:</dt>
                        <dd>{{ \Carbon\Carbon::parse($warehouseRequest->created_at)->format('d M Y') }}</dd>
                    </div>
                    <div class="grid grid-cols-2">
                        <dt class="font-bold">End Date (estimated):</dt>
                        <dd>{{ \Carbon\Carbon::parse($warehouseRequest->created_at)->addMonths($warehouseRequest->duration_months)->format('d M Y') }}</dd>
                    </div>
                    <div class="grid grid-cols-2">
                        <dt class="font-bold">Insurance Status:</dt>
                        <dd>
                            @if($warehouseRequest->insurance)
                                <span class="px-2 py-1 rounded text-xs 
                                    @if($warehouseRequest->insurance->status == 'active') bg-green-200 text-green-800
                                    @elseif($warehouseRequest->insurance->status == 'expired') bg-red-200 text-red-800
                                    @else bg-yellow-200 text-yellow-800 @endif">
                                    {{ ucfirst($warehouseRequest->insurance->status) }}
                                </span>
                            @else
                                <span class="text-gray-500">Pending (email sent to insurance company)</span>
                            @endif
                        </dd>
                    </div>
                    @if($warehouseRequest->insurance)
                        <div class="grid grid-cols-2">
                            <dt class="font-bold">Provider:</dt>
                            <dd>{{ $warehouseRequest->insurance->provider ?? 'Not provided' }}</dd>
                        </div>
                        <div class="grid grid-cols-2">
                            <dt class="font-bold">Policy Number:</dt>
                            <dd>{{ $warehouseRequest->insurance->policy_number ?? 'Not provided' }}</dd>
                        </div>
                        <div class="grid grid-cols-2">
                            <dt class="font-bold">Premium:</dt>
                            <dd>{{ $warehouseRequest->insurance->premium ? 'Rs. '.number_format($warehouseRequest->insurance->premium, 2) : 'Not provided' }}</dd>
                        </div>
                        <div class="grid grid-cols-2">
                            <dt class="font-bold">Insurance End Date:</dt>
                            <dd>{{ $warehouseRequest->insurance->end_date ? \Carbon\Carbon::parse($warehouseRequest->insurance->end_date)->format('d M Y') : 'Not set' }}</dd>
                        </div>
                    @endif
                    <div class="grid grid-cols-2">
                        <dt class="font-bold">Invoice:</dt>
                        <dd>
                            @if($warehouseRequest->invoice_path)
                                <a href="{{ route('documents.private.show', ['path' => $warehouseRequest->invoice_path]) }}" target="_blank" class="text-blue-600">Download</a>
                            @else
                                Not uploaded
                            @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-2">
                        <dt class="font-bold">Packing List:</dt>
                        <dd>
                            @if($warehouseRequest->packing_list_path)
                                <a href="{{ route('documents.private.show', ['path' => $warehouseRequest->packing_list_path]) }}" target="_blank" class="text-blue-600">Download</a>
                            @else
                                Not uploaded
                            @endif
                        </dd>
                    </div>
                    <div class="grid grid-cols-2">
                        <dt class="font-bold">Insurance Document:</dt>
                        <dd>
                            @if($warehouseRequest->insurance_path)
                                <a href="{{ route('documents.private.show', ['path' => $warehouseRequest->insurance_path]) }}" target="_blank" class="text-blue-600">Download</a>
                            @else
                                Not uploaded
                            @endif
                        </dd>
                    </div>
                </dl>
                <div class="mt-6">
                    <a href="{{ route('admin.insurance.list') }}" class="text-blue-600 hover:underline">← Back to Insurance List</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
