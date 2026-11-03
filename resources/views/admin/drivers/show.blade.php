@extends('layouts.app')

@section('title', 'Driver Details - ' . $driver->name)
@section('header', 'Driver Profile')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <a href="{{ route('admin.drivers') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-orange-600 transition mb-2">
                <i class="fas fa-arrow-left mr-2"></i> Back to Drivers
            </a>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center font-bold text-lg">
                    {{ strtoupper(substr($driver->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">{{ $driver->name }}</h2>
                    <p class="text-sm text-gray-500 font-mono">{{ $driver->user_code ?? ('DRV-' . str_pad($driver->id, 4, '0', STR_PAD_LEFT)) }}</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $driver->is_active ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                <i class="fas {{ $driver->is_active ? 'fa-check-circle' : 'fa-ban' }} mr-1"></i>
                {{ $driver->is_active ? 'Active' : 'Inactive' }}
            </span>
            <a href="{{ route('admin.drivers.edit', $driver->id) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-edit mr-2 text-gray-500"></i> Edit Driver
            </a>
        </div>
    </div>

    <!-- Metrics Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Completed Trips</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $totalTrips ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">NPR {{ number_format($totalEarnings ?? 0, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Driver Rating</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">
                @if($driver->avg_rating)
                    {{ number_format($driver->avg_rating, 1) }} <i class="fas fa-star text-amber-400 text-base"></i>
                @else
                    <span class="text-gray-400 text-lg">5.0 <i class="fas fa-star text-amber-400 text-base"></i></span>
                @endif
            </p>
        </div>
        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-500 tracking-wider">Vehicles Assigned</p>
            <p class="text-2xl font-bold text-gray-900 mt-2">{{ $driver->vehicles->count() }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Driver Profile Info -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center">
                <i class="fas fa-id-card text-orange-500 mr-2"></i> Contact & Details
            </h3>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-gray-500">Email Address</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $driver->email }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Phone Number</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $driver->phone ?? 'Not provided' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Operating Address</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $driver->address ?? 'Kathmandu Valley' }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500">Registered On</dt>
                    <dd class="font-medium text-gray-900 mt-0.5">{{ $driver->created_at?->format('M j, Y (g:i A)') ?? 'N/A' }}</dd>
                </div>
            </dl>

            <!-- Assigned Vehicles -->
            <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 pt-4 flex items-center">
                <i class="fas fa-truck text-orange-500 mr-2"></i> Assigned Vehicle
            </h3>
            @forelse($driver->vehicles as $vehicle)
                <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-sm space-y-1">
                    <p class="font-bold text-gray-900">{{ $vehicle->make }} {{ $vehicle->model }}</p>
                    <p class="text-gray-600 font-mono text-xs">Plate: {{ $vehicle->license_plate ?? $vehicle->plate_number }}</p>
                    <p class="text-gray-500 text-xs">Capacity: {{ $vehicle->capacity_tons ?? $vehicle->capacity ?? 1.5 }} Tons</p>
                </div>
            @empty
                <p class="text-sm text-gray-500 italic">No vehicle directly linked. Assign via Vehicle Management.</p>
            @endforelse
        </div>

        <!-- Recent Dispatches & Trips -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
            <h3 class="text-base font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center justify-between">
                <span class="flex items-center"><i class="fas fa-route text-orange-500 mr-2"></i> Recent Dispatches & Deliveries</span>
                <span class="text-xs font-normal text-gray-500">Latest orders</span>
            </h3>

            @if(isset($driver->dispatchOrders) && $driver->dispatchOrders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-2.5 text-left">Order</th>
                                <th class="px-4 py-2.5 text-left">Route</th>
                                <th class="px-4 py-2.5 text-left">Status</th>
                                <th class="px-4 py-2.5 text-right">Price</th>
                                <th class="px-4 py-2.5 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($driver->dispatchOrders as $dispatch)
                                <tr>
                                    <td class="px-4 py-3 font-mono font-semibold text-gray-900">
                                        #{{ $dispatch->dispatch_number ?? $dispatch->id }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 max-w-xs truncate">
                                        {{ $dispatch->pickup_address }} &rarr; {{ $dispatch->delivery_address }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full 
                                            @if($dispatch->status === 'delivered') bg-green-100 text-green-800
                                            @elseif($dispatch->status === 'on_the_way' || $dispatch->status === 'picked_up') bg-blue-100 text-blue-800
                                            @else bg-amber-100 text-amber-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $dispatch->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900">
                                        NPR {{ number_format($dispatch->base_price, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('dispatch.show', $dispatch->id) }}" class="text-orange-600 hover:text-orange-800 font-medium text-xs">
                                            View &rarr;
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-truck-loading text-3xl text-gray-300 mb-2"></i>
                    <p class="text-sm">No recent dispatch trips recorded for this driver.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
