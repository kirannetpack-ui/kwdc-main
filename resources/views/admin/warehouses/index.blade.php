@extends('layouts.app')

@section('title', 'All Warehouses')
@section('header', 'All Warehouses')

@section('content')
@php
    $totalCount = $warehouses->count();
    $approvedCount = $warehouses->where('status', 'approved')->count();
    $pendingCount = $warehouses->where('status', 'pending')->count();
    $rejectedCount = $warehouses->where('status', 'rejected')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">All Warehouses</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-warehouse text-[9px]"></i>
                {{ $totalCount }} Properties
            </span>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.warehouses.create') }}" class="px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs transition inline-flex items-center gap-1.5 shadow-2xs">
                <i class="fas fa-plus text-[10px]"></i>
                <span>Add Warehouse</span>
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
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="whStatusFilterTabs">
                <button type="button" 
                        onclick="setWarehouseStatusFilter('all')" 
                        data-filter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ $totalCount }}</span>
                </button>
                @if($approvedCount > 0)
                <button type="button" 
                        onclick="setWarehouseStatusFilter('approved')" 
                        data-filter="approved"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Approved</span>
                    <span class="tab-count-badge">{{ $approvedCount }}</span>
                </button>
                @endif
                @if($pendingCount > 0)
                <button type="button" 
                        onclick="setWarehouseStatusFilter('pending')" 
                        data-filter="pending"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Pending</span>
                    <span class="tab-count-badge">{{ $pendingCount }}</span>
                </button>
                @endif
                @if($rejectedCount > 0)
                <button type="button" 
                        onclick="setWarehouseStatusFilter('rejected')" 
                        data-filter="rejected"
                        class="kwdc-tab-pill">
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
                       id="warehouseSearchInput" 
                       placeholder="Search warehouse, city, owner..." 
                       oninput="applyWarehouseFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearWhSearchBtn" 
                        onclick="clearWarehouseSearch()" 
                        class="hidden kwdc-search-clear-slot" 
                        title="Clear search">
                    <i class="fas fa-circle-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Table (Zero-Scroll Table-Fixed Fit) -->
        <div class="overflow-hidden rounded-xl border border-slate-200/80 mt-2.5">
            <table class="w-full table-fixed text-left border-collapse">
                <thead class="bg-slate-50/95 border-b border-slate-200/80">
                    <tr>
                        <th class="w-[32%] px-2.5 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Warehouse</th>
                        <th class="w-[28%] px-2.5 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Location</th>
                        <th class="w-[20%] px-2.5 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Owner</th>
                        <th class="w-[12%] px-2.5 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                        <th class="w-[8%] px-2 py-2 text-right text-[10.5px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="warehousesTableBody">
                    @forelse($warehouses ?? [] as $warehouse)
                    @php
                        $status = strtolower($warehouse->status ?? 'pending');
                        $ownerName = $warehouse->owner->name ?? null;
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $warehouse->name,
                            $warehouse->location,
                            $ownerName,
                            $status
                        ])));
                    @endphp
                    <tr class="warehouse-row hover:bg-slate-50/80 transition-colors"
                        data-status="{{ $status }}"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- Name Column -->
                        <td class="px-2.5 py-1.5 overflow-hidden">
                            <a href="{{ route('admin.warehouses.show', $warehouse->id) }}" class="font-bold text-slate-900 hover:text-orange-600 text-xs transition inline-flex items-center gap-1.5 truncate max-w-full" title="{{ $warehouse->name }}">
                                <i class="fas fa-warehouse text-slate-400 text-xs shrink-0"></i>
                                <span class="truncate">{{ $warehouse->name }}</span>
                            </a>
                        </td>
                        
                        <!-- Location Column -->
                        <td class="px-2.5 py-1.5 text-xs text-slate-600 overflow-hidden">
                            <span class="inline-flex items-center gap-1 truncate max-w-full" title="{{ $warehouse->location ?? 'Kathmandu Valley' }}">
                                <i class="fas fa-location-dot text-slate-400 text-[10px] shrink-0"></i>
                                <span class="truncate">{{ $warehouse->location ?? 'Kathmandu Valley' }}</span>
                            </span>
                        </td>

                        <!-- Owner Column -->
                        <td class="px-2.5 py-1.5 text-xs text-slate-700 overflow-hidden">
                            @if($ownerName)
                                <span class="font-medium text-slate-800 flex items-center gap-1 truncate max-w-full" title="{{ $ownerName }}">
                                    <i class="fas fa-user-circle text-slate-400 text-xs shrink-0"></i>
                                    <span class="truncate">{{ $ownerName }}</span>
                                </span>
                            @else
                                <span class="text-slate-400 text-[11px] truncate block">Direct / In-House</span>
                            @endif
                        </td>
                        
                        <!-- Status Column -->
                        <td class="px-2.5 py-1.5 overflow-hidden">
                            @php
                                $badgeStyles = match($status) {
                                    'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                    'pending' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                    'rejected' => 'bg-rose-50 text-rose-700 border-rose-200/80',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border truncate {{ $badgeStyles }}">
                                @if($status === 'approved')
                                    <i class="fas fa-check text-[8px] shrink-0"></i>
                                @endif
                                <span class="truncate">{{ ucfirst($status) }}</span>
                            </span>
                        </td>
                        
                        <!-- Actions Column -->
                        <td class="px-2 py-1.5 whitespace-nowrap text-right text-xs overflow-hidden">
                            <div class="inline-flex items-center gap-1 justify-end">
                                <a href="{{ route('admin.warehouses.show', $warehouse->id) }}" 
                                   class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-600 transition border border-slate-200 shadow-2xs" 
                                   title="View Details">
                                    <i class="fas fa-eye text-[10px]"></i>
                                </a>
                                
                                <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" 
                                   class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-indigo-600 hover:text-white text-indigo-600 transition border border-slate-200 shadow-2xs" 
                                   title="Edit Warehouse">
                                    <i class="fas fa-pen text-[10px]"></i>
                                </a>

                                <form action="{{ route('admin.warehouses.destroy', $warehouse->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-rose-600 hover:text-white text-rose-500 transition border border-slate-200 shadow-2xs" 
                                            title="Delete Warehouse"
                                            onclick="return confirm('Are you sure you want to delete this warehouse?')">
                                        <i class="fas fa-trash text-[10px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyWhRow">
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-warehouse text-2xl text-slate-300 block mb-1"></i>
                            No warehouses found.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noWhMatchesRow" class="hidden">
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching warehouses</span>
                            <button type="button" onclick="resetWarehouseFilters()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
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
                <span id="filteredWhCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ $totalCount }} warehouses
                </span>
                <button type="button" 
                        id="resetWhFiltersBtn" 
                        onclick="resetWarehouseFilters()" 
                        class="hidden text-orange-600 hover:underline font-semibold text-xs">
                    Reset
                </button>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setWhPageSize(5)" data-whsize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setWhPageSize(10)" data-whsize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setWhPageSize('all')" data-whsize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="whPaginationControls">
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.kwdc-tab-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 11.5px;
    font-weight: 500;
    color: #475569 !important;
    background: transparent !important;
    border: none !important;
    cursor: pointer;
    transition: all 0.16s ease;
    white-space: nowrap;
    user-select: none;
}
.kwdc-tab-pill:hover {
    color: #0f172a !important;
    background: rgba(255, 255, 255, 0.7) !important;
}
.kwdc-tab-pill.active {
    color: #ffffff !important;
    background: #0f172a !important;
    font-weight: 600;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12) !important;
}
.kwdc-tab-pill.active .tab-count-badge {
    background: rgba(255, 255, 255, 0.25) !important;
    color: #ffffff !important;
}
.tab-count-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5px 5px;
    border-radius: 9999px;
    font-size: 9.5px;
    font-weight: 700;
    background: #e2e8f0;
    color: #475569;
}
.kwdc-search-icon-slot {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 11px;
    pointer-events: none;
    z-index: 2;
}
.kwdc-search-clear-slot {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 12px;
    cursor: pointer;
    background: none;
    border: none;
    padding: 0;
    z-index: 2;
}
.kwdc-clean-search-input {
    width: 100% !important;
    height: 34px !important;
    min-height: 34px !important;
    padding-left: 32px !important;
    padding-right: 28px !important;
    border-radius: 8px !important;
    border: 1px solid #cbd5e1 !important;
    background: #f8fafc !important;
    color: #0f172a !important;
    font-size: 12px !important;
}
.kwdc-clean-search-input:focus {
    background: #ffffff !important;
    border-color: #f97316 !important;
    box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.12) !important;
    outline: none !important;
}
.page-size-btn {
    padding: 2px 7px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.14s ease;
}
.page-size-btn.active {
    background: #0f172a !important;
    color: #ffffff !important;
    border-color: #0f172a !important;
}
.pager-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.14s ease;
}
.pager-btn:hover:not(.disabled) {
    background: #f1f5f9;
    color: #0f172a;
}
.pager-btn.active {
    background: #0f172a !important;
    color: #ffffff !important;
    border-color: #0f172a !important;
}
.pager-btn.disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
</style>

