@extends('layouts.app')

@section('title', 'Fleet Vehicles')
@section('header', 'Fleet Vehicles')

@section('content')
@php
    $totalCount = count($vehicles ?? []);
    $truckCount = collect($vehicles ?? [])->filter(fn($v) => stripos($v->type ?? '', 'truck') !== false)->count();
    $vanCount = collect($vehicles ?? [])->filter(fn($v) => stripos($v->type ?? '', 'van') !== false)->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Fleet Vehicles</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-truck text-[9px]"></i>
                {{ $totalCount }} Registered
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
            <!-- Category Tabs -->
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="vehicleFilterTabs">
                <button type="button" 
                        onclick="setVehicleTypeFilter('all')" 
                        data-vehfilter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ $totalCount }}</span>
                </button>
                @if($truckCount > 0)
                <button type="button" 
                        onclick="setVehicleTypeFilter('truck')" 
                        data-vehfilter="truck"
                        class="kwdc-tab-pill">
                    <i class="fas fa-truck text-[9px] text-slate-500"></i>
                    <span>Trucks</span>
                    <span class="tab-count-badge">{{ $truckCount }}</span>
                </button>
                @endif
                @if($vanCount > 0)
                <button type="button" 
                        onclick="setVehicleTypeFilter('van')" 
                        data-vehfilter="van"
                        class="kwdc-tab-pill">
                    <i class="fas fa-van-shuttle text-[9px] text-slate-500"></i>
                    <span>Vans</span>
                    <span class="tab-count-badge">{{ $vanCount }}</span>
                </button>
                @endif
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full lg:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="vehicleSearchInput" 
                       placeholder="Search plate #, type, owner..." 
                       oninput="applyVehicleFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearVehSearchBtn" 
                        onclick="clearVehicleSearch()" 
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
                        <th class="w-[22%]">Plate Number</th>
                        <th class="w-[20%]">Type</th>
                        <th class="w-[28%]">Owner / Driver</th>
                        <th class="w-[16%]">Status</th>
                        <th class="w-[14%] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="vehiclesTableBody">
                    @forelse($vehicles ?? [] as $vehicle)
                    @php
                        $plate = $vehicle->plate_number ?? 'N/A';
                        $type = $vehicle->type ?? 'Commercial';
                        $ownerName = $vehicle->owner->name ?? 'Unassigned';
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $plate,
                            $type,
                            $ownerName,
                            'active'
                        ])));
                    @endphp
                    <tr class="vehicle-row hover:bg-slate-50/80 transition-colors"
                        data-type="{{ strtolower($type) }}"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- Plate Number -->
                        <td>
                            <div class="flex items-center gap-1.5 min-w-0" title="{{ $plate }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 shrink-0"></span>
                                <span class="font-bold text-slate-900 text-xs font-mono truncate">{{ $plate }}</span>
                            </div>
                        </td>

                        <!-- Type -->
                        <td>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 text-slate-700 truncate" title="{{ $type }}">
                                <i class="fas fa-truck-moving text-[9px] text-slate-400 shrink-0"></i>
                                <span class="truncate">{{ $type }}</span>
                            </span>
                        </td>

                        <!-- Owner -->
                        <td>
                            <span class="font-medium text-slate-800 text-xs truncate block" title="{{ $ownerName }}">
                                {{ $ownerName }}
                            </span>
                        </td>

                        <!-- Status Badge -->
                        <td>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border bg-emerald-50 text-emerald-700 border-emerald-200/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                <span>Active</span>
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <button type="button" 
                                        class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-600 transition border border-slate-200 shadow-2xs" 
                                        title="Edit Vehicle">
                                    <i class="fas fa-pen text-[9px]"></i>
                                </button>
                                <button type="button" 
                                        class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-rose-600 hover:text-white text-rose-500 transition border border-slate-200 shadow-2xs" 
                                        title="Remove Vehicle">
                                    <i class="fas fa-trash text-[9px]"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyVehRow">
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-truck text-2xl text-slate-300 block mb-1"></i>
                            No vehicles registered yet.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noVehMatchesRow" class="hidden">
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching vehicles found</span>
                            <button type="button" onclick="resetVehicleFilters()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
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
                <span id="filteredVehCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ $totalCount }} vehicles
                </span>
                <button type="button" 
                        id="resetVehFiltersBtn" 
                        onclick="resetVehicleFilters()" 
                        class="hidden text-orange-600 hover:underline font-semibold text-xs">
                    Reset
                </button>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setVehPageSize(5)" data-vehsize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setVehPageSize(10)" data-vehsize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setVehPageSize('all')" data-vehsize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="vehPaginationControls">
                </div>
            </div>
        </div>

    </div>
