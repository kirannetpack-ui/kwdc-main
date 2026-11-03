@extends('layouts.app')

@section('title', 'Pending Warehouse Approvals | Admin Hub')
@section('header', 'Pending Approvals')

@section('content')
@php
    $totalCount = $pendingWarehouses instanceof \Illuminate\Pagination\AbstractPaginator ? $pendingWarehouses->total() : count($pendingWarehouses ?? []);
    $items = $pendingWarehouses instanceof \Illuminate\Pagination\AbstractPaginator ? $pendingWarehouses->items() : ($pendingWarehouses ?? []);
@endphp

<div class="max-w-7xl mx-auto space-y-4">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3 pb-2 border-b border-[#E8E2D8]">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-serif font-bold text-[#24201D] tracking-tight" style="font-family: 'Newsreader', Georgia, serif;">
                Pending Facility Approvals
            </h2>
            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider bg-amber-50 text-amber-800 border border-amber-200">
                <i class="fas fa-clock text-[9px]"></i>
                {{ $totalCount }} Awaiting Review
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.all-warehouses') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FFFDF9] hover:bg-[#FAF8F5] text-[#24201D] text-xs font-semibold border border-[#E8E2D8] transition shadow-2xs">
                <i class="fas fa-warehouse text-xs text-[#D96B43]"></i>
                <span>All Warehouses</span>
            </a>
            <a href="{{ route('admin.dashboard') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FFFDF9] hover:bg-[#FAF8F5] text-[#24201D] text-xs font-semibold border border-[#E8E2D8] transition shadow-2xs">
                <i class="fas fa-gauge-high text-xs text-[#645D56]"></i>
                <span>Admin Hub</span>
            </a>
        </div>
    </div>
    
    @if(session('success'))
        <div class="rounded-xl p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-circle-check text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    
    @if(session('error'))
        <div class="rounded-xl p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-circle-exclamation text-rose-600"></i>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Main Data Shell -->
    <div class="bg-[#FFFDF9] border border-[#E8E2D8] rounded-2xl p-4 shadow-2xs">
        <div class="w-full overflow-hidden">
            <table class="kwdc-table-fixed">
                <thead>
                    <tr>
                        <th class="w-[220px]">Facility Name</th>
                        <th class="w-[180px]">Location</th>
                        <th class="w-[160px]">Property Owner</th>
                        <th class="w-[130px]">Submitted</th>
                        <th class="w-[180px] text-right">Review Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E2D8]/70 text-xs">
                    @forelse($items as $warehouse)
                    <tr class="hover:bg-[#FAF8F5]/80 transition">
                        <td>
                            <strong class="text-[#24201D] font-semibold block truncate" title="{{ $warehouse->name }}">
                                {{ $warehouse->name }}
                            </strong>
                            <span class="text-[10px] text-[#8C827A] font-mono">
                                ID: #{{ $warehouse->id }} &bull; {{ number_format($warehouse->area_sqft ?? 0) }} sq ft
                            </span>
                        </td>
                        <td class="text-[#645D56] truncate" title="{{ $warehouse->location }}">
                            <i class="fas fa-map-pin text-[#D96B43] mr-1 text-[10px]"></i>
                            {{ $warehouse->location }}
                        </td>
                        <td class="text-[#645D56] truncate">
                            <span class="font-medium text-[#24201D]">{{ optional($warehouse->owner)->name ?? 'Unassigned' }}</span>
                            @if(optional($warehouse->owner)->phone)
                                <span class="block text-[10px] text-[#8C827A]">{{ optional($warehouse->owner)->phone }}</span>
                            @endif
                        </td>
                        <td class="text-[#8C827A]">
                            {{ $warehouse->created_at?->diffForHumans() ?? '—' }}
                        </td>
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('admin.warehouses.show', $warehouse->id) }}" 
                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#FAF8F5] hover:bg-[#F0ECE4] text-[#24201D] text-[11px] font-semibold border border-[#E8E2D8] transition"
                                   title="Inspect Facility Documents">
                                    <i class="fas fa-eye text-[#645D56] text-[10px]"></i>
                                    <span>Inspect</span>
                                </a>
                                
                                <form action="{{ route('admin.approve', $warehouse->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Approve this warehouse facility for active booking?');">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold shadow-2xs transition"
                                            title="Approve Facility">
                                        <i class="fas fa-check text-[10px]"></i>
                                        <span>Approve</span>
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.reject', $warehouse->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Reject this warehouse facility registration?');">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[11px] font-semibold transition"
                                            title="Reject Registration">
                                        <i class="fas fa-times text-[10px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-full bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center text-xl">
                                <i class="fas fa-check-double"></i>
                            </div>
                            <div class="space-y-0.5">
                                <h3 class="text-sm font-bold text-[#24201D]">All Warehouse Facilities Reviewed</h3>
                                <p class="text-xs text-[#645D56]">There are no pending warehouse submissions requiring administrator approval at this time.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pendingWarehouses instanceof \Illuminate\Pagination\AbstractPaginator && $pendingWarehouses->hasPages())
        <div class="pt-4 border-t border-[#E8E2D8]">
            {{ $pendingWarehouses->links() }}
        </div>
        @endif
    </div>
</div>
@endsection