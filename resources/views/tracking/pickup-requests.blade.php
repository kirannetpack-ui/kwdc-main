@extends('layouts.app')

@section('title', 'Pickup Requests')
@section('header', 'My Pickup Requests')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">Pickup Requests</h3>
    
    <div class="space-y-4">
        @forelse($pickups ?? [] as $pickup)
        <div class="border rounded-lg p-4">
            <div class="flex justify-between items-start">
                <div>
                    <p class="font-semibold">Request #{{ $pickup->id }}</p>
                    <p class="text-gray-600">Pickup Address: {{ $pickup->pickup_address }}</p>
                    <p class="text-gray-600">Status: {{ ucfirst($pickup->status) }}</p>
                </div>
                <a href="{{ route('tracking.shipment', $pickup->id) }}" class="text-orange-500">Track →</a>
            </div>
        </div>
        @empty
        <p class="text-gray-500 text-center">No pickup requests found</p>
        @endforelse
    </div>
</div>
@endsection