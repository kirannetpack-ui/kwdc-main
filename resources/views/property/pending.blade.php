@extends('layouts.app')

@section('title', 'Pending Properties')
@section('header', 'Pending Approval')

@section('content')
@php
    $totalCount = $pendingWarehouses instanceof \Illuminate\Pagination\AbstractPaginator ? $pendingWarehouses->total() : count($pendingWarehouses ?? []);
    $items = $pendingWarehouses instanceof \Illuminate\Pagination\AbstractPaginator ? $pendingWarehouses->items() : ($pendingWarehouses ?? []);
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Pending Approval</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200/80">
                <i class="fas fa-clock text-[9px]"></i>
                {{ $totalCount }} Properties
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('property.approved') }}" 
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold transition border border-slate-200 shadow-2xs">
                <i class="fas fa-check-circle text-emerald-500 text-[10px]"></i>
                <span>Approved</span>
            </a>
            <a href="{{ route('property.rejected') }}" 
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold transition border border-slate-200 shadow-2xs">
                <i class="fas fa-times-circle text-rose-500 text-[10px]"></i>
                <span>Rejected</span>
            </a>
            <a href="{{ route('warehouses.create') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-plus text-[10px]"></i>
                <span>Register Facility</span>
            </a>
        </div>
    </div>

    <!-- Main Data Shell -->
    <div class="bg-white border border-slate-200/80 rounded-xl p-3 sm:p-3.5 shadow-2xs">
        <!-- Search Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
            <div class="text-xs font-semibold text-slate-600">
                Properties awaiting verification & compliance check
            </div>
            
            <div class="relative w-full sm:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="propPendingSearchInput" 
                       placeholder="Search facility name, location..." 
                       oninput="applyPropPendingFilter()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="propPendingSearchClear" 
                        onclick="clearPropPendingSearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Table with Zero Sideways Scroll -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="propPendingTable">
                <thead>
                    <tr>
                        <th class="w-[60px]">#</th>
                        <th class="w-[240px]">Facility Name</th>
                        <th class="w-[180px]">Location</th>
                        <th class="w-[120px]">Area (sq ft)</th>
                        <th class="w-[120px]">Submitted</th>
                        <th class="w-[100px]">Status</th>
                        <th class="w-[80px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="propPendingTableBody">
                    @forelse($items as $warehouse)
                    <tr class="hover:bg-slate-50/70 transition prop-pending-row"
                        data-search="{{ strtolower($warehouse->name . ' ' . $warehouse->location . ' ' . ($warehouse->owner_name ?? '')) }}">
                        
                        <td class="font-mono text-slate-500 font-semibold">
                            {{ $loop->iteration }}
                        </td>

                        <td>
                            <strong class="text-slate-900 font-semibold block truncate" title="{{ $warehouse->name }}">{{ $warehouse->name }}</strong>
                            <span class="text-[10px] text-slate-500">
                                Owner: {{ $warehouse->owner_name ?? 'N/A' }}
                            </span>
                        </td>

                        <td>
                            <div class="truncate text-slate-600" title="{{ $warehouse->location }}">
                                <i class="fas fa-map-marker-alt text-orange-400 text-[10px] me-1"></i>
                                {{ \Illuminate\Support\Str::limit($warehouse->location, 30) }}
                            </div>
                        </td>

                        <td class="font-semibold text-slate-800">
                            {{ $warehouse->area_sqft ? number_format((float) $warehouse->area_sqft) . ' sq ft' : 'N/A' }}
                        </td>

                        <td class="text-slate-500 text-[11px]">
                            {{ $warehouse->created_at ? $warehouse->created_at->format('M d, Y') : '—' }}
                        </td>

                        <td>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border bg-amber-50 text-amber-700 border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Pending
                            </span>
                        </td>

                        <td class="text-right">
                            <a href="{{ route('property.warehouses.show', $warehouse->id) }}" 
                               class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 transition"
                               title="View Facility">
                                <i class="fas fa-eye text-[11px]"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-check-circle text-2xl text-emerald-500 mb-1.5"></i>
                                <span class="text-xs font-semibold text-slate-700">No properties pending approval</span>
                                <span class="text-[11px] text-slate-400 mt-0.5">All your registered properties have been reviewed.</span>
                                <a href="{{ route('warehouses.create') }}" class="mt-2 text-xs text-orange-600 font-bold hover:underline">
                                    Register New Property &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyPendingSearch" class="hidden">
                        <td colspan="7" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No pending facilities match your search</span>
                                <button type="button" onclick="clearPropPendingSearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
                                    Clear search
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 5-Row Pagination Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2.5 mt-2 border-t border-slate-100 text-xs text-slate-500">
            <div>
                Showing <span id="pendingRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="pendingTotal" class="font-bold text-slate-800">{{ count($items) }}</span> facilities
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="pendingPagination">
                <button type="button" id="pendingPrevBtn" onclick="prevPendingPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="pendingPageNumbers" class="flex items-center gap-1"></div>
                <button type="button" id="pendingNextBtn" onclick="nextPendingPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentPendingPage = 1;
const PENDING_PER_PAGE = 5;

function clearPropPendingSearch() {
    const input = document.getElementById('propPendingSearchInput');
    if (input) input.value = '';
    applyPropPendingFilter();
}

function applyPropPendingFilter() {
    const searchVal = (document.getElementById('propPendingSearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('propPendingSearchClear');
    if (clearBtn) clearBtn.classList.toggle('visible', searchVal.length > 0);

    const rows = Array.from(document.querySelectorAll('.prop-pending-row'));
    const matched = [];

    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        if (!searchVal || text.includes(searchVal)) {
            matched.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const empty = document.getElementById('emptyPendingSearch');
    if (empty) empty.classList.toggle('hidden', matched.length > 0 || rows.length === 0);

    paginatePending(matched);
}

function paginatePending(matched) {
    const total = matched.length;
    const totalPages = Math.max(1, Math.ceil(total / PENDING_PER_PAGE));
    if (currentPendingPage > totalPages) currentPendingPage = totalPages;
    if (currentPendingPage < 1) currentPendingPage = 1;

    matched.forEach((row, idx) => {
        const start = (currentPendingPage - 1) * PENDING_PER_PAGE;
        const end = start + PENDING_PER_PAGE;
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    const startNum = total === 0 ? 0 : (currentPendingPage - 1) * PENDING_PER_PAGE + 1;
    const endNum = Math.min(total, currentPendingPage * PENDING_PER_PAGE);

    const rangeEl = document.getElementById('pendingRange');
    const totalEl = document.getElementById('pendingTotal');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('pendingPrevBtn');
    const nextBtn = document.getElementById('pendingNextBtn');
    if (prevBtn) prevBtn.disabled = currentPendingPage <= 1;
    if (nextBtn) nextBtn.disabled = currentPendingPage >= totalPages;

    const container = document.getElementById('pendingPageNumbers');
    if (container) {
        container.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = i;
                btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
                    i === currentPendingPage ? 'bg-orange-500 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`;
                btn.onclick = () => { currentPendingPage = i; applyPropPendingFilter(); };
                container.appendChild(btn);
            }
        }
    }
}

function prevPendingPage() {
    if (currentPendingPage > 1) {
        currentPendingPage--;
        applyPropPendingFilter();
    }
}

function nextPendingPage() {
    currentPendingPage++;
    applyPropPendingFilter();
}

document.addEventListener('DOMContentLoaded', () => {
    applyPropPendingFilter();
});
</script>
@endpush
@endsection