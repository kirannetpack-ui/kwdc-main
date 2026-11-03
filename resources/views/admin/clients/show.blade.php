@extends('layouts.app')

@section('title', 'Client - ' . $client->name)
@section('header', 'Client Profile')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Breadcrumb & Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('admin.clients') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-orange-600 transition mb-2">
                <i class="fas fa-arrow-left mr-2"></i> Back to Clients
            </a>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg">
                    {{ strtoupper(substr($client->name, 0, 2)) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $client->name }}</h1>
                    <p class="text-xs text-gray-500 font-mono">{{ $client->user_code ?? ('CLI-' . str_pad($client->id, 4, '0', STR_PAD_LEFT)) }}</p>
                </div>
            </div>
        </div>

        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $client->is_active ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
            <i class="fas {{ $client->is_active ? 'fa-check-circle' : 'fa-ban' }} mr-1"></i>
            {{ $client->is_active ? 'Active Account' : 'Inactive' }}
        </span>
    </div>

    <!-- Client Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-bold uppercase text-gray-400 tracking-wider">Total Requests</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $stats['total_requests'] ?? $client->warehouseRequests->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-bold uppercase text-gray-400 tracking-wider">Approved Requests</p>
            <p class="text-2xl font-bold text-green-600 mt-2">{{ $stats['approved_requests'] ?? $client->warehouseRequests->where('status', 'approved')->count() }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-bold uppercase text-gray-400 tracking-wider">Active Warehouses</p>
            <p class="text-2xl font-bold text-blue-600 mt-2">
                {{ $client->warehouseRequests->where('status', 'approved')->pluck('warehouse_id')->unique()->count() }}
            </p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-bold uppercase text-gray-400 tracking-wider">Total Stored SKUs</p>
            <p class="text-2xl font-bold text-purple-600 mt-2">
                {{ $client->warehouseRequests->flatMap->stocks->count() }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Client Profile Details -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center">
                <i class="fas fa-user text-orange-500 mr-2"></i> Client Information
            </h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-xs text-gray-400 font-semibold uppercase">Email Address</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $client->email }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 font-semibold uppercase">Phone Number</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $client->phone ?? 'Not provided' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 font-semibold uppercase">Business Address</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $client->address ?? 'Kathmandu, Nepal' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 font-semibold uppercase">Member Since</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $client->created_at?->format('F j, Y') ?? 'N/A' }}</dd>
                </div>
            </dl>
        </div>

        <!-- Warehouse Requests & Storage History -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center justify-between">
                    <span class="flex items-center"><i class="fas fa-warehouse text-orange-500 mr-2"></i> Warehouse Requests</span>
                    <span class="text-xs text-gray-500">{{ $client->warehouseRequests->count() }} total</span>
                </h2>

                @if($client->warehouseRequests->count())
                    <div class="space-y-3">
                        @foreach($client->warehouseRequests as $req)
                            <div class="p-4 rounded-2xl bg-gray-50 border border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('warehouse-requests.show', $req->id) }}" class="font-bold text-gray-900 hover:text-orange-600 font-mono">
                                            Request #{{ $req->id }}
                                        </a>
                                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                            @if($req->status === 'approved') bg-green-100 text-green-800
                                            @elseif($req->status === 'rejected') bg-red-100 text-red-800
                                            @else bg-amber-100 text-amber-800 @endif">
                                            {{ ucfirst($req->status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 mt-1">
                                        Warehouse: <span class="font-medium">{{ $req->assignedWarehouse?->name ?? $req->warehouse?->name ?? 'Not assigned' }}</span> &bull; 
                                        Space: <span class="font-medium">{{ number_format((float) ($req->required_area ?? $req->space_required ?? 0)) }} sq ft</span> &bull; 
                                        Duration: <span class="font-medium">{{ $req->duration_months ?? 1 }} mos</span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('warehouse-requests.show', $req->id) }}" class="px-3 py-1.5 bg-white border border-gray-200 text-xs font-semibold text-gray-700 rounded-lg hover:bg-gray-100 transition shadow-sm">
                                        View Details &rarr;
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 text-sm">
                        No warehouse requests found for this client.
                    </div>
                @endif
            </div>

            <!-- Client Stored Inventory / Stocks -->
            @php $allStocks = $client->warehouseRequests->flatMap->stocks; @endphp
            @if($allStocks->count())
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <h2 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center justify-between">
                        <span class="flex items-center"><i class="fas fa-boxes-stacked text-orange-500 mr-2"></i> Inventory & Stored Stocks</span>
                        <span class="text-xs text-gray-500">{{ $allStocks->count() }} items</span>
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-2.5 text-left">Product</th>
                                    <th class="px-4 py-2.5 text-left">SKU</th>
                                    <th class="px-4 py-2.5 text-right">Quantity</th>
                                    <th class="px-4 py-2.5 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($allStocks as $stock)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $stock->product_name ?? $stock->name }}</td>
                                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $stock->sku ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 text-right font-semibold text-gray-900">{{ $stock->quantity }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Stored
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
