@extends('layouts.app')

@section('title', 'My Pickup Requests')
@section('header', 'My Pickup Requests')

@section('content')
@php
    $totalCount = $pickups instanceof \Illuminate\Pagination\AbstractPaginator ? $pickups->total() : count($pickups ?? []);
    $items = $pickups instanceof \Illuminate\Pagination\AbstractPaginator ? $pickups->items() : ($pickups ?? []);
    $pendingCount = collect($items)->where('status', 'pending')->count();
    $assignedCount = collect($items)->where('status', 'assigned')->count();
    $completedCount = collect($items)->filter(fn($p) => in_array($p->status, ['completed', 'delivered']))->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Pickup Requests</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-truck-pickup text-[9px]"></i>
                {{ $totalCount }} Bookings
            </span>
        </div>
        <a href="{{ route('pickup.direct-create') }}" 
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs transition shadow-xs">
            <i class="fas fa-plus text-[10px]"></i>
            <span>New Pickup</span>
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
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="clientPickupTabs">
                <button type="button" 
                        onclick="setClientPickupFilter('all')" 
                        data-cfilter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ count($items) }}</span>
                </button>
                @if($pendingCount > 0)
                <button type="button" 
                        onclick="setClientPickupFilter('pending')" 
                        data-cfilter="pending"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Pending</span>
                    <span class="tab-count-badge">{{ $pendingCount }}</span>
                </button>
                @endif
                @if($assignedCount > 0)
                <button type="button" 
                        onclick="setClientPickupFilter('assigned')" 
                        data-cfilter="assigned"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 shrink-0"></span>
                    <span>Assigned</span>
                    <span class="tab-count-badge">{{ $assignedCount }}</span>
                </button>
                @endif
                @if($completedCount > 0)
                <button type="button" 
                        onclick="setClientPickupFilter('completed')" 
                        data-cfilter="completed"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Completed</span>
                    <span class="tab-count-badge">{{ $completedCount }}</span>
                </button>
                @endif
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full lg:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="clientPickupSearchInput" 
                       placeholder="Search tracking, pickup, destination..." 
                       oninput="applyClientPickupFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearClientPickupSearchBtn" 
                        onclick="clearClientPickupSearch()" 
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
                        <th class="w-[22%]">Pickup Location</th>
                        <th class="w-[20%]">Destination</th>
                        <th class="w-[14%]">Amount</th>
                        <th class="w-[12%]">Status</th>
                        <th class="w-[10%]">Driver</th>
                        <th class="w-[8%] text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="clientPickupTableBody">
                    @forelse($items as $pickup)
                    @php
                        $firstStop = $pickup->pickupStops ? $pickup->pickupStops->first() : null;
                        $pickupAddress = $pickup->pickup_address ?? optional($firstStop)->address ?? 'Pickup location not set';
                        $destination = $pickup->destination_address
                            ?? optional($pickup->warehouse)->name
                            ?? optional($pickup->warehouse)->location
                            ?? 'Destination not set';
                        $status = strtolower($pickup->status ?? 'pending');
                        $trackingId = $pickup->tracking_id ?? ('#' . $pickup->id);
                        $driverName = $pickup->driver->name ?? 'Unassigned';
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $pickup->id,
                            $trackingId,
                            $pickupAddress,
                            $destination,
                            $driverName,
                            $status
                        ])));
                    @endphp
                    <tr class="cpickup-row hover:bg-slate-50/80 transition-colors"
                        data-status="{{ $status }}"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- Tracking -->
                        <td>
                            <span class="font-bold text-slate-900 text-xs font-mono truncate block" title="{{ $trackingId }}">{{ $trackingId }}</span>
                            <span class="text-[10px] text-slate-400 block">{{ $pickup->created_at ? $pickup->created_at->format('M d, Y') : '-' }}</span>
                        </td>

                        <!-- Pickup Location -->
                        <td>
                            <div class="truncate" title="{{ $pickupAddress }}">
                                <span class="font-medium text-slate-800 text-xs truncate block">{{ $pickupAddress }}</span>
                                @if((float)$pickup->total_distance > 0)
                                    <span class="text-[10px] text-slate-400 block">{{ number_format((float)$pickup->total_distance, 1) }} km</span>
                                @endif
                            </div>
                        </td>

                        <!-- Destination -->
                        <td>
                            <span class="text-slate-700 text-xs truncate block" title="{{ $destination }}">
                                {{ $destination }}
                            </span>
                        </td>

                        <!-- Amount -->
                        <td>
                            <span class="font-extrabold text-slate-900 text-xs truncate block">
                                रू {{ number_format((float) $pickup->total_price, 2) }}
                            </span>
                        </td>

                        <!-- Status Badge -->
                        <td>
                            @php
                                $badgeStyles = match($status) {
                                    'completed', 'delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                    'assigned' => 'bg-sky-50 text-sky-700 border-sky-200/80',
                                    'picked_up', 'in_progress', 'on_the_way' => 'bg-purple-50 text-purple-700 border-purple-200/80',
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
                            <a href="{{ route('pickup.show', $pickup->id) }}" 
                               class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-[10.5px] font-semibold transition border border-slate-200 shadow-2xs group"
                               title="View Pickup">
                                <span>View</span>
                                <i class="fas fa-arrow-right text-[8px] opacity-60 group-hover:translate-x-0.5 transition-transform"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyCPickupRow">
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-truck-pickup text-2xl text-slate-300 block mb-1"></i>
                            No pickup requests found.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noCPickupMatchesRow" class="hidden">
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching pickup requests</span>
                            <button type="button" onclick="resetClientPickupFilters()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
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
                <span id="filteredCPickupCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ count($items) }} requests
                </span>
                <button type="button" 
                        id="resetCPickupFiltersBtn" 
                        onclick="resetClientPickupFilters()" 
                        class="hidden text-orange-600 hover:underline font-semibold text-xs">
                    Reset
                </button>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setCPickupPageSize(5)" data-cpsize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setCPickupPageSize(10)" data-cpsize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setCPickupPageSize('all')" data-cpsize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="cPickupPaginationControls">
                </div>
            </div>
        </div>

        @if($pickups instanceof \Illuminate\Pagination\AbstractPaginator && $pickups->hasPages())
        <div class="mt-2.5 pt-2 border-t border-slate-100 flex justify-end">
            {{ $pickups->links() }}
        </div>
        @endif

    </div>
</div>

<script>
let currentCPickupFilter = 'all';
let currentCPickupPage = 1;
let cPickupPageSize = 5;
let filteredCPickupRows = [];

function setClientPickupFilter(status) {
    currentCPickupFilter = status;
    currentCPickupPage = 1;

    document.querySelectorAll('#clientPickupTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.cfilter === status) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    applyClientPickupFilters();
}

function clearClientPickupSearch() {
    const input = document.getElementById('clientPickupSearchInput');
    input.value = '';
    document.getElementById('clearClientPickupSearchBtn').classList.add('hidden');
    currentCPickupPage = 1;
    applyClientPickupFilters();
    input.focus();
}

function resetClientPickupFilters() {
    document.getElementById('clientPickupSearchInput').value = '';
    document.getElementById('clearClientPickupSearchBtn').classList.add('hidden');
    currentCPickupFilter = 'all';
    document.querySelectorAll('#clientPickupTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.cfilter === 'all') tab.classList.add('active');
        else tab.classList.remove('active');
    });
    currentCPickupPage = 1;
    applyClientPickupFilters();
}

function setCPickupPageSize(size) {
    cPickupPageSize = (size === 'all') ? 9999 : parseInt(size);
    currentCPickupPage = 1;
    
    document.querySelectorAll('[data-cpsize]').forEach(btn => {
        if (btn.dataset.cpsize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderCPickupPagination();
}

function goToCPickupPage(page) {
    currentCPickupPage = page;
    renderCPickupPagination();
}

function applyClientPickupFilters() {
    const input = document.getElementById('clientPickupSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearClientPickupSearchBtn');
    const resetBtn = document.getElementById('resetCPickupFiltersBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const isFiltered = (currentCPickupFilter !== 'all' || query.length > 0);
    if (resetBtn) {
        if (isFiltered) resetBtn.classList.remove('hidden');
        else resetBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#clientPickupTableBody .cpickup-row'));
    filteredCPickupRows = [];

    rows.forEach(row => {
        const rowStatus = row.dataset.status || '';
        const rowSearch = row.dataset.search || '';

        let statusMatches = false;
        if (currentCPickupFilter === 'all') {
            statusMatches = true;
        } else if (currentCPickupFilter === 'completed') {
            statusMatches = (rowStatus === 'completed' || rowStatus === 'delivered');
        } else {
            statusMatches = (rowStatus === currentCPickupFilter);
        }

        let queryMatches = (!query || rowSearch.includes(query));

        if (statusMatches && queryMatches) {
            filteredCPickupRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderCPickupPagination();
}

function renderCPickupPagination() {
    const totalCount = filteredCPickupRows.length;
    const effectivePageSize = cPickupPageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentCPickupPage > totalPages) currentCPickupPage = 1;
    if (currentCPickupPage < 1) currentCPickupPage = 1;

    const startIndex = (currentCPickupPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#clientPickupTableBody .cpickup-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredCPickupRows[i]) {
            filteredCPickupRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredCPickupCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching pickup requests';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} requests`;
        }
    }

    const noMatchesRow = document.getElementById('noCPickupMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('cPickupPaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToCPickupPage(${currentCPickupPage - 1})" 
                ${currentCPickupPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentCPickupPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToCPickupPage(${p})" 
                    class="pager-btn ${p === currentCPickupPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToCPickupPage(${currentCPickupPage + 1})" 
                ${currentCPickupPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentCPickupPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    applyClientPickupFilters();
});
if (document.readyState !== 'loading') {
    applyClientPickupFilters();
}
</script>
@endsection
