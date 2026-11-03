@extends('layouts.app')

@section('title', 'Warehouse Tenants')
@section('header', 'Warehouse Tenants')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-6">Current Warehouse Tenants</h3>
    
    <div class="space-y-4">
        @forelse($warehouses ?? [] as $warehouse)
        <div class="border rounded-lg p-4">
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="font-bold text-lg">{{ $warehouse->name }}</h4>
                    <p class="text-sm text-gray-500">{{ $warehouse->location }}</p>
                </div>
                <span class="status-badge status-active">Active</span>
            </div>
            <div class="mt-4">
                <p class="text-sm font-semibold">Tenants:</p>
                <ul class="list-disc list-inside mt-2">
                    @forelse($warehouse->tenants ?? [] as $tenant)
                    <li class="text-sm text-gray-600">{{ $tenant->name }} - {{ $tenant->phone }}</li>
                    @empty
                    <li class="text-sm text-gray-500">No tenants</li>
                    @endforelse
                </ul>
            </div>
        </div>
        @empty
        <p class="text-gray-500 text-center py-8">No warehouses found</p>
        @endforelse
    </div>
</div>
@endsection