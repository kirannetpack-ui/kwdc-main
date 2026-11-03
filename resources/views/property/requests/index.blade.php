@extends('layouts.app')

@section('title', 'Warehouse Storage Requests')
@section('header', 'Warehouse Requests')

@section('content')
@php
    $totalCount = $warehouseRequests instanceof \Illuminate\Pagination\AbstractPaginator ? $warehouseRequests->total() : count($warehouseRequests ?? []);
    $items = $warehouseRequests instanceof \Illuminate\Pagination\AbstractPaginator ? $warehouseRequests->items() : ($warehouseRequests ?? []);
    $pendingCount = collect($items)->where('status', 'pending')->count();
    $approvedCount = collect($items)->where('status', 'approved')->count();
    $rejectedCount = collect($items)->where('status', 'rejected')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Storage Requests</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-clipboard-list text-[9px]"></i>
                {{ $totalCount }} Requests
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('property.approved') }}" 
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold transition border border-slate-200 shadow-2xs">
                <i class="fas fa-warehouse text-[10px]"></i>
                <span>My Properties</span>
            </a>
        </div>
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
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="propReqStatusTabs">
                <button type="button" 
                        onclick="setPropReqStatusFilter('all')" 
                        data-preqfilter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ count($items) }}</span>
                </button>
                @if($pendingCount > 0)
                <button type="button" 
                        onclick="setPropReqStatusFilter('pending')" 
                        data-preqfilter="pending"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Pending</span>
                    <span class="tab-count-badge">{{ $pendingCount }}</span>
                </button>
                @endif
                @if($approvedCount > 0)
                <button type="button" 
                        onclick="setPropReqStatusFilter('approved')" 
                        data-preqfilter="approved"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Approved</span>
                    <span class="tab-count-badge">{{ $approvedCount }}</span>
                </button>
                @endif
                @if($rejectedCount > 0)
                <button type="button" 
                        onclick="setPropReqStatusFilter('rejected')" 
                        data-preqfilter="rejected"
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
                       id="propReqSearchInput" 
                       placeholder="Search request #, client, warehouse..." 
                       oninput="applyPropReqFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearPropReqSearchBtn" 
                        onclick="clearPropReqSearch()" 
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
                        <th class="w-[10%]">Request</th>
                        <th class="w-[22%]">Client</th>
                        <th class="w-[22%]">Warehouse</th>
                        <th class="w-[18%]">Duration</th>
                        <th class="w-[12%]">Status</th>
                        <th class="w-[16%] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="propReqTableBody">
                    @forelse($items as $request)
                    @php
                        $clientName = $request->client->name ?? 'Direct Client';
                        $whName = $request->warehouse->name ?? 'Direct Warehouse';
                        $status = strtolower($request->status ?? 'pending');
                        $dateRange = ($request->start_date && $request->end_date) 
                            ? \Carbon\Carbon::parse($request->start_date)->format('M d') . ' – ' . \Carbon\Carbon::parse($request->end_date)->format('M d, Y')
                            : ($request->created_at ? $request->created_at->format('M d, Y') : '-');
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $request->id,
                            '#' . $request->id,
                            $clientName,
                            $whName,
                            $status,
                            $dateRange
                        ])));
                    @endphp
                    <tr class="prop-req-row hover:bg-slate-50/80 transition-colors"
                        data-status="{{ $status }}"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- Request ID -->
                        <td>
                            <a href="{{ route('warehouse-requests.show', $request->id) }}" 
                               class="font-bold text-slate-900 text-xs font-mono hover:text-orange-600 transition truncate block" 
                               title="#{{ $request->id }}">
                                #{{ $request->id }}
                            </a>
                        </td>

                        <!-- Client -->
                        <td>
                            <span class="font-semibold text-slate-800 text-xs truncate block" title="{{ $clientName }}">
                                {{ $clientName }}
                            </span>
                        </td>

                        <!-- Warehouse -->
                        <td>
                            <div class="flex items-center gap-1.5 text-xs text-slate-700 truncate" title="{{ $whName }}">
                                <i class="fas fa-warehouse text-slate-400 text-[10px] shrink-0"></i>
                                <span class="truncate">{{ $whName }}</span>
                            </div>
                        </td>

                        <!-- Duration -->
                        <td>
                            <span class="text-xs text-slate-600 truncate block" title="{{ $dateRange }}">
                                {{ $dateRange }}
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
                            @if($status === 'pending')
                                <div class="inline-flex items-center justify-end gap-1">
                                    <form action="{{ route('property.requests.approve', $request->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="px-2 py-0.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[10.5px] transition shadow-2xs inline-flex items-center gap-1" 
                                                title="Approve Request">
                                            <i class="fas fa-check text-[8px]"></i>
                                            <span>Approve</span>
                                        </button>
                                    </form>
                                    <form action="{{ route('property.requests.reject', $request->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="px-2 py-0.5 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-[10.5px] transition border border-rose-200/70" 
                                                title="Reject Request">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            @else
                                <a href="{{ route('warehouse-requests.show', $request->id) }}" 
                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-[10.5px] font-semibold transition border border-slate-200 shadow-2xs group"
                                   title="View Details">
                                    <span>View</span>
                                    <i class="fas fa-arrow-right text-[8px] opacity-60 group-hover:translate-x-0.5 transition-transform"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyPropReqRow">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-clipboard-list text-2xl text-slate-300 block mb-1"></i>
                            No incoming storage requests.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noPropReqMatchesRow" class="hidden">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching requests</span>
                            <button type="button" onclick="resetPropReqFilters()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
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
                <span id="filteredPropReqCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ count($items) }} requests
                </span>
                <button type="button" 
                        id="resetPropReqFiltersBtn" 
                        onclick="resetPropReqFilters()" 
                        class="hidden text-orange-600 hover:underline font-semibold text-xs">
                    Reset
                </button>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setPropReqPageSize(5)" data-prsize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setPropReqPageSize(10)" data-prsize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setPropReqPageSize('all')" data-prsize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="propReqPaginationControls">
                </div>
            </div>
        </div>

        @if($warehouseRequests instanceof \Illuminate\Pagination\AbstractPaginator && $warehouseRequests->hasPages())
        <div class="mt-2.5 pt-2 border-t border-slate-100 flex justify-end">
            {{ $warehouseRequests->links() }}
        </div>
        @endif

    </div>
