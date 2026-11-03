@extends('layouts.app')

@section('title', 'Fleet Equipment List')
@section('header', 'Fleet Machinery & Equipment')

@section('content')
@php
    $totalCount = count($equipment ?? []);
    $availableCount = collect($equipment ?? [])->where('status', 'available')->count();
    $inUseCount = collect($equipment ?? [])->where('status', 'in_use')->count();
    $maintCount = collect($equipment ?? [])->where('status', 'maintenance')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Equipment Fleet</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-tractor text-[9px]"></i>
                {{ $totalCount }} Machines
            </span>
        </div>
        <a href="{{ route('equipment.register') }}" 
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs transition shadow-xs">
            <i class="fas fa-plus text-[10px]"></i>
            <span>Register Equipment</span>
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
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="eqListStatusTabs">
                <button type="button" 
                        onclick="setEqListStatusFilter('all')" 
                        data-eqlfilter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ $totalCount }}</span>
                </button>
                @if($availableCount > 0)
                <button type="button" 
                        onclick="setEqListStatusFilter('available')" 
                        data-eqlfilter="available"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Available</span>
                    <span class="tab-count-badge">{{ $availableCount }}</span>
                </button>
                @endif
                @if($inUseCount > 0)
                <button type="button" 
                        onclick="setEqListStatusFilter('in_use')" 
                        data-eqlfilter="in_use"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 shrink-0"></span>
                    <span>In Use</span>
                    <span class="tab-count-badge">{{ $inUseCount }}</span>
                </button>
                @endif
                @if($maintCount > 0)
                <button type="button" 
                        onclick="setEqListStatusFilter('maintenance')" 
                        data-eqlfilter="maintenance"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Maintenance</span>
                    <span class="tab-count-badge">{{ $maintCount }}</span>
                </button>
                @endif
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full lg:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="eqListSearchInput" 
                       placeholder="Search equipment, type, location..." 
                       oninput="applyEqListFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearEqListSearchBtn" 
                        onclick="clearEqListSearch()" 
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
                        <th class="w-[26%]">Equipment / Model</th>
                        <th class="w-[18%]">Category</th>
                        <th class="w-[22%]">Base Location</th>
                        <th class="w-[14%]">Daily Rate</th>
                        <th class="w-[10%]">Status</th>
                        <th class="w-[10%] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="eqListTableBody">
                    @forelse($equipment ?? [] as $item)
                    @php
                        $name = $item->name ?? 'Machinery';
                        $model = $item->model ? 'Model: ' . $item->model : 'Fleet Machinery';
                        $type = $item->type ?? 'Industrial';
                        $location = $item->location ?: 'Kathmandu Valley';
                        $status = strtolower($item->status ?? 'available');
                        $rate = (float)($item->daily_rate ?? 3500);
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $item->id,
                            $name,
                            $model,
                            $type,
                            $location,
                            $status,
                            $rate
                        ])));
                    @endphp
                    <tr class="eql-row hover:bg-slate-50/80 transition-colors"
                        data-status="{{ $status }}"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- Name & Model -->
                        <td>
                            <span class="font-bold text-slate-900 text-xs truncate block" title="{{ $name }}">{{ $name }}</span>
                            <span class="text-[10.5px] text-slate-400 truncate block">{{ $model }} &bull; Year {{ $item->year ?? '2022' }}</span>
                        </td>

                        <!-- Category -->
                        <td>
                            <span class="inline-flex px-2 py-0.5 rounded text-[10.5px] font-semibold bg-slate-100 text-slate-700 truncate" title="{{ ucfirst($type) }}">
                                {{ ucfirst($type) }}
                            </span>
                        </td>

                        <!-- Base Location -->
                        <td>
                            <div class="flex items-center gap-1 text-xs text-slate-700 truncate" title="{{ $location }}">
                                <i class="fas fa-location-dot text-slate-400 text-[10px] shrink-0"></i>
                                <span class="truncate">{{ $location }}</span>
                            </div>
                        </td>

                        <!-- Daily Rate -->
                        <td>
                            <span class="font-extrabold text-slate-900 text-xs truncate block">
                                रू {{ number_format($rate, 2) }}
                            </span>
                        </td>

                        <!-- Status Badge -->
                        <td>
                            @php
                                $badgeStyles = match($status) {
                                    'available' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                    'in_use', 'rented' => 'bg-sky-50 text-sky-700 border-sky-200/80',
                                    'maintenance' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeStyles }}">
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <a href="{{ route('equipment.edit', $item->id) }}" 
                                   class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-600 transition border border-slate-200 shadow-2xs" 
                                   title="Edit Machinery">
                                    <i class="fas fa-pen text-[9px]"></i>
                                </a>
                                <form action="{{ route('equipment.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Remove this equipment from active registry?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-rose-600 hover:text-white text-rose-500 transition border border-slate-200 shadow-2xs" 
                                            title="Delete Machinery">
                                        <i class="fas fa-trash text-[9px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyEqListRow">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-tractor text-2xl text-slate-300 block mb-1"></i>
                            No equipment registered in your fleet yet.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noEqListMatchesRow" class="hidden">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching equipment</span>
                            <button type="button" onclick="resetEqListFilters()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
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
                <span id="filteredEqListCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ $totalCount }} machines
                </span>
                <button type="button" 
                        id="resetEqListFiltersBtn" 
                        onclick="resetEqListFilters()" 
                        class="hidden text-orange-600 hover:underline font-semibold text-xs">
                    Reset
                </button>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setEqListPageSize(5)" data-eqlsize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setEqListPageSize(10)" data-eqlsize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setEqListPageSize('all')" data-eqlsize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="eqListPaginationControls">
                </div>
            </div>
        </div>

    </div>