</div>

<script>
let currentVehTypeFilter = 'all';
let currentVehPage = 1;
let vehPageSize = 5;
let filteredVehRows = [];

function setVehicleTypeFilter(type) {
    currentVehTypeFilter = type;
    currentVehPage = 1;

    document.querySelectorAll('#vehicleFilterTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.vehfilter === type) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    applyVehicleFilters();
}

function clearVehicleSearch() {
    const input = document.getElementById('vehicleSearchInput');
    input.value = '';
    document.getElementById('clearVehSearchBtn').classList.add('hidden');
    currentVehPage = 1;
    applyVehicleFilters();
    input.focus();
}

function resetVehicleFilters() {
    document.getElementById('vehicleSearchInput').value = '';
    document.getElementById('clearVehSearchBtn').classList.add('hidden');
    currentVehTypeFilter = 'all';
    document.querySelectorAll('#vehicleFilterTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.vehfilter === 'all') tab.classList.add('active');
        else tab.classList.remove('active');
    });
    currentVehPage = 1;
    applyVehicleFilters();
}

function setVehPageSize(size) {
    vehPageSize = (size === 'all') ? 9999 : parseInt(size);
    currentVehPage = 1;
    
    document.querySelectorAll('[data-vehsize]').forEach(btn => {
        if (btn.dataset.vehsize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderVehPagination();
}

function goToVehPage(page) {
    currentVehPage = page;
    renderVehPagination();
}

function applyVehicleFilters() {
    const input = document.getElementById('vehicleSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearVehSearchBtn');
    const resetBtn = document.getElementById('resetVehFiltersBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const isFiltered = (currentVehTypeFilter !== 'all' || query.length > 0);
    if (resetBtn) {
        if (isFiltered) resetBtn.classList.remove('hidden');
        else resetBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#vehiclesTableBody .vehicle-row'));
    filteredVehRows = [];

    rows.forEach(row => {
        const rowType = row.dataset.type || '';
        const rowSearch = row.dataset.search || '';

        let typeMatches = (currentVehTypeFilter === 'all' || rowType.includes(currentVehTypeFilter));
        let queryMatches = (!query || rowSearch.includes(query));

        if (typeMatches && queryMatches) {
            filteredVehRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderVehPagination();
}

function renderVehPagination() {
    const totalCount = filteredVehRows.length;
    const effectivePageSize = vehPageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentVehPage > totalPages) currentVehPage = 1;
    if (currentVehPage < 1) currentVehPage = 1;

    const startIndex = (currentVehPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#vehiclesTableBody .vehicle-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredVehRows[i]) {
            filteredVehRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredVehCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching vehicles found';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} vehicles`;
        }
    }

    const noMatchesRow = document.getElementById('noVehMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('vehPaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToVehPage(${currentVehPage - 1})" 
                ${currentVehPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentVehPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToVehPage(${p})" 
                    class="pager-btn ${p === currentVehPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToVehPage(${currentVehPage + 1})" 
                ${currentVehPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentVehPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    applyVehicleFilters();
});
if (document.readyState !== 'loading') {
    applyVehicleFilters();
}
</script>
@endsection