</div>

<script>
let currentPropReqStatusFilter = 'all';
let currentPropReqPage = 1;
let propReqPageSize = 5;
let filteredPropReqRows = [];

function setPropReqStatusFilter(status) {
    currentPropReqStatusFilter = status;
    currentPropReqPage = 1;

    document.querySelectorAll('#propReqStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.preqfilter === status) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    applyPropReqFilters();
}

function clearPropReqSearch() {
    const input = document.getElementById('propReqSearchInput');
    input.value = '';
    document.getElementById('clearPropReqSearchBtn').classList.add('hidden');
    currentPropReqPage = 1;
    applyPropReqFilters();
    input.focus();
}

function resetPropReqFilters() {
    document.getElementById('propReqSearchInput').value = '';
    document.getElementById('clearPropReqSearchBtn').classList.add('hidden');
    currentPropReqStatusFilter = 'all';
    document.querySelectorAll('#propReqStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.preqfilter === 'all') tab.classList.add('active');
        else tab.classList.remove('active');
    });
    currentPropReqPage = 1;
    applyPropReqFilters();
}

function setPropReqPageSize(size) {
    propReqPageSize = (size === 'all') ? 9999 : parseInt(size);
    currentPropReqPage = 1;
    
    document.querySelectorAll('[data-prsize]').forEach(btn => {
        if (btn.dataset.prsize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderPropReqPagination();
}

function goToPropReqPage(page) {
    currentPropReqPage = page;
    renderPropReqPagination();
}

function applyPropReqFilters() {
    const input = document.getElementById('propReqSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearPropReqSearchBtn');
    const resetBtn = document.getElementById('resetPropReqFiltersBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const isFiltered = (currentPropReqStatusFilter !== 'all' || query.length > 0);
    if (resetBtn) {
        if (isFiltered) resetBtn.classList.remove('hidden');
        else resetBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#propReqTableBody .prop-req-row'));
    filteredPropReqRows = [];

    rows.forEach(row => {
        const rowStatus = row.dataset.status || '';
        const rowSearch = row.dataset.search || '';

        let statusMatches = (currentPropReqStatusFilter === 'all' || rowStatus === currentPropReqStatusFilter);
        let queryMatches = (!query || rowSearch.includes(query));

        if (statusMatches && queryMatches) {
            filteredPropReqRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderPropReqPagination();
}

function renderPropReqPagination() {
    const totalCount = filteredPropReqRows.length;
    const effectivePageSize = propReqPageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentPropReqPage > totalPages) currentPropReqPage = 1;
    if (currentPropReqPage < 1) currentPropReqPage = 1;

    const startIndex = (currentPropReqPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#propReqTableBody .prop-req-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredPropReqRows[i]) {
            filteredPropReqRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredPropReqCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching requests';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} requests`;
        }
    }

    const noMatchesRow = document.getElementById('noPropReqMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('propReqPaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToPropReqPage(${currentPropReqPage - 1})" 
                ${currentPropReqPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentPropReqPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToPropReqPage(${p})" 
                    class="pager-btn ${p === currentPropReqPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToPropReqPage(${currentPropReqPage + 1})" 
                ${currentPropReqPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentPropReqPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    applyPropReqFilters();
});
if (document.readyState !== 'loading') {
    applyPropReqFilters();
}
</script>
@endsection
