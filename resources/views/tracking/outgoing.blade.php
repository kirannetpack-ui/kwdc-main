@extends('layouts.app')

@section('title', 'Outgoing Shipments')
@section('header', 'Outgoing Shipments Tracking')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <div class="mb-4">
        @if(auth()->user()->is_admin || auth()->user()->role == 'admin')
        <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded mb-4">
            <p class="text-blue-700"><i class="fas fa-eye mr-2"></i> Admin View: Showing all outgoing shipments</p>
        </div>
        @else
        <div class="bg-green-50 border-l-4 border-green-500 p-3 rounded mb-4">
            <p class="text-green-700"><i class="fas fa-user mr-2"></i> Your View: Showing your outgoing shipments only</p>
        </div>
        @endif
    </div>
    
    <div class="space-y-4">
        @forelse($shipments as $shipment)
        <div class="border rounded-lg p-4 hover:shadow-md transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="font-semibold text-lg">Dispatch #{{ $shipment->id }}</p>
                    @if(auth()->user()->is_admin || auth()->user()->role == 'admin')
                    <p class="text-sm text-gray-600">Client: {{ $shipment->warehouseRequest->client->name ?? 'N/A' }}</p>
                    @endif
                    <p class="text-gray-600">Delivery Address: {{ $shipment->delivery_address ?? 'N/A' }}</p>
                    <p class="text-sm text-gray-500">Created: {{ $shipment->created_at->format('Y-m-d H:i') }}</p>
                </div>
                <div>
                    <span class="status-badge status-{{ $shipment->status ?? 'pending' }}">
                        {{ ucfirst(str_replace('_', ' ', $shipment->status ?? 'Pending')) }}
                    </span>
                </div>
            </div>
            
            @if($shipment->driver)
            <div class="mt-3 pt-3 border-t">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-user-circle text-orange-500 text-2xl"></i>
                    <div>
                        <p class="text-sm font-semibold">Driver: {{ $shipment->driver->name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">Phone: {{ $shipment->driver->phone ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">Vehicle: {{ $shipment->vehicle->plate_number ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Progress Bar -->
            <div class="mt-3">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    @php
                        $progress = [
                            'pending' => 0,
                            'assigned' => 25,
                            'picked_up' => 50,
                            'on_the_way' => 75,
                            'delivered' => 100
                        ];
                        $percentage = $progress[$shipment->status ?? 'pending'] ?? 0;
                    @endphp
                    <div class="bg-orange-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">{{ $percentage }}% Complete</p>
            </div>
        </div>
        @empty
        <div class="text-center py-12">
            <i class="fas fa-truck text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No outgoing shipments found</p>
        </div>
        @endforelse
    </div>
    
    <div class="mt-4">
        {{ $shipments->links() }}
    </div>
</div>

<style>
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-pending { background: #fef3c7; color: #d97706; }
    .status-assigned { background: #dbeafe; color: #2563eb; }
    .status-picked_up { background: #e0e7ff; color: #4338ca; }
    .status-on_the_way { background: #fed7aa; color: #ea580c; }
    .status-delivered { background: #d1fae5; color: #059669; }
</style>
@endsection