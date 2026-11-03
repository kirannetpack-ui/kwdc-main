@extends('layouts.app')

@section('title', 'Pickup Requests')
@section('header', 'Pickup Requests')

@section('content')
@php
    $totalCount = count($pickups ?? []);
    $pendingCount = collect($pickups ?? [])->where('status', 'pending')->count();
    $assignedCount = collect($pickups ?? [])->where('status', 'assigned')->count();
    $completedCount = collect($pickups ?? [])->filter(fn($p) => in_array($p->status, ['completed', 'delivered']))->count();
    $otherCount = $totalCount - $pendingCount - $assignedCount - $completedCount;
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Pickup Requests</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-truck-pickup text-[9px]"></i>
                {{ $totalCount }} Requests
            </span>
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
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="pickupStatusTabs">
                <button type="button" 
                        onclick="setPickupStatusFilter('all')" 
                        data-pickupfilter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ $totalCount }}</span>
                </button>
                @if($pendingCount > 0)
                <button type="button" 
                        onclick="setPickupStatusFilter('pending')" 
                        data-pickupfilter="pending"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Pending</span>
                    <span class="tab-count-badge">{{ $pendingCount }}</span>
                </button>
                @endif
                @if($assignedCount > 0)
                <button type="button" 
                        onclick="setPickupStatusFilter('assigned')" 
                        data-pickupfilter="assigned"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 shrink-0"></span>
                    <span>Assigned</span>
                    <span class="tab-count-badge">{{ $assignedCount }}</span>
                </button>
                @endif
                @if($completedCount > 0)
                <button type="button" 
                        onclick="setPickupStatusFilter('completed')" 
                        data-pickupfilter="completed"
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
                       id="pickupSearchInput" 
                       placeholder="Search request #, client, address..." 
                       oninput="applyPickupFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearPickupSearchBtn" 
                        onclick="clearPickupSearch()" 
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
                        <th class="w-[12%]">ID</th>
                        <th class="w-[24%]">Client</th>
                        <th class="w-[32%]">Pickup Address</th>
                        <th class="w-[18%]">Status</th>
                        <th class="w-[14%] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="pickupsTableBody">
                    @forelse($pickups ?? [] as $pickup)
                    @php
                        $status = strtolower($pickup->status ?? 'pending');
                        $clientName = $pickup->client->name ?? 'N/A';
                        $address = $pickup->pickup_address ?? 'Pickup location not specified';
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $pickup->id,
                            '#' . $pickup->id,
                            $clientName,
                            $address,
                            $status
                        ])));
                    @endphp
                    <tr class="pickup-row hover:bg-slate-50/80 transition-colors"
                        data-status="{{ $status }}"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- ID -->
                        <td>
                            <span class="font-bold text-slate-900 text-xs truncate block" title="#{{ $pickup->id }}">#{{ $pickup->id }}</span>
                        </td>

                        <!-- Client -->
                        <td>
                            <span class="font-semibold text-slate-800 text-xs truncate block" title="{{ $clientName }}">{{ $clientName }}</span>
                        </td>

                        <!-- Address -->
                        <td>
                            <div class="flex items-center gap-1.5 text-xs text-slate-700 truncate" title="{{ $address }}">
                                <i class="fas fa-location-dot text-slate-400 text-[10px] shrink-0"></i>
                                <span class="truncate">{{ $address }}</span>
                            </div>
                        </td>

                        <!-- Status Badge -->
                        <td>
                            @php
                                $badgeStyles = match($status) {
                                    'completed', 'delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                    'assigned' => 'bg-sky-50 text-sky-700 border-sky-200/80',
                                    'picked_up', 'in_transit' => 'bg-purple-50 text-purple-700 border-purple-200/80',
                                    'pending' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                    'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200/80',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeStyles }}">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="text-right">
                            <a class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-[10.5px] font-semibold transition border border-slate-200 shadow-2xs group" 
                               href="{{ route('pickup.show', $pickup->id) }}" 
                               aria-label="View pickup #{{ $pickup->id }}"
                               title="View Pickup">
                                <span>View</span>
                                <i class="fas fa-arrow-right text-[8px] opacity-60 group-hover:translate-x-0.5 transition-transform"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyPickupRow">
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-truck-pickup text-2xl text-slate-300 block mb-1"></i>
                            No pickup requests found.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noPickupMatchesRow" class="hidden">
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching pickup requests</span>
                            <button type="button" onclick="resetPickupFilters()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
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
                <span id="filteredPickupCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ $totalCount }} requests
                </span>
                <button type="button" 
                        id="resetPickupFiltersBtn" 
                        onclick="resetPickupFilters()" 
                        class="hidden text-orange-600 hover:underline font-semibold text-xs">
                    Reset
                </button>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setPickupPageSize(5)" data-pickupsize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setPickupPageSize(10)" data-pickupsize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setPickupPageSize('all')" data-pickupsize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="pickupPaginationControls">
                </div>
            </div>
        </div>

    </div>
