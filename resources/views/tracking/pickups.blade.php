@extends('layouts.app')

@section('title', 'Pickup Requests')
@section('header', 'Pickup Requests Tracking')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <div class="mb-4">
        @if(auth()->user()->is_admin || auth()->user()->role == 'admin')
        <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded mb-4">
            <p class="text-blue-700"><i class="fas fa-eye mr-2"></i> Admin View: Showing all pickup requests</p>
        </div>
        @else
        <div class="bg-green-50 border-l-4 border-green-500 p-3 rounded mb-4">
            <p class="text-green-700"><i class="fas fa-user mr-2"></i> Your View: Showing your pickup requests only</p>
        </div>
        @endif
    </div>
    
    <div class="space-y-4">
        @forelse($pickups as $pickup)
        <div class="border rounded-lg p-4 hover:shadow-md transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="font-semibold text-lg">Pickup #{{ $pickup->id }}</p>
                    @if(auth()->user()->is_admin || auth()->user()->role == 'admin')
                    <p class="text-sm text-gray-600">Client: {{ $pickup->client->name ?? 'N/A' }}</p>
                    @endif
                    <p class="text-gray-600">Pickup Address: {{ $pickup->pickup_address }}</p>
                    <p class="text-sm text-gray-500">Requested: {{ $pickup->created_at->format('Y-m-d H:i') }}</p>
                </div>
                <div>
                    <span class="status-badge status-{{ $pickup->status ?? 'pending' }}">
                        {{ ucfirst($pickup->status ?? 'Pending') }}
                    </span>
                </div>
            </div>
            
            @if($pickup->driver)
            <div class="mt-3 pt-3 border-t">
                <div class="flex items-center space-x-4">
                    <i class="fas fa-truck text-orange-500"></i>
                    <div>
                        <p class="text-sm font-semibold">Driver Assigned: {{ $pickup->driver->name ?? 'N/A' }}</p>
                        <p class="text-xs text-gray-500">Contact: {{ $pickup->driver->phone ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-12">
            <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500">No pickup requests found</p>
        </div>
        @endforelse
    </div>
    
    <div class="mt-4">
        {{ $pickups->links() }}
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
    .status-completed { background: #d1fae5; color: #059669; }
</style>
@endsection