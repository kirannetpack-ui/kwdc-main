@extends('layouts.app')

@section('title', 'Fleet Compliance & Blue Book Expiry | KTM-WDC Admin')
@section('header', 'Commercial Fleet Regulatory Compliance')

@section('content')
<div class="max-w-7xl mx-auto space-y-3">
    <!-- Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Fleet Compliance & Blue Book Monitor</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-truck-shield text-[9px]"></i>
                {{ $totalVehicles }} Registered Vehicles
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.vehicles') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                <i class="fas fa-truck"></i> All Vehicles
            </a>
            <a href="{{ route('admin.drivers') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                <i class="fas fa-id-card"></i> Driver List
            </a>
        </div>
    </div>

    <!-- KPI Metric Capsules -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
        <div class="bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Fleet</div>
            <div class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $totalVehicles }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Commercial transport units</div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
            <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fas fa-circle-check text-[10px]"></i> Fully Compliant
            </div>
            <div class="text-xl font-extrabold text-emerald-700 mt-0.5">{{ $compliantCount }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Active highway clearance</div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
            <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fas fa-triangle-exclamation text-[10px]"></i> Expiring (< 15 Days)
            </div>
            <div class="text-xl font-extrabold text-amber-700 mt-0.5">{{ $expiringCount }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Urgent renewal required</div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
            <div class="text-[11px] font-bold text-rose-600 uppercase tracking-wider flex items-center gap-1.5">
                <i class="fas fa-ban text-[10px]"></i> Expired / Grounded
            </div>
            <div class="text-xl font-extrabold text-rose-700 mt-0.5">{{ $expiredCount }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Dispatch barred by policy</div>
        </div>
    </div>

    <!-- Vehicles Compliance Table -->
    <div class="bg-white border border-slate-200/80 rounded-xl overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-2.5 px-3">Vehicle / Plate</th>
                        <th class="py-2.5 px-3">Driver / Owner</th>
                        <th class="py-2.5 px-3">Blue Book & Fitness</th>
                        <th class="py-2.5 px-3">Insurance Policy</th>
                        <th class="py-2.5 px-3">Route Permit</th>
                        <th class="py-2.5 px-3">Compliance Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($vehicles as $vehicle)
                    @php
                        $issues = $vehicle->getComplianceIssues();
                        $hasExpired = collect($issues)->contains('status', 'expired');
                        $hasExpiring = collect($issues)->contains('status', 'expiring');
                    @endphp
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="py-2.5 px-3">
                            <div class="font-bold text-slate-900">{{ $vehicle->vehicle_number ?: 'Unregistered' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $vehicle->vehicle_type ?? 'Freight Carrier' }} ({{ $vehicle->manufacturer ?? 'Generic' }})</div>
                        </td>
                        <td class="py-2.5 px-3">
                            <div class="font-medium text-slate-900">{{ $vehicle->driver->name ?? 'Unassigned' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $vehicle->driver->phone ?? 'No phone' }}</div>
                        </td>
                        <td class="py-2.5 px-3">
                            @if($vehicle->fitness_valid_until)
                                @php $isPast = $vehicle->fitness_valid_until < now(); @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold {{ $isPast ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-50 text-slate-700 border border-slate-200' }}">
                                    {{ $vehicle->fitness_valid_until->format('M d, Y') }}
                                    @if($isPast) (Expired) @endif
                                </span>
                            @else
                                <span class="text-slate-400 text-[11px]">Not recorded</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3">
                            @if($vehicle->insurance_valid_until)
                                @php $isPast = $vehicle->insurance_valid_until < now(); @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold {{ $isPast ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-50 text-slate-700 border border-slate-200' }}">
                                    {{ $vehicle->insurance_valid_until->format('M d, Y') }}
                                    @if($isPast) (Expired) @endif
                                </span>
                            @else
                                <span class="text-slate-400 text-[11px]">Not recorded</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3">
                            @if($vehicle->permit_valid_until)
                                @php $isPast = $vehicle->permit_valid_until < now(); @endphp
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold {{ $isPast ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-slate-50 text-slate-700 border border-slate-200' }}">
                                    {{ $vehicle->permit_valid_until->format('M d, Y') }}
                                    @if($isPast) (Expired) @endif
                                </span>
                            @else
                                <span class="text-slate-400 text-[11px]">Not recorded</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3">
                            @if($hasExpired)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <i class="fas fa-circle-xmark"></i> Grounded
                                </span>
                            @elseif($hasExpiring)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="fas fa-clock"></i> Expiring Soon
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fas fa-circle-check"></i> Compliant
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-truck text-2xl text-slate-300 block mb-2"></i>
                            No fleet vehicles registered yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vehicles->hasPages())
        <div class="px-3.5 py-2.5 border-t border-slate-100 bg-slate-50/50">
            {{ $vehicles->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
