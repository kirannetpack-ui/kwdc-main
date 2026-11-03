@extends('layouts.app')

@section('title', 'Approved Properties')
@section('header', 'Approved Warehouse Facilities')

@section('content')
@php
    $totalCount = $approvedWarehouses instanceof \Illuminate\Pagination\AbstractPaginator ? $approvedWarehouses->total() : count($approvedWarehouses ?? []);
    $items = $approvedWarehouses instanceof \Illuminate\Pagination\AbstractPaginator ? $approvedWarehouses->items() : ($approvedWarehouses ?? []);
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Approved Facilities</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                <i class="fas fa-check-circle text-[9px]"></i>
                {{ $totalCount }} Certified
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('property.pending') }}" 
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold transition border border-slate-200 shadow-2xs">
                <i class="fas fa-clock text-amber-500 text-[10px]"></i>
                <span>Pending</span>
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
                Certified active warehouse facilities ready for enterprise leasing
            </div>
            
            <div class="relative w-full sm:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="propApprovedSearchInput" 
                       placeholder="Search facility name, location..." 
                       oninput="applyPropApprovedFilter()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="propApprovedSearchClear" 
                        onclick="clearPropApprovedSearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Table with Zero Sideways Scroll -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="propApprovedTable">
                <thead>
                    <tr>
                        <th class="w-[220px]">Facility Name</th>
                        <th class="w-[170px]">Location</th>
                        <th class="w-[110px]">Usable Area</th>
                        <th class="w-[120px]">Monthly Rate</th>
                        <th class="w-[110px]">Approved Date</th>
                        <th class="w-[110px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="propApprovedTableBody">
                    @forelse($items as $warehouse)
                    <tr class="hover:bg-slate-50/70 transition prop-approved-row"
                        data-search="{{ strtolower($warehouse->name . ' ' . ($warehouse->address ?: $warehouse->location) . ' ' . (optional($warehouse->user)->name ?? '')) }}">
                        
                        <td>
                            <strong class="text-slate-900 font-semibold block truncate" title="{{ $warehouse->name }}">{{ $warehouse->name }}</strong>
                            <span class="text-[10px] text-slate-500 truncate block">
                                Owner: {{ optional($warehouse->user)->name ?? optional($warehouse->owner)->name ?? 'Verified Partner' }}
                                @if($warehouse->cold_storage) &bull; <span class="text-blue-600 font-semibold">Cold Storage</span> @endif
                            </span>
                        </td>

                        <td>
                            <div class="truncate text-slate-600" title="{{ $warehouse->address ?: $warehouse->location }}">
                                <i class="fas fa-map-marker-alt text-orange-400 text-[10px] me-1"></i>
                                {{ \Illuminate\Support\Str::limit($warehouse->address ?: $warehouse->location, 28) }}
                            </div>
                        </td>

                        <td class="font-semibold text-slate-800">
                            {{ number_format((float) $warehouse->area_sqft) }} <span class="text-[10px] font-normal text-slate-500">sq ft</span>
                        </td>

                        <td class="font-semibold text-orange-600">
                            NPR {{ number_format((float) $warehouse->price_per_sqft, 2) }} <span class="text-[10px] font-normal text-slate-500">/ sq ft</span>
                        </td>

                        <td class="text-slate-500 text-[11px]">
                            {{ $warehouse->approved_at ? $warehouse->approved_at->format('M d, Y') : 'Certified' }}
                        </td>

                        <td class="text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <a href="{{ route('property.warehouses.show', $warehouse->id) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 transition"
                                   title="View Details">
                                    <i class="fas fa-eye text-[11px]"></i>
                                </a>
                                <a href="{{ route('warehouses.pdf', $warehouse->id) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-orange-50 hover:bg-orange-600 hover:text-white text-orange-600 transition"
                                   title="Official Certificate PDF">
                                    <i class="fas fa-file-pdf text-[11px]"></i>
                                </a>
                                <a href="{{ route('property.warehouses.edit', $warehouse->id) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 transition"
                                   title="Edit Facility">
                                    <i class="fas fa-edit text-[11px]"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-warehouse text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold text-slate-700">No Approved Facilities Yet</span>
                                <span class="text-[11px] text-slate-400 mt-0.5">Register your commercial warehouse space to receive verification and client rental requests.</span>
                                <a href="{{ route('warehouses.create') }}" class="mt-2 text-xs text-orange-600 font-bold hover:underline">
                                    Register Facility &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyApprovedSearch" class="hidden">
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No approved facilities match your search</span>
                                <button type="button" onclick="clearPropApprovedSearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
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
                Showing <span id="approvedRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="approvedTotal" class="font-bold text-slate-800">{{ count($items) }}</span> facilities
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="approvedPagination">
                <button type="button" id="approvedPrevBtn" onclick="prevApprovedPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="approvedPageNumbers" class="flex items-center gap-1"></div>
                <button type="button" id="approvedNextBtn" onclick="nextApprovedPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentApprovedPage = 1;
const APPROVED_PER_PAGE = 5;

function clearPropApprovedSearch() {
    const input = document.getElementById('propApprovedSearchInput');
    if (input) input.value = '';
    applyPropApprovedFilter();
}

function applyPropApprovedFilter() {
    const searchVal = (document.getElementById('propApprovedSearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('propApprovedSearchClear');
    if (clearBtn) clearBtn.classList.toggle('visible', searchVal.length > 0);

    const rows = Array.from(document.querySelectorAll('.prop-approved-row'));
    const matched = [];

    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        if (!searchVal || text.includes(searchVal)) {
            matched.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const empty = document.getElementById('emptyApprovedSearch');
    if (empty) empty.classList.toggle('hidden', matched.length > 0 || rows.length === 0);

    paginateApproved(matched);
}

function paginateApproved(matched) {
    const total = matched.length;
    const totalPages = Math.max(1, Math.ceil(total / APPROVED_PER_PAGE));
    if (currentApprovedPage > totalPages) currentApprovedPage = totalPages;
    if (currentApprovedPage < 1) currentApprovedPage = 1;

    matched.forEach((row, idx) => {
        const start = (currentApprovedPage - 1) * APPROVED_PER_PAGE;
        const end = start + APPROVED_PER_PAGE;
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    const startNum = total === 0 ? 0 : (currentApprovedPage - 1) * APPROVED_PER_PAGE + 1;
    const endNum = Math.min(total, currentApprovedPage * APPROVED_PER_PAGE);

    const rangeEl = document.getElementById('approvedRange');
    const totalEl = document.getElementById('approvedTotal');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('approvedPrevBtn');
    const nextBtn = document.getElementById('approvedNextBtn');
    if (prevBtn) prevBtn.disabled = currentApprovedPage <= 1;
    if (nextBtn) nextBtn.disabled = currentApprovedPage >= totalPages;

    const container = document.getElementById('approvedPageNumbers');
    if (container) {
        container.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = i;
                btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
                    i === currentApprovedPage ? 'bg-orange-500 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`;
                btn.onclick = () => { currentApprovedPage = i; applyPropApprovedFilter(); };
                container.appendChild(btn);
            }
        }
    }
}

function prevApprovedPage() {
    if (currentApprovedPage > 1) {
        currentApprovedPage--;
        applyPropApprovedFilter();
    }
}

function nextApprovedPage() {
    currentApprovedPage++;
    applyPropApprovedFilter();
}

document.addEventListener('DOMContentLoaded', () => {
    applyPropApprovedFilter();
});
</script>
@endpush
@endsection
