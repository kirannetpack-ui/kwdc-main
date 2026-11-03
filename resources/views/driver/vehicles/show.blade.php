@extends('layouts.app')

@section('title', 'Vehicle Details')
@section('header', 'Vehicle Details')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Vehicle: {{ $vehicle->vehicle_number }}</h3>
        <a href="{{ route('driver.vehicles.index') }}" class="text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-2"></i>Back to Vehicles
        </a>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h4 class="font-semibold text-gray-700 mb-2">Basic Information</h4>
            <div class="space-y-2">
                <p><span class="text-gray-500">Vehicle Number:</span> {{ $vehicle->vehicle_number }}</p>
                <p><span class="text-gray-500">Type:</span> {{ $vehicle->full_vehicle_type }}</p>
                <p><span class="text-gray-500">Capacity:</span> {{ $vehicle->capacity }} {{ $vehicle->capacity_unit }}</p>
                <p><span class="text-gray-500">Manufacturer:</span> {{ $vehicle->manufacturer ?? 'N/A' }}</p>
                <p><span class="text-gray-500">Model:</span> {{ $vehicle->model ?? 'N/A' }}</p>
                <p><span class="text-gray-500">Year:</span> {{ $vehicle->year ?? 'N/A' }}</p>
                <p><span class="text-gray-500">Color:</span> {{ $vehicle->color ?? 'N/A' }}</p>
                <p><span class="text-gray-500">Fuel Type:</span> {{ ucfirst($vehicle->fuel_type ?? 'N/A') }}</p>
            </div>
        </div>
        
        <div>
            <h4 class="font-semibold text-gray-700 mb-2">Documentation</h4>
            <div class="space-y-2">
                <p><span class="text-gray-500">Registration Number:</span> {{ $vehicle->registration_number ?? 'N/A' }}</p>
                <p><span class="text-gray-500">Registration Date:</span> {{ $vehicle->registration_date ?? 'N/A' }}</p>
                <p><span class="text-gray-500">Insurance Number:</span> {{ $vehicle->insurance_number ?? 'N/A' }}</p>
                <p><span class="text-gray-500">Insurance Valid Until:</span> {{ $vehicle->insurance_valid_until ?? 'N/A' }}</p>
                <p><span class="text-gray-500">Fitness Certificate:</span> {{ $vehicle->fitness_certificate_number ?? 'N/A' }}</p>
                <p><span class="text-gray-500">Pollution Certificate:</span> {{ $vehicle->pollution_certificate_number ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
    
    <div class="mt-6 pt-4 border-t">
        <div class="flex justify-between items-center">
            <div>
                <span class="text-gray-500">Status:</span>
                @if($vehicle->status == 'active')
                    <span class="ml-2 px-2 py-1 rounded-full text-xs bg-green-100 text-green-600">Active</span>
                @else
                    <span class="ml-2 px-2 py-1 rounded-full text-xs bg-red-100 text-red-600">Inactive</span>
                @endif
                
                <span class="ml-4 text-gray-500">Verification:</span>
                @if($vehicle->is_verified)
                    <span class="ml-2 text-green-600"><i class="fas fa-check-circle"></i> Verified</span>
                @else
                    <span class="ml-2 text-yellow-600"><i class="fas fa-clock"></i> Pending</span>
                @endif
            </div>
            <a href="{{ route('driver.vehicles.edit', $vehicle->id) }}" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition">
                <i class="fas fa-edit mr-2"></i>Edit Vehicle
            </a>
        </div>
    </div>
</div>
@endsection