</div>

<script>
let currentEqListStatusFilter = 'all';
let currentEqListPage = 1;
let eqListPageSize = 5;
let filteredEqListRows = [];

function setEqListStatusFilter(status) {
    currentEqListStatusFilter = status;
    currentEqListPage = 1;

    document.querySelectorAll('#eqListStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.eqlfilter === status) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    applyEqListFilters();
}

function clearEqListSearch() {
    const input = document.getElementById('eqListSearchInput');
    input.value = '';
    document.getElementById('clearEqListSearchBtn').classList.add('hidden');
    currentEqListPage = 1;
    applyEqListFilters();
    input.focus();
}

function resetEqListFilters() {
    document.getElementById('eqListSearchInput').value = '';
    document.getElementById('clearEqListSearchBtn').classList.add('hidden');
    currentEqListStatusFilter = 'all';
    document.querySelectorAll('#eqListStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.eqlfilter === 'all') tab.classList.add('active');
        else tab.classList.remove('active');
    });
    currentEqListPage = 1;
    applyEqListFilters();
}

function setEqListPageSize(size) {
    eqListPageSize = (size === 'all') ? 9999 : parseInt(size);
    currentEqListPage = 1;
    
    document.querySelectorAll('[data-eqlsize]').forEach(btn => {
        if (btn.dataset.eqlsize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderEqListPagination();
}

function goToEqListPage(page) {
    currentEqListPage = page;
    renderEqListPagination();
}

function applyEqListFilters() {
    const input = document.getElementById('eqListSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearEqListSearchBtn');
    const resetBtn = document.getElementById('resetEqListFiltersBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const isFiltered = (currentEqListStatusFilter !== 'all' || query.length > 0);
    if (resetBtn) {
        if (isFiltered) resetBtn.classList.remove('hidden');
        else resetBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#eqListTableBody .eql-row'));
    filteredEqListRows = [];

    rows.forEach(row => {
        const rowStatus = row.dataset.status || '';
        const rowSearch = row.dataset.search || '';

        let statusMatches = (currentEqListStatusFilter === 'all' || rowStatus === currentEqListStatusFilter);
        let queryMatches = (!query || rowSearch.includes(query));

        if (statusMatches && queryMatches) {
            filteredEqListRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderEqListPagination();
}

function renderEqListPagination() {
    const totalCount = filteredEqListRows.length;
    const effectivePageSize = eqListPageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentEqListPage > totalPages) currentEqListPage = 1;
    if (currentEqListPage < 1) currentEqListPage = 1;

    const startIndex = (currentEqListPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#eqListTableBody .eql-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredEqListRows[i]) {
            filteredEqListRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredEqListCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching equipment';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} machines`;
        }
    }

    const noMatchesRow = document.getElementById('noEqListMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('eqListPaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToEqListPage(${currentEqListPage - 1})" 
                ${currentEqListPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentEqListPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToEqListPage(${p})" 
                    class="pager-btn ${p === currentEqListPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToEqListPage(${currentEqListPage + 1})" 
                ${currentEqListPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentEqListPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    applyEqListFilters();
});
if (document.readyState !== 'loading') {
    applyEqListFilters();
}
</script>
@endsection
