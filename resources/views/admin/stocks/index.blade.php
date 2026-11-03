@extends('layouts.app')

@section('title', 'Stock Inventory')
@section('header', 'Stock Inventory')

@section('content')
@php
    $totalCount = count($stocks ?? []);
    $inStockCount = collect($stocks ?? [])->filter(fn($s) => in_array(strtolower($s->status ?? ''), ['active', 'stored', 'in_stock']))->count();
    $pendingCount = collect($stocks ?? [])->filter(fn($s) => strtolower($s->status ?? '') === 'pending')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Stock Inventory</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-boxes-stacked text-[9px]"></i>
                {{ $totalCount }} Items
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
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="stockStatusTabs">
                <button type="button" 
                        onclick="setStockStatusFilter('all')" 
                        data-stockfilter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ $totalCount }}</span>
                </button>
                @if($inStockCount > 0)
                <button type="button" 
                        onclick="setStockStatusFilter('active')" 
                        data-stockfilter="active"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>In Stock</span>
                    <span class="tab-count-badge">{{ $inStockCount }}</span>
                </button>
                @endif
                @if($pendingCount > 0)
                <button type="button" 
                        onclick="setStockStatusFilter('pending')" 
                        data-stockfilter="pending"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Pending</span>
                    <span class="tab-count-badge">{{ $pendingCount }}</span>
                </button>
                @endif
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full lg:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="stockSearchInput" 
                       placeholder="Search product, warehouse..." 
                       oninput="applyStockFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearStockSearchBtn" 
                        onclick="clearStockSearch()" 
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
                        <th class="w-[28%]">Product</th>
                        <th class="w-[18%]">Quantity</th>
                        <th class="w-[28%]">Warehouse</th>
                        <th class="w-[14%]">Status</th>
                        <th class="w-[12%] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="stocksTableBody">
                    @forelse($stocks ?? [] as $stock)
                    @php
                        $prod = $stock->product_name ?? 'Item';
                        $whName = $stock->warehouse->name ?? 'Unassigned';
                        $status = strtolower($stock->status ?? 'pending');
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $prod,
                            $stock->quantity,
                            $whName,
                            $status
                        ])));
                    @endphp
                    <tr class="stock-row hover:bg-slate-50/80 transition-colors"
                        data-status="{{ $status }}"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- Product -->
                        <td>
                            <div class="flex items-center gap-1.5 min-w-0" title="{{ $prod }}">
                                <i class="fas fa-box text-slate-400 text-[10px] shrink-0"></i>
                                <span class="font-bold text-slate-900 text-xs truncate">{{ $prod }}</span>
                            </div>
                        </td>

                        <!-- Quantity -->
                        <td>
                            <span class="font-semibold text-slate-800 text-xs truncate block">
                                {{ number_format($stock->quantity ?? 0) }} Units
                            </span>
                        </td>

                        <!-- Warehouse -->
                        <td>
                            <div class="flex items-center gap-1.5 text-xs text-slate-700 truncate" title="{{ $whName }}">
                                <i class="fas fa-warehouse text-slate-400 text-[10px] shrink-0"></i>
                                <span class="truncate">{{ $whName }}</span>
                            </div>
                        </td>

                        <!-- Status Badge -->
                        <td>
                            @php
                                $badgeStyles = match($status) {
                                    'active', 'stored', 'in_stock' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                    'pending' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeStyles }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="text-right">
                            <button type="button" 
                                    class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-emerald-600 hover:text-white text-emerald-600 transition border border-slate-200 shadow-2xs" 
                                    title="Verify Stock">
                                <i class="fas fa-check text-[9px]"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyStockRow">
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-boxes-stacked text-2xl text-slate-300 block mb-1"></i>
                            No stock inventory items found.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noStockMatchesRow" class="hidden">
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching stock items</span>
                            <button type="button" onclick="resetStockFilters()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
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
                <span id="filteredStockCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ $totalCount }} items
                </span>
                <button type="button" 
                        id="resetStockFiltersBtn" 
                        onclick="resetStockFilters()" 
                        class="hidden text-orange-600 hover:underline font-semibold text-xs">
                    Reset
                </button>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setStockPageSize(5)" data-stocksize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setStockPageSize(10)" data-stocksize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setStockPageSize('all')" data-stocksize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="stockPaginationControls">
                </div>
            </div>
        </div>

    </div>
</div>

<script>
let currentStockStatusFilter = 'all';
let currentStockPage = 1;
let stockPageSize = 5;
let filteredStockRows = [];

function setStockStatusFilter(status) {
    currentStockStatusFilter = status;
    currentStockPage = 1;

    document.querySelectorAll('#stockStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.stockfilter === status) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    applyStockFilters();
}

function clearStockSearch() {
    const input = document.getElementById('stockSearchInput');
    input.value = '';
    document.getElementById('clearStockSearchBtn').classList.add('hidden');
    currentStockPage = 1;
    applyStockFilters();
    input.focus();
}

function resetStockFilters() {
    document.getElementById('stockSearchInput').value = '';
    document.getElementById('clearStockSearchBtn').classList.add('hidden');
    currentStockStatusFilter = 'all';
    document.querySelectorAll('#stockStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.stockfilter === 'all') tab.classList.add('active');
        else tab.classList.remove('active');
    });
    currentStockPage = 1;
    applyStockFilters();
}

function setStockPageSize(size) {
    stockPageSize = (size === 'all') ? 9999 : parseInt(size);
    currentStockPage = 1;
    
    document.querySelectorAll('[data-stocksize]').forEach(btn => {
        if (btn.dataset.stocksize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderStockPagination();
}

function goToStockPage(page) {
    currentStockPage = page;
    renderStockPagination();
}

function applyStockFilters() {
    const input = document.getElementById('stockSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearStockSearchBtn');
    const resetBtn = document.getElementById('resetStockFiltersBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const isFiltered = (currentStockStatusFilter !== 'all' || query.length > 0);
    if (resetBtn) {
        if (isFiltered) resetBtn.classList.remove('hidden');
        else resetBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#stocksTableBody .stock-row'));
    filteredStockRows = [];

    rows.forEach(row => {
        const rowStatus = row.dataset.status || '';
        const rowSearch = row.dataset.search || '';

        let statusMatches = false;
        if (currentStockStatusFilter === 'all') {
            statusMatches = true;
        } else if (currentStockStatusFilter === 'active') {
            statusMatches = (rowStatus === 'active' || rowStatus === 'stored' || rowStatus === 'in_stock');
        } else {
            statusMatches = (rowStatus === currentStockStatusFilter);
        }

        let queryMatches = (!query || rowSearch.includes(query));

        if (statusMatches && queryMatches) {
            filteredStockRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderStockPagination();
}

function renderStockPagination() {
    const totalCount = filteredStockRows.length;
    const effectivePageSize = stockPageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentStockPage > totalPages) currentStockPage = 1;
    if (currentStockPage < 1) currentStockPage = 1;

    const startIndex = (currentStockPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#stocksTableBody .stock-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredStockRows[i]) {
            filteredStockRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredStockCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching stock items';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} items`;
        }
    }

    const noMatchesRow = document.getElementById('noStockMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('stockPaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToStockPage(${currentStockPage - 1})" 
                ${currentStockPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentStockPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToStockPage(${p})" 
                    class="pager-btn ${p === currentStockPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToStockPage(${currentStockPage + 1})" 
                ${currentStockPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentStockPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    applyStockFilters();
});
if (document.readyState !== 'loading') {
    applyStockFilters();
}
</script>
@endsection