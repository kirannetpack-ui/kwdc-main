@extends('layouts.app')

@section('title', 'My Dispatch Orders')
@section('header', 'My Dispatch Orders')

@section('content')
@php
    $totalCount = $dispatches instanceof \Illuminate\Pagination\AbstractPaginator ? $dispatches->total() : count($dispatches ?? []);
    $items = $dispatches instanceof \Illuminate\Pagination\AbstractPaginator ? $dispatches->items() : ($dispatches ?? []);
    $pendingCount = collect($items)->where('status', 'pending')->count();
    $assignedCount = collect($items)->where('status', 'assigned')->count();
    $transitCount = collect($items)->filter(fn($d) => in_array($d->status, ['picked_up', 'on_the_way', 'in_transit']))->count();
    $deliveredCount = collect($items)->filter(fn($d) => in_array($d->status, ['delivered', 'completed']))->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Dispatch Orders</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-truck-ramp-box text-[9px]"></i>
                {{ $totalCount }} Dispatches
            </span>
        </div>
        <a href="{{ route('dispatch.direct-create') }}" 
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs transition shadow-xs">
            <i class="fas fa-plus text-[10px]"></i>
            <span>New Dispatch</span>
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

    @if(session('error'))
    <div class="bg-rose-50 border border-rose-200 text-rose-800 text-xs px-3.5 py-2 rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-triangle-exclamation text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- Main Data Shell -->
    <div class="bg-white border border-slate-200/80 rounded-xl p-3 sm:p-3.5 shadow-2xs">
        
        <!-- Filter & Search Toolbar -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
            <!-- Status Tabs -->
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="clientDispTabs">
                <button type="button" 
                        onclick="setClientDispFilter('all')" 
                        data-dfilter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ count($items) }}</span>
                </button>
                @if($pendingCount > 0)
                <button type="button" 
                        onclick="setClientDispFilter('pending')" 
                        data-dfilter="pending"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Pending</span>
                    <span class="tab-count-badge">{{ $pendingCount }}</span>
                </button>
                @endif
                @if($assignedCount > 0)
                <button type="button" 
                        onclick="setClientDispFilter('assigned')" 
                        data-dfilter="assigned"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 shrink-0"></span>
                    <span>Assigned</span>
                    <span class="tab-count-badge">{{ $assignedCount }}</span>
                </button>
                @endif
                @if($transitCount > 0)
                <button type="button" 
                        onclick="setClientDispFilter('transit')" 
                        data-dfilter="transit"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                    <span>In Transit</span>
                    <span class="tab-count-badge">{{ $transitCount }}</span>
                </button>
                @endif
                @if($deliveredCount > 0)
                <button type="button" 
                        onclick="setClientDispFilter('delivered')" 
                        data-dfilter="delivered"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Delivered</span>
                    <span class="tab-count-badge">{{ $deliveredCount }}</span>
                </button>
                @endif
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full lg:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="clientDispSearchInput" 
                       placeholder="Search tracking, route, driver..." 
                       oninput="applyClientDispFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearClientDispSearchBtn" 
                        onclick="clearClientDispSearch()" 
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
                        <th class="w-[14%]">Tracking</th>
                        <th class="w-[28%]">Route Corridor</th>
                        <th class="w-[10%]">Distance</th>
                        <th class="w-[14%]">Amount</th>
                        <th class="w-[14%]">Status</th>
                        <th class="w-[12%]">Driver</th>
                        <th class="w-[8%] text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="clientDispTableBody">
                    @forelse($items as $dispatch)
                    @php
                        $firstStop = $dispatch->deliveryStops ? $dispatch->deliveryStops->first() : null;
                        $pickupAddr = $dispatch->pickup_address ?? 'Pickup not set';
                        $deliveryAddr = $dispatch->delivery_address ?? optional($firstStop)->address ?? 'Destination not set';
                        $status = strtolower($dispatch->status ?? 'pending');
                        $trackingId = $dispatch->tracking_id ?? ('#' . $dispatch->id);
                        $driverName = $dispatch->driver->name ?? 'Unassigned';
                        $amount = (float)($dispatch->base_price ?? $dispatch->total_price ?? 0);
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $dispatch->id,
                            $trackingId,
                            $pickupAddr,
                            $deliveryAddr,
                            $driverName,
                            $status
                        ])));
                    @endphp
                    <tr class="cdisp-row hover:bg-slate-50/80 transition-colors"
                        data-status="{{ $status }}"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- Tracking -->
                        <td>
                            <span class="font-bold text-slate-900 text-xs font-mono truncate block" title="{{ $trackingId }}">{{ $trackingId }}</span>
                            <span class="text-[10px] text-slate-400 block">{{ $dispatch->created_at ? $dispatch->created_at->format('M d, Y') : '-' }}</span>
                        </td>

                        <!-- Route Corridor -->
                        <td>
                            <div class="flex items-center gap-1 text-xs text-slate-700 min-w-0" title="{{ $pickupAddr }} → {{ $deliveryAddr }}">
                                <span class="truncate font-medium">{{ $pickupAddr }}</span>
                                <i class="fas fa-arrow-right text-[7px] text-slate-300 shrink-0"></i>
                                <span class="truncate text-slate-500">{{ $deliveryAddr }}</span>
                            </div>
                        </td>

                        <!-- Distance -->
                        <td>
                            <span class="text-xs text-slate-600 truncate block">
                                {{ number_format((float) $dispatch->total_distance, 1) }} km
                            </span>
                        </td>

                        <!-- Amount -->
                        <td>
                            <span class="font-extrabold text-slate-900 text-xs truncate block">
                                रू {{ number_format($amount, 2) }}
                            </span>
                        </td>

                        <!-- Status Badge -->
                        <td>
                            @php
                                $badgeStyles = match($status) {
                                    'delivered', 'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                    'assigned' => 'bg-sky-50 text-sky-700 border-sky-200/80',
                                    'picked_up', 'in_transit', 'on_the_way' => 'bg-indigo-50 text-indigo-700 border-indigo-200/80',
                                    'pending' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                    'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200/80',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeStyles }}">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </td>

                        <!-- Driver -->
                        <td>
                            <span class="text-slate-600 text-xs truncate block" title="{{ $driverName }}">
                                {{ $driverName }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="text-right">
                            <a href="{{ route('dispatch.show', $dispatch->id) }}" 
                               class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-[10.5px] font-semibold transition border border-slate-200 shadow-2xs group"
                               title="View Order">
                                <span>View</span>
                                <i class="fas fa-arrow-right text-[8px] opacity-60 group-hover:translate-x-0.5 transition-transform"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyCDispRow">
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-truck-ramp-box text-2xl text-slate-300 block mb-1"></i>
                            No dispatch orders found.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noCDispMatchesRow" class="hidden">
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching dispatch orders</span>
                            <button type="button" onclick="resetClientDispFilters()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
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
                <span id="filteredCDispCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ count($items) }} orders
                </span>
                <button type="button" 
                        id="resetCDispFiltersBtn" 
                        onclick="resetClientDispFilters()" 
                        class="hidden text-orange-600 hover:underline font-semibold text-xs">
                    Reset
                </button>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setCDispPageSize(5)" data-cdsize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setCDispPageSize(10)" data-cdsize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setCDispPageSize('all')" data-cdsize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="cDispPaginationControls">
                </div>
            </div>
        </div>

        @if($dispatches instanceof \Illuminate\Pagination\AbstractPaginator && $dispatches->hasPages())
        <div class="mt-2.5 pt-2 border-t border-slate-100 flex justify-end">
            {{ $dispatches->links() }}
        </div>
        @endif

    </div>
</div>

<script>
let currentCDispFilter = 'all';
let currentCDispPage = 1;
let cDispPageSize = 5;
let filteredCDispRows = [];

function setClientDispFilter(status) {
    currentCDispFilter = status;
    currentCDispPage = 1;

    document.querySelectorAll('#clientDispTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.dfilter === status) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    applyClientDispFilters();
}

function clearClientDispSearch() {
    const input = document.getElementById('clientDispSearchInput');
    input.value = '';
    document.getElementById('clearClientDispSearchBtn').classList.add('hidden');
    currentCDispPage = 1;
    applyClientDispFilters();
    input.focus();
}

function resetClientDispFilters() {
    document.getElementById('clientDispSearchInput').value = '';
    document.getElementById('clearClientDispSearchBtn').classList.add('hidden');
    currentCDispFilter = 'all';
    document.querySelectorAll('#clientDispTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.dfilter === 'all') tab.classList.add('active');
        else tab.classList.remove('active');
    });
    currentCDispPage = 1;
    applyClientDispFilters();
}

function setCDispPageSize(size) {
    cDispPageSize = (size === 'all') ? 9999 : parseInt(size);
    currentCDispPage = 1;
    
    document.querySelectorAll('[data-cdsize]').forEach(btn => {
        if (btn.dataset.cdsize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderCDispPagination();
}

function goToCDispPage(page) {
    currentCDispPage = page;
    renderCDispPagination();
}

function applyClientDispFilters() {
    const input = document.getElementById('clientDispSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearClientDispSearchBtn');
    const resetBtn = document.getElementById('resetCDispFiltersBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const isFiltered = (currentCDispFilter !== 'all' || query.length > 0);
    if (resetBtn) {
        if (isFiltered) resetBtn.classList.remove('hidden');
        else resetBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#clientDispTableBody .cdisp-row'));
    filteredCDispRows = [];

    rows.forEach(row => {
        const rowStatus = row.dataset.status || '';
        const rowSearch = row.dataset.search || '';

        let statusMatches = false;
        if (currentCDispFilter === 'all') {
            statusMatches = true;
        } else if (currentCDispFilter === 'transit') {
            statusMatches = (rowStatus === 'picked_up' || rowStatus === 'on_the_way' || rowStatus === 'in_transit');
        } else if (currentCDispFilter === 'delivered') {
            statusMatches = (rowStatus === 'delivered' || rowStatus === 'completed');
        } else {
            statusMatches = (rowStatus === currentCDispFilter);
        }

        let queryMatches = (!query || rowSearch.includes(query));

        if (statusMatches && queryMatches) {
            filteredCDispRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderCDispPagination();
}

function renderCDispPagination() {
    const totalCount = filteredCDispRows.length;
    const effectivePageSize = cDispPageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentCDispPage > totalPages) currentCDispPage = 1;
    if (currentCDispPage < 1) currentCDispPage = 1;

    const startIndex = (currentCDispPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#clientDispTableBody .cdisp-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredCDispRows[i]) {
            filteredCDispRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredCDispCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching dispatch orders';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} orders`;
        }
    }

    const noMatchesRow = document.getElementById('noCDispMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('cDispPaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToCDispPage(${currentCDispPage - 1})" 
                ${currentCDispPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentCDispPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToCDispPage(${p})" 
                    class="pager-btn ${p === currentCDispPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToCDispPage(${currentCDispPage + 1})" 
                ${currentCDispPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentCDispPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    applyClientDispFilters();
});
if (document.readyState !== 'loading') {
    applyClientDispFilters();
}
</script>
@endsection
