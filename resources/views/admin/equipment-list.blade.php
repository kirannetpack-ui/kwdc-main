@extends('layouts.app')

@section('title', 'Equipment Fleet')
@section('header', 'Equipment Fleet')

@section('content')
@php
    $totalCount = $equipment instanceof \Illuminate\Pagination\AbstractPaginator ? $equipment->total() : count($equipment ?? []);
    $items = $equipment instanceof \Illuminate\Pagination\AbstractPaginator ? $equipment->items() : ($equipment ?? []);
    $availableCount = collect($items)->where('status', 'available')->count();
    $rentedCount = collect($items)->where('status', 'rented')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Equipment Fleet</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-tools text-[9px]"></i>
                {{ $totalCount }} Machines
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
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="equipStatusTabs">
                <button type="button" 
                        onclick="setEquipStatusFilter('all')" 
                        data-eqfilter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ count($items) }}</span>
                </button>
                @if($availableCount > 0)
                <button type="button" 
                        onclick="setEquipStatusFilter('available')" 
                        data-eqfilter="available"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Available</span>
                    <span class="tab-count-badge">{{ $availableCount }}</span>
                </button>
                @endif
                @if($rentedCount > 0)
                <button type="button" 
                        onclick="setEquipStatusFilter('rented')" 
                        data-eqfilter="rented"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Rented</span>
                    <span class="tab-count-badge">{{ $rentedCount }}</span>
                </button>
                @endif
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full lg:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="equipSearchInput" 
                       placeholder="Search equipment, type, owner..." 
                       oninput="applyEquipFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearEquipSearchBtn" 
                        onclick="clearEquipSearch()" 
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
                        <th class="w-[24%]">Equipment Name</th>
                        <th class="w-[16%]">Type</th>
                        <th class="w-[22%]">Owner</th>
                        <th class="w-[14%]">Status</th>
                        <th class="w-[12%]">Jobs</th>
                        <th class="w-[12%] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="equipmentTableBody">
                    @forelse($items as $item)
                    @php
                        $name = $item->name ?? 'Equipment';
                        $type = $item->type ?? 'Industrial';
                        $ownerName = optional($item->owner)->name ?? 'Direct Owner';
                        $status = strtolower($item->status ?? 'available');
                        $activeJobs = $item->equipmentJobs ? $item->equipmentJobs->whereIn('status', ['accepted', 'in_progress'])->count() : 0;
                        $completedJobs = $item->equipmentJobs ? $item->equipmentJobs->where('status', 'completed')->count() : 0;
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $item->id,
                            $name,
                            $type,
                            $ownerName,
                            $status
                        ])));
                    @endphp
                    <tr class="equip-row hover:bg-slate-50/80 transition-colors"
                        data-status="{{ $status }}"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- Name -->
                        <td>
                            <div class="flex items-center gap-1.5 min-w-0" title="{{ $name }}">
                                <i class="fas fa-hammer text-slate-400 text-[10px] shrink-0"></i>
                                <span class="font-bold text-slate-900 text-xs truncate">{{ $name }}</span>
                            </div>
                        </td>

                        <!-- Type -->
                        <td>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700 truncate" title="{{ $type }}">
                                {{ $type }}
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
                            @php
                                $badgeStyles = match($status) {
                                    'available' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                    'rented' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                    'maintenance' => 'bg-rose-50 text-rose-700 border-rose-200/80',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeStyles }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>

                        <!-- Jobs -->
                        <td>
                            <div class="flex items-center gap-1 min-w-0">
                                @if($activeJobs > 0)
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60" title="{{ $activeJobs }} Active Jobs">
                                        {{ $activeJobs }}A
                                    </span>
                                @endif
                                @if($completedJobs > 0)
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60" title="{{ $completedJobs }} Completed Jobs">
                                        {{ $completedJobs }}D
                                    </span>
                                @endif
                                @if($activeJobs === 0 && $completedJobs === 0)
                                    <span class="text-slate-400 text-xs">-</span>
                                @endif

                                @if(\Route::has('admin.equipment.jobs'))
                                <a href="{{ route('admin.equipment.jobs', $item->id) }}" 
                                   class="text-[10px] text-orange-600 hover:underline font-semibold ml-1 shrink-0" 
                                   title="View Jobs">
                                    Jobs
                                </a>
                                @endif
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <a href="{{ route('admin.equipment.edit', $item->id) }}" 
                                   class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-600 transition border border-slate-200 shadow-2xs" 
                                   title="Edit Equipment">
                                    <i class="fas fa-pen text-[9px]"></i>
                                </a>
                                <form action="{{ route('admin.equipment.destroy', $item->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-rose-600 hover:text-white text-rose-500 transition border border-slate-200 shadow-2xs" 
                                            onclick="return confirm('Delete this equipment and its history?')"
                                            title="Delete Equipment">
                                        <i class="fas fa-trash text-[9px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyEquipRow">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-tools text-2xl text-slate-300 block mb-1"></i>
                            No equipment registered yet.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noEquipMatchesRow" class="hidden">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching equipment</span>
                            <button type="button" onclick="resetEquipFilters()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
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
                <span id="filteredEquipCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ count($items) }} machines
                </span>
                <button type="button" 
                        id="resetEquipFiltersBtn" 
                        onclick="resetEquipFilters()" 
                        class="hidden text-orange-600 hover:underline font-semibold text-xs">
                    Reset
                </button>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setEquipPageSize(5)" data-eqsize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setEquipPageSize(10)" data-eqsize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setEquipPageSize('all')" data-eqsize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="equipPaginationControls">
                </div>
            </div>
        </div>

        @if($equipment instanceof \Illuminate\Pagination\AbstractPaginator && $equipment->hasPages())
        <div class="mt-2.5 pt-2 border-t border-slate-100 flex justify-end">
            {{ $equipment->links() }}
        </div>
        @endif

    </div>
