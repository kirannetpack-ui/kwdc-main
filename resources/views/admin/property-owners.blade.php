@extends('layouts.app')

@section('title', 'Property Owners Registry - Admin')
@section('header', 'Property Owners')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">
    <!-- Claude Editorial Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-[#E8E2D8]">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#FAF4ED] text-[#D96B43] border border-[#F3DFC8] mb-1.5">
                <i class="fas fa-building text-[11px]"></i>
                Real Estate Partner Registry
            </div>
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#24201D] tracking-tight">Registered Property Owners</h1>
            <p class="text-xs text-[#5C554E] mt-0.5 max-w-xl">
                Oversee warehouse landlords, real estate partners, facility capacity allocation, and property verification status.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.dashboard') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-[#5C554E] bg-[#FFFDF9] border border-[#E8E2D8] hover:bg-[#FAF8F5] transition shadow-2xs">
                <i class="fas fa-arrow-left text-[11px]"></i>
                <span>Admin Console</span>
            </a>
            <a href="{{ route('admin.pending') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-white bg-[#D96B43] hover:bg-[#C25A34] transition shadow-xs">
                <i class="fas fa-clock text-[11px]"></i>
                <span>Pending Approvals</span>
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-3.5 rounded-xl bg-[#EDF7EE] border border-[#C8E6C9] text-[#1E4620] text-xs flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="p-3.5 rounded-xl bg-[#FCF2F0] border border-[#F5C2B8] text-[#A6321D] text-xs flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-circle-exclamation text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl bg-[#FFFDF9] border border-[#E8E2D8] shadow-2xs">
            <span class="text-[11px] font-bold text-[#8C827A] uppercase tracking-wider">Total Property Partners</span>
            <div class="text-2xl font-serif font-bold text-[#24201D] mt-1">{{ count($propertyOwners ?? []) }}</div>
            <p class="text-[11px] text-[#5C554E] mt-0.5">Verified landlord accounts</p>
        </div>
        <div class="p-4 rounded-xl bg-[#FFFDF9] border border-[#E8E2D8] shadow-2xs">
            <span class="text-[11px] font-bold text-[#8C827A] uppercase tracking-wider">Total Warehouses</span>
            <div class="text-2xl font-serif font-bold text-[#D96B43] mt-1">
                {{ \App\Models\Warehouse::count() }}
            </div>
            <p class="text-[11px] text-[#5C554E] mt-0.5">Across Kathmandu & provincial hubs</p>
        </div>
        <div class="p-4 rounded-xl bg-[#FFFDF9] border border-[#E8E2D8] shadow-2xs">
            <span class="text-[11px] font-bold text-[#8C827A] uppercase tracking-wider">Approved Facilities</span>
            <div class="text-2xl font-serif font-bold text-emerald-700 mt-1">
                {{ \App\Models\Warehouse::where('status', 'approved')->count() }}
            </div>
            <p class="text-[11px] text-[#5C554E] mt-0.5">Live for client booking</p>
        </div>
    </div>

    <!-- Data Table Shell -->
    <div class="bg-[#FFFDF9] rounded-2xl border border-[#E8E2D8] p-4 sm:p-5 shadow-2xs space-y-3">
        <div class="flex items-center justify-between pb-3 border-b border-[#E8E2D8]/80">
            <h2 class="text-sm font-serif font-bold text-[#24201D]">Landlord Directory</h2>
            <span class="text-xs text-[#8C827A]">{{ count($propertyOwners ?? []) }} property owners</span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-[#E8E2D8]/70">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-[#FAF8F5] text-[#5C554E] border-b border-[#E8E2D8]/80 uppercase tracking-wider text-[10px] font-bold">
                    <tr>
                        <th class="px-4 py-3">Owner ID</th>
                        <th class="px-4 py-3">Landlord Name</th>
                        <th class="px-4 py-3">Email Address</th>
                        <th class="px-4 py-3">Contact Phone</th>
                        <th class="px-4 py-3">Properties</th>
                        <th class="px-4 py-3 text-right">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E2D8]/50 text-[#24201D]">
                    @forelse($propertyOwners ?? [] as $owner)
                    <tr class="hover:bg-[#FAF8F5]/60 transition">
                        <td class="px-4 py-3.5 font-mono text-[11px] text-[#8C827A]">#PO-{{ str_pad($owner->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-[#FAF4ED] text-[#D96B43] flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($owner->name, 0, 1)) }}
                                </div>
                                <span class="font-bold text-[#24201D]">{{ $owner->name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-[#5C554E] font-medium">{{ $owner->email }}</td>
                        <td class="px-4 py-3.5 text-[#5C554E]">
                            @if($owner->phone)
                            <span class="font-mono">{{ $owner->phone }}</span>
                            @else
                            <span class="text-[#8C827A] italic">Not provided</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            @php 
                                $propertyCount = \App\Models\Warehouse::where('user_id', $owner->id)->count(); 
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-[#FAF4ED] text-[#D96B43] border border-[#F3DFC8]">
                                <i class="fas fa-warehouse text-[9px]"></i>
                                {{ $propertyCount }} Facilities
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right text-[#8C827A] text-[11px]">
                            {{ $owner->created_at ? $owner->created_at->format('M j, Y') : 'Active' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-[#8C827A]">
                            <div class="w-10 h-10 rounded-full bg-[#FAF4ED] text-[#D96B43] flex items-center justify-center mx-auto mb-2">
                                <i class="fas fa-building text-base"></i>
                            </div>
                            <p class="font-medium text-xs">No property owners registered yet.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection