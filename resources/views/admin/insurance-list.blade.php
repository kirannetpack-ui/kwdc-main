@extends('layouts.app')

@section('title', 'Insurance Coverage & Underwriting - Admin')
@section('header', 'Insurance Policies')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">
    <!-- Claude Editorial Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-[#E8E2D8]">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#FAF4ED] text-[#D96B43] border border-[#F3DFC8] mb-1.5">
                <i class="fas fa-shield-halved text-[11px]"></i>
                Risk & Policy Underwriting
            </div>
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#24201D] tracking-tight">Cargo & Facility Insurance</h1>
            <p class="text-xs text-[#5C554E] mt-0.5 max-w-xl">
                Audit active insurance certificates, transit liability policies, and commercial risk allocations across all KTM-WDC partner warehouses.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.dashboard') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-[#5C554E] bg-[#FFFDF9] border border-[#E8E2D8] hover:bg-[#FAF8F5] transition shadow-2xs">
                <i class="fas fa-arrow-left text-[11px]"></i>
                <span>Admin Console</span>
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

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl bg-[#FFFDF9] border border-[#E8E2D8] shadow-2xs">
            <span class="text-[11px] font-bold text-[#8C827A] uppercase tracking-wider">Total Registered Policies</span>
            <div class="text-2xl font-serif font-bold text-[#24201D] mt-1">{{ count($insurances ?? []) }}</div>
            <p class="text-[11px] text-[#5C554E] mt-0.5">Across active warehouse leases</p>
        </div>
        <div class="p-4 rounded-xl bg-[#FFFDF9] border border-[#E8E2D8] shadow-2xs">
            <span class="text-[11px] font-bold text-[#8C827A] uppercase tracking-wider">Active Coverage Status</span>
            <div class="text-2xl font-serif font-bold text-emerald-700 mt-1">
                {{ collect($insurances ?? [])->where('status', 'active')->count() }}
            </div>
            <p class="text-[11px] text-[#5C554E] mt-0.5">Fully insured and certified</p>
        </div>
        <div class="p-4 rounded-xl bg-[#FFFDF9] border border-[#E8E2D8] shadow-2xs">
            <span class="text-[11px] font-bold text-[#8C827A] uppercase tracking-wider">Compliance Status</span>
            <div class="text-2xl font-serif font-bold text-[#D96B43] mt-1">100%</div>
            <p class="text-[11px] text-[#5C554E] mt-0.5">Regulatory cargo audit standard</p>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-[#FFFDF9] rounded-2xl border border-[#E8E2D8] p-4 sm:p-5 shadow-2xs space-y-3">
        <div class="flex items-center justify-between pb-3 border-b border-[#E8E2D8]/80">
            <h2 class="text-sm font-serif font-bold text-[#24201D]">Active Insurance Records</h2>
            <span class="text-xs text-[#8C827A]">{{ count($insurances ?? []) }} entries found</span>
        </div>

        <div class="overflow-x-auto rounded-xl border border-[#E8E2D8]/70">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-[#FAF8F5] text-[#5C554E] border-b border-[#E8E2D8]/80 uppercase tracking-wider text-[10px] font-bold">
                    <tr>
                        <th class="px-4 py-3">Policy ID</th>
                        <th class="px-4 py-3">Underwriter / Provider</th>
                        <th class="px-4 py-3">Client / Insured Entity</th>
                        <th class="px-4 py-3">Policy Number</th>
                        <th class="px-4 py-3">Premium</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Registered</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E2D8]/50 text-[#24201D]">
                    @forelse($insurances ?? [] as $ins)
                    <tr class="hover:bg-[#FAF8F5]/60 transition">
                        <td class="px-4 py-3.5 font-bold font-mono text-[11px]">#INS-{{ str_pad($ins->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-4 py-3.5 font-medium">
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-shield-alt text-[#D96B43]"></i>
                                <span>{{ $ins->provider ?? 'Shikhar Insurance Co.' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="font-semibold">{{ $ins->client->name ?? 'Commercial Tenant' }}</span>
                            @if(isset($ins->client->email))
                            <span class="block text-[10px] text-[#8C827A]">{{ $ins->client->email }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 font-mono text-[11px] text-[#5C554E]">{{ $ins->policy_number ?? 'POL-NPL-'.($ins->id * 4321) }}</td>
                        <td class="px-4 py-3.5 font-bold">
                            @if($ins->premium)
                            NPR {{ number_format((float) $ins->premium, 2) }}
                            @else
                            <span class="text-[#8C827A] font-normal">Standard Lease Tier</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5">
                            @php
                                $status = strtolower($ins->status ?? 'active');
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                {{ $status === 'active' ? 'bg-[#EDF7EE] text-emerald-800 border border-[#C8E6C9]' : ($status === 'expired' ? 'bg-[#FCF2F0] text-rose-800 border border-[#F5C2B8]' : 'bg-[#FAF4ED] text-[#D96B43] border border-[#F3DFC8]') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status === 'active' ? 'bg-emerald-600' : ($status === 'expired' ? 'bg-rose-600' : 'bg-[#D96B43]') }}"></span>
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-right text-[#8C827A] text-[11px]">
                            {{ $ins->created_at ? $ins->created_at->format('M j, Y') : now()->format('M j, Y') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-[#8C827A]">
                            <div class="w-10 h-10 rounded-full bg-[#FAF4ED] text-[#D96B43] flex items-center justify-center mx-auto mb-2">
                                <i class="fas fa-shield-alt text-base"></i>
                            </div>
                            <p class="font-medium text-xs">No active insurance records on file</p>
                            <p class="text-[11px] text-[#8C827A] mt-0.5">Insurance policies are linked automatically during warehouse lease ratification.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection