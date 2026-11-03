@extends('layouts.app')

@section('title', 'My Properties')
@section('header', 'My Properties')

@section('content')
@php
    $totalCount = $warehouses instanceof \Illuminate\Pagination\AbstractPaginator ? $warehouses->total() : count($warehouses ?? []);
    $items = $warehouses instanceof \Illuminate\Pagination\AbstractPaginator ? $warehouses->items() : ($warehouses ?? []);
    $approvedCount = collect($items)->where('status', 'approved')->count();
    $pendingCount = collect($items)->where('status', 'pending')->count();
    $rejectedCount = collect($items)->where('status', 'rejected')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">My Properties</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-warehouse text-[9px]"></i>
                {{ $totalCount }} Properties
            </span>
        </div>
        <a href="{{ route('warehouses.create') }}" 
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs transition shadow-xs">
            <i class="fas fa-plus text-[10px]"></i>
            <span>Register Property</span>
        </a>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs px-3.5 py-2 rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Main Data Shell -->
    <div class="bg-white border border-slate-200/80 rounded-xl p-3 sm:p-3.5 shadow-2xs">
        
        <!-- Filter & Search Toolbar -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
            <!-- Status Tabs -->
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="propWhStatusTabs">
                <button type="button" 
                        onclick="setPropWhStatusFilter('all')" 
                        data-propfilter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ count($items) }}</span>
                </button>
                @if($approvedCount > 0)
                <button type="button" 
                        onclick="setPropWhStatusFilter('approved')" 
                        data-propfilter="approved"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Approved</span>
                    <span class="tab-count-badge">{{ $approvedCount }}</span>
                </button>
                @endif
                @if($pendingCount > 0)
                <button type="button" 
                        onclick="setPropWhStatusFilter('pending')" 
                        data-propfilter="pending"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Pending</span>
                    <span class="tab-count-badge">{{ $pendingCount }}</span>
                </button>
                @endif
                @if($rejectedCount > 0)
                <button type="button" 
                        onclick="setPropWhStatusFilter('rejected')" 
                        data-propfilter="rejected"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                    <span>Rejected</span>
                    <span class="tab-count-badge">{{ $rejectedCount }}</span>
                </button>
                @endif
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full lg:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="propWhSearchInput" 
                       placeholder="Search property, location, price..." 
                       oninput="applyPropWhFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearPropWhSearchBtn" 
                        onclick="clearPropWhSearch()" 
                        class="hidden kwdc-search-clear-slot" 
                        title="Clear search">
                    <i class="fas fa-circle-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Table (Zero Sideways Scroll) -->
        <div class="overflow-hidden rounded-xl border border-slate-200/80 mt-2.5">
            <table class="kwdc-table-fixed text-left">
                <thead class="bg-slate-50/95 border-b border-slate-200/80">
                    <tr>
                        <th class="w-[26%]">Property Name</th>
                        <th class="w-[24%]">Location</th>
                        <th class="w-[14%]">Area (Capacity)</th>
                        <th class="w-[14%]">Rate</th>
                        <th class="w-[12%]">Status</th>
                        <th class="w-[10%] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="propWhTableBody">
                    @forelse($items as $warehouse)
                    @php
                        $name = $warehouse->name ?? 'Property';
                        $location = $warehouse->location ?? $warehouse->address ?? 'Location not specified';
                        $status = strtolower($warehouse->status ?? 'pending');
                        $area = $warehouse->area_sqft ?? $warehouse->total_capacity ?? 0;
                        $rate = $warehouse->price_per_sqft ?? $warehouse->price_per_unit ?? 0;
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $warehouse->id,
                            $name,
                            $location,
                            $status,
                            $area,
                            $rate
                        ])));
                    @endphp
                    <tr class="prop-wh-row hover:bg-slate-50/80 transition-colors"
                        data-status="{{ $status }}"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- Property Name -->
                        <td>
                            <div class="flex items-center gap-1.5 min-w-0" title="{{ $name }}">
                                <i class="fas fa-warehouse text-slate-400 text-[10px] shrink-0"></i>
                                <span class="font-bold text-slate-900 text-xs truncate">{{ $name }}</span>
                                @if($warehouse->cold_storage)
                                    <span class="inline-flex items-center px-1 py-0.2 rounded text-[9px] font-bold bg-sky-50 text-sky-700 border border-sky-200 shrink-0" title="Cold Storage Available">
                                        <i class="fas fa-snowflake text-[8px]"></i>
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Location -->
                        <td>
                            <div class="flex items-center gap-1 text-xs text-slate-700 truncate" title="{{ $location }}">
                                <i class="fas fa-location-dot text-slate-400 text-[10px] shrink-0"></i>
                                <span class="truncate">{{ $location }}</span>
                            </div>
                        </td>

                        <!-- Area / Capacity -->
                        <td>
                            <span class="font-semibold text-slate-800 text-xs truncate block">
                                {{ number_format($area) }} sq ft
                            </span>
                        </td>

                        <!-- Rate -->
                        <td>
                            <span class="font-extrabold text-slate-900 text-xs truncate block">
                                रू {{ number_format($rate, 2) }}
                            </span>
                        </td>

                        <!-- Status Badge -->
                        <td>
                            @php
                                $badgeStyles = match($status) {
                                    'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                    'pending' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                    'rejected' => 'bg-rose-50 text-rose-700 border-rose-200/80',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeStyles }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <a href="{{ route('property.warehouses.show', $warehouse->id) }}" 
                                   class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-600 transition border border-slate-200 shadow-2xs" 
                                   title="View Property">
                                    <i class="fas fa-eye text-[9px]"></i>
                                </a>
                                <a href="{{ route('property.warehouses.edit', $warehouse->id) }}" 
                                   class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-600 transition border border-slate-200 shadow-2xs" 
                                   title="Edit Property">
                                    <i class="fas fa-pen text-[9px]"></i>
                                </a>
                                <form action="{{ route('property.warehouses.destroy', $warehouse->id) }}" 
                                      method="POST" class="inline-block"
                                      onsubmit="return confirm('Delete this property?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-rose-600 hover:text-white text-rose-500 transition border border-slate-200 shadow-2xs" 
                                            title="Delete Property">
                                        <i class="fas fa-trash text-[9px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyPropWhRow">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-warehouse text-2xl text-slate-300 block mb-1"></i>
                            No properties registered yet.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noPropWhMatchesRow" class="hidden">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching properties</span>
                            <button type="button" onclick="resetPropWhFilters()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
                                Clear filters
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Sleek Bottom Pager Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2.5 mt-1 border-t border-slate-100 text-xs text-slate-500">
            <!-- Count Readout -->
            <div class="flex items-center gap-3">
                <span id="filteredPropWhCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ count($items) }} properties
                </span>
                <button type="button" 
                        id="resetPropWhFiltersBtn" 
                        onclick="resetPropWhFilters()" 
                        class="hidden text-orange-600 hover:underline font-semibold text-xs">
                    Reset
                </button>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setPropWhPageSize(5)" data-propwhsize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setPropWhPageSize(10)" data-propwhsize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setPropWhPageSize('all')" data-propwhsize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="propWhPaginationControls">
                </div>
            </div>
        </div>

        @if($warehouses instanceof \Illuminate\Pagination\AbstractPaginator && $warehouses->hasPages())
        <div class="mt-2.5 pt-2 border-t border-slate-100 flex justify-end">
            {{ $warehouses->links() }}
        </div>
        @endif

    </div>
</div>

<script>
let currentPropWhStatusFilter = 'all';
let currentPropWhPage = 1;
let propWhPageSize = 5;
let filteredPropWhRows = [];

function setPropWhStatusFilter(status) {
    currentPropWhStatusFilter = status;
    currentPropWhPage = 1;

    document.querySelectorAll('#propWhStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.propfilter === status) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    applyPropWhFilters();
}

function clearPropWhSearch() {
    const input = document.getElementById('propWhSearchInput');
    input.value = '';
    document.getElementById('clearPropWhSearchBtn').classList.add('hidden');
    currentPropWhPage = 1;
    applyPropWhFilters();
    input.focus();
}

function resetPropWhFilters() {
    document.getElementById('propWhSearchInput').value = '';
    document.getElementById('clearPropWhSearchBtn').classList.add('hidden');
    currentPropWhStatusFilter = 'all';
    document.querySelectorAll('#propWhStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.propfilter === 'all') tab.classList.add('active');
        else tab.classList.remove('active');
    });
    currentPropWhPage = 1;
    applyPropWhFilters();
}

function setPropWhPageSize(size) {
    propWhPageSize = (size === 'all') ? 9999 : parseInt(size);
    currentPropWhPage = 1;
    
    document.querySelectorAll('[data-propwhsize]').forEach(btn => {
        if (btn.dataset.propwhsize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderPropWhPagination();
}

function goToPropWhPage(page) {
    currentPropWhPage = page;
    renderPropWhPagination();
}

function applyPropWhFilters() {
    const input = document.getElementById('propWhSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearPropWhSearchBtn');
    const resetBtn = document.getElementById('resetPropWhFiltersBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const isFiltered = (currentPropWhStatusFilter !== 'all' || query.length > 0);
    if (resetBtn) {
        if (isFiltered) resetBtn.classList.remove('hidden');
        else resetBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#propWhTableBody .prop-wh-row'));
    filteredPropWhRows = [];

    rows.forEach(row => {
        const rowStatus = row.dataset.status || '';
        const rowSearch = row.dataset.search || '';

        let statusMatches = (currentPropWhStatusFilter === 'all' || rowStatus === currentPropWhStatusFilter);
        let queryMatches = (!query || rowSearch.includes(query));

        if (statusMatches && queryMatches) {
            filteredPropWhRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderPropWhPagination();
}

function renderPropWhPagination() {
    const totalCount = filteredPropWhRows.length;
    const effectivePageSize = propWhPageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentPropWhPage > totalPages) currentPropWhPage = 1;
    if (currentPropWhPage < 1) currentPropWhPage = 1;

    const startIndex = (currentPropWhPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#propWhTableBody .prop-wh-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredPropWhRows[i]) {
            filteredPropWhRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredPropWhCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching properties';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} properties`;
        }
    }

    const noMatchesRow = document.getElementById('noPropWhMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('propWhPaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToPropWhPage(${currentPropWhPage - 1})" 
                ${currentPropWhPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentPropWhPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToPropWhPage(${p})" 
                    class="pager-btn ${p === currentPropWhPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToPropWhPage(${currentPropWhPage + 1})" 
                ${currentPropWhPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentPropWhPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    applyPropWhFilters();
});
if (document.readyState !== 'loading') {
    applyPropWhFilters();
}
</script>
@endsection