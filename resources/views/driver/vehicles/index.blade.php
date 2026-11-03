@extends('layouts.app')

@section('title', 'My Vehicles')
@section('header', 'My Vehicles')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Registered Vehicles</h3>
            <p class="text-sm text-gray-500 mt-1">Manage your vehicle fleet and documentation</p>
        </div>
        <a href="{{ route('driver.vehicles.create') }}" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition flex items-center">
            <i class="fas fa-plus mr-2"></i> Register New Vehicle
        </a>
    </div>

    @if(isset($vehicles) && $vehicles->count() > 0)
    <div class="overflow-hidden rounded-xl border border-slate-200/80 mt-2.5">
        <table class="kwdc-table-fixed text-left">
            <thead class="bg-slate-50 border-b border-slate-200/80">
                <tr>
                    <th class="w-[20%]">Vehicle Number</th>
                    <th class="w-[22%]">Type & Model</th>
                    <th class="w-[14%]">Capacity</th>
                    <th class="w-[18%]">Registration & Docs</th>
                    <th class="w-[14%]">Status</th>
                    <th class="w-[12%] text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($vehicles as $vehicle)
                <tr class="hover:bg-slate-50/80 transition">
                    <td class="font-bold text-slate-900 truncate" title="{{ $vehicle->vehicle_number }}">
                        <span class="font-mono text-xs">{{ $vehicle->vehicle_number }}</span>
                        <p class="text-[10.5px] text-slate-400 font-normal m-0">ID: {{ $vehicle->registration_number ?? 'N/A' }}</p>
                    </td>
                    <td class="text-xs text-slate-700 truncate" title="{{ $vehicle->full_vehicle_type ?? $vehicle->vehicle_type }}">
                        <span class="font-semibold text-slate-800">{{ $vehicle->full_vehicle_type ?? $vehicle->vehicle_type }}</span>
                        @if($vehicle->manufacturer || $vehicle->model)
                            <p class="text-[10.5px] text-slate-400 m-0">{{ $vehicle->manufacturer }} {{ $vehicle->model }}</p>
                        @endif
                    </td>
                    <td class="text-xs text-slate-700 truncate">
                        <span class="font-medium">{{ number_format($vehicle->capacity) }} {{ $vehicle->capacity_unit }}</span>
                    </td>
                    <td class="text-xs text-slate-600 truncate">
                        <div>Reg: {{ $vehicle->registration_date ? date('M d, Y', strtotime($vehicle->registration_date)) : '-' }}</div>
                        @if($vehicle->insurance_valid_until)
                            <div class="text-[10.5px] text-{{ \Carbon\Carbon::parse($vehicle->insurance_valid_until)->isPast() ? 'rose' : 'emerald' }}-600">
                                Ins: {{ date('M d, Y', strtotime($vehicle->insurance_valid_until)) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($vehicle->status == 'active')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span> Active
                            </span>
                        @elseif($vehicle->status == 'pending')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border bg-amber-50 text-amber-700 border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span> Pending
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border bg-slate-100 text-slate-700 border-slate-200">
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td class="text-right">
                        <div class="inline-flex items-center justify-end gap-1">
                            <a href="{{ route('driver.vehicles.show', $vehicle->id) }}" 
                               class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-600 transition border border-slate-200 shadow-2xs" 
                               title="View Details">
                                <i class="fas fa-eye text-[9px]"></i>
                            </a>
                            <a href="{{ route('driver.vehicles.edit', $vehicle->id) }}" 
                               class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-600 transition border border-slate-200 shadow-2xs" 
                               title="Edit Vehicle">
                                <i class="fas fa-pen text-[9px]"></i>
                            </a>
                            <form method="POST" action="{{ route('driver.vehicles.destroy', $vehicle->id) }}" 
                                  class="inline" 
                                  onsubmit="return confirm('Delete this vehicle?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-rose-600 hover:text-white text-rose-500 transition border border-slate-200 shadow-2xs" title="Delete Vehicle">
                                    <i class="fas fa-trash text-[9px]"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $vehicles->links() }}
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6 pt-4 border-t">
        <div class="bg-gray-50 rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $vehicles->total() }}</p>
            <p class="text-xs text-gray-500">Total Vehicles</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-green-600">
                {{ $vehicles->filter(function($v) { return $v->status == 'active' && $v->is_verified; })->count() }}
            </p>
            <p class="text-xs text-gray-500">Active & Verified</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-3 text-center">
            <p class="text-2xl font-bold text-yellow-600">
                {{ $vehicles->filter(function($v) { return $v->status == 'pending' || !$v->is_verified; })->count() }}
            </p>
            <p class="text-xs text-gray-500">Pending Approval</p>
        </div>
    </div>
    
    @else
    <div class="text-center py-12 bg-gray-50 rounded-lg">
        <i class="fas fa-truck text-gray-400 text-5xl mb-4"></i>
        <p class="text-gray-500 text-lg">No vehicles registered yet</p>
        <p class="text-gray-400 text-sm mt-2">Click "Register New Vehicle" to add your vehicle</p>
        <div class="mt-6">
            <a href="{{ route('driver.vehicles.create') }}" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition inline-flex items-center">
                <i class="fas fa-plus mr-2"></i> Register Your First Vehicle
            </a>
        </div>
    </div>
    @endif
</div>

<script>
function confirmDelete() {
    return confirm('Are you sure you want to delete this vehicle? This action cannot be undone.');
}
</script>
@endsection