</div>

<script>
let currentEquipStatusFilter = 'all';
let currentEquipPage = 1;
let equipPageSize = 5;
let filteredEquipRows = [];

function setEquipStatusFilter(status) {
    currentEquipStatusFilter = status;
    currentEquipPage = 1;

    document.querySelectorAll('#equipStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.eqfilter === status) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    applyEquipFilters();
}

function clearEquipSearch() {
    const input = document.getElementById('equipSearchInput');
    input.value = '';
    document.getElementById('clearEquipSearchBtn').classList.add('hidden');
    currentEquipPage = 1;
    applyEquipFilters();
    input.focus();
}

function resetEquipFilters() {
    document.getElementById('equipSearchInput').value = '';
    document.getElementById('clearEquipSearchBtn').classList.add('hidden');
    currentEquipStatusFilter = 'all';
    document.querySelectorAll('#equipStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.eqfilter === 'all') tab.classList.add('active');
        else tab.classList.remove('active');
    });
    currentEquipPage = 1;
    applyEquipFilters();
}

function setEquipPageSize(size) {
    equipPageSize = (size === 'all') ? 9999 : parseInt(size);
    currentEquipPage = 1;
    
    document.querySelectorAll('[data-eqsize]').forEach(btn => {
        if (btn.dataset.eqsize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderEquipPagination();
}

function goToEquipPage(page) {
    currentEquipPage = page;
    renderEquipPagination();
}

function applyEquipFilters() {
    const input = document.getElementById('equipSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearEquipSearchBtn');
    const resetBtn = document.getElementById('resetEquipFiltersBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const isFiltered = (currentEquipStatusFilter !== 'all' || query.length > 0);
    if (resetBtn) {
        if (isFiltered) resetBtn.classList.remove('hidden');
        else resetBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#equipmentTableBody .equip-row'));
    filteredEquipRows = [];

    rows.forEach(row => {
        const rowStatus = row.dataset.status || '';
        const rowSearch = row.dataset.search || '';

        let statusMatches = (currentEquipStatusFilter === 'all' || rowStatus === currentEquipStatusFilter);
        let queryMatches = (!query || rowSearch.includes(query));

        if (statusMatches && queryMatches) {
            filteredEquipRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderEquipPagination();
}

function renderEquipPagination() {
    const totalCount = filteredEquipRows.length;
    const effectivePageSize = equipPageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentEquipPage > totalPages) currentEquipPage = 1;
    if (currentEquipPage < 1) currentEquipPage = 1;

    const startIndex = (currentEquipPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#equipmentTableBody .equip-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredEquipRows[i]) {
            filteredEquipRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredEquipCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching equipment';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} machines`;
        }
    }

    const noMatchesRow = document.getElementById('noEquipMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('equipPaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToEquipPage(${currentEquipPage - 1})" 
                ${currentEquipPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentEquipPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToEquipPage(${p})" 
                    class="pager-btn ${p === currentEquipPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToEquipPage(${currentEquipPage + 1})" 
                ${currentEquipPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentEquipPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    applyEquipFilters();
});
if (document.readyState !== 'loading') {
    applyEquipFilters();
}
</script>
@endsection