<script>
let currentWhFilter = 'all';
let currentWhPage = 1;
let whPageSize = 5;
let filteredWhRows = [];

function setWarehouseStatusFilter(filter) {
    currentWhFilter = filter;
    currentWhPage = 1;
    
    document.querySelectorAll('#whStatusFilterTabs .kwdc-tab-pill').forEach(btn => {
        if (btn.dataset.filter === filter) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    applyWarehouseFilters();
}

function clearWarehouseSearch() {
    const input = document.getElementById('warehouseSearchInput');
    input.value = '';
    document.getElementById('clearWhSearchBtn').classList.add('hidden');
    currentWhPage = 1;
    applyWarehouseFilters();
    input.focus();
}

function resetWarehouseFilters() {
    document.getElementById('warehouseSearchInput').value = '';
    document.getElementById('clearWhSearchBtn').classList.add('hidden');
    currentWhPage = 1;
    setWarehouseStatusFilter('all');
}

function setWhPageSize(size) {
    whPageSize = (size === 'all') ? 9999 : parseInt(size);
    currentWhPage = 1;
    
    document.querySelectorAll('[data-whsize]').forEach(btn => {
        if (btn.dataset.whsize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderWhPagination();
}

function goToWhPage(page) {
    currentWhPage = page;
    renderWhPagination();
}

function applyWarehouseFilters() {
    const input = document.getElementById('warehouseSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearWhSearchBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#warehousesTableBody .warehouse-row'));
    filteredWhRows = [];

    rows.forEach(row => {
        const rowStatus = row.dataset.status || '';
        const rowSearch = row.dataset.search || '';

        const statusMatches = (currentWhFilter === 'all' || rowStatus === currentWhFilter);
        const queryMatches = (!query || rowSearch.includes(query));

        if (statusMatches && queryMatches) {
            filteredWhRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderWhPagination();
}

function renderWhPagination() {
    const totalCount = filteredWhRows.length;
    const effectivePageSize = whPageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentWhPage > totalPages) currentWhPage = 1;
    if (currentWhPage < 1) currentWhPage = 1;

    const startIndex = (currentWhPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#warehousesTableBody .warehouse-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredWhRows[i]) {
            filteredWhRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredWhCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching warehouses';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} warehouses`;
        }
    }

    const searchInput = document.getElementById('warehouseSearchInput');
    const query = (searchInput ? searchInput.value : '').trim();
    const isFiltered = (currentWhFilter !== 'all' || query.length > 0);
    const resetBtn = document.getElementById('resetWhFiltersBtn');
    if (resetBtn) {
        if (isFiltered) resetBtn.classList.remove('hidden');
        else resetBtn.classList.add('hidden');
    }

    const noMatchesRow = document.getElementById('noWhMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('whPaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToWhPage(${currentWhPage - 1})" 
                ${currentWhPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentWhPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToWhPage(${p})" 
                    class="pager-btn ${p === currentWhPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToWhPage(${currentWhPage + 1})" 
                ${currentWhPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentWhPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    applyWarehouseFilters();
});
if (document.readyState !== 'loading') {
    applyWarehouseFilters();
}
</script>
@endsection