</div>

<script>
let currentPickupStatusFilter = 'all';
let currentPickupPage = 1;
let pickupPageSize = 5;
let filteredPickupRows = [];

function setPickupStatusFilter(status) {
    currentPickupStatusFilter = status;
    currentPickupPage = 1;

    document.querySelectorAll('#pickupStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.pickupfilter === status) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    applyPickupFilters();
}

function clearPickupSearch() {
    const input = document.getElementById('pickupSearchInput');
    input.value = '';
    document.getElementById('clearPickupSearchBtn').classList.add('hidden');
    currentPickupPage = 1;
    applyPickupFilters();
    input.focus();
}

function resetPickupFilters() {
    document.getElementById('pickupSearchInput').value = '';
    document.getElementById('clearPickupSearchBtn').classList.add('hidden');
    currentPickupStatusFilter = 'all';
    document.querySelectorAll('#pickupStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.pickupfilter === 'all') tab.classList.add('active');
        else tab.classList.remove('active');
    });
    currentPickupPage = 1;
    applyPickupFilters();
}

function setPickupPageSize(size) {
    pickupPageSize = (size === 'all') ? 9999 : parseInt(size);
    currentPickupPage = 1;
    
    document.querySelectorAll('[data-pickupsize]').forEach(btn => {
        if (btn.dataset.pickupsize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderPickupPagination();
}

function goToPickupPage(page) {
    currentPickupPage = page;
    renderPickupPagination();
}

function applyPickupFilters() {
    const input = document.getElementById('pickupSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearPickupSearchBtn');
    const resetBtn = document.getElementById('resetPickupFiltersBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const isFiltered = (currentPickupStatusFilter !== 'all' || query.length > 0);
    if (resetBtn) {
        if (isFiltered) resetBtn.classList.remove('hidden');
        else resetBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#pickupsTableBody .pickup-row'));
    filteredPickupRows = [];

    rows.forEach(row => {
        const rowStatus = row.dataset.status || '';
        const rowSearch = row.dataset.search || '';

        let statusMatches = false;
        if (currentPickupStatusFilter === 'all') {
            statusMatches = true;
        } else if (currentPickupStatusFilter === 'completed') {
            statusMatches = (rowStatus === 'completed' || rowStatus === 'delivered');
        } else {
            statusMatches = (rowStatus === currentPickupStatusFilter);
        }

        const queryMatches = (!query || rowSearch.includes(query));

        if (statusMatches && queryMatches) {
            filteredPickupRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderPickupPagination();
}

function renderPickupPagination() {
    const totalCount = filteredPickupRows.length;
    const effectivePageSize = pickupPageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentPickupPage > totalPages) currentPickupPage = 1;
    if (currentPickupPage < 1) currentPickupPage = 1;

    const startIndex = (currentPickupPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#pickupsTableBody .pickup-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredPickupRows[i]) {
            filteredPickupRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredPickupCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching pickup requests';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} requests`;
        }
    }

    const noMatchesRow = document.getElementById('noPickupMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('pickupPaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToPickupPage(${currentPickupPage - 1})" 
                ${currentPickupPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentPickupPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToPickupPage(${p})" 
                    class="pager-btn ${p === currentPickupPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToPickupPage(${currentPickupPage + 1})" 
                ${currentPickupPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentPickupPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    applyPickupFilters();
});
if (document.readyState !== 'loading') {
    applyPickupFilters();
}
</script>
@endsection
