@extends('layouts.app')

@section('title', 'Driver Management')
@section('header', 'Driver Management')

@section('content')
@php
    $totalCount = $drivers instanceof \Illuminate\Pagination\AbstractPaginator ? $drivers->total() : count($drivers ?? []);
    $items = $drivers instanceof \Illuminate\Pagination\AbstractPaginator ? $drivers->items() : ($drivers ?? []);
    $activeCount = collect($items)->where('is_active', true)->count();
    $inactiveCount = count($items) - $activeCount;
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Driver Management</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-id-card text-[9px]"></i>
                {{ $totalCount }} Drivers
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
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="driverStatusTabs">
                <button type="button" 
                        onclick="setDriverStatusFilter('all')" 
                        data-driverfilter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ count($items) }}</span>
                </button>
                @if($activeCount > 0)
                <button type="button" 
                        onclick="setDriverStatusFilter('active')" 
                        data-driverfilter="active"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Active</span>
                    <span class="tab-count-badge">{{ $activeCount }}</span>
                </button>
                @endif
                @if($inactiveCount > 0)
                <button type="button" 
                        onclick="setDriverStatusFilter('inactive')" 
                        data-driverfilter="inactive"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                    <span>Inactive</span>
                    <span class="tab-count-badge">{{ $inactiveCount }}</span>
                </button>
                @endif
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full lg:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="driverSearchInput" 
                       placeholder="Search driver #, name, email..." 
                       oninput="applyDriverFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearDriverSearchBtn" 
                        onclick="clearDriverSearch()" 
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
                        <th class="w-[10%]">ID</th>
                        <th class="w-[26%]">Driver</th>
                        <th class="w-[24%]">Email</th>
                        <th class="w-[16%]">Phone</th>
                        <th class="w-[12%]">Status</th>
                        <th class="w-[12%] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="driversTableBody">
                    @forelse($items as $driver)
                    @php
                        $name = $driver->name ?? 'Driver';
                        $email = $driver->email ?? '';
                        $phone = $driver->phone ?? 'N/A';
                        $status = $driver->is_active ? 'active' : 'inactive';
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $driver->id,
                            '#' . $driver->id,
                            $name,
                            $email,
                            $phone,
                            $status
                        ])));
                    @endphp
                    <tr class="driver-row hover:bg-slate-50/80 transition-colors"
                        data-status="{{ $status }}"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- ID -->
                        <td>
                            <span class="font-bold text-slate-900 text-xs truncate block" title="#{{ $driver->id }}">#{{ $driver->id }}</span>
                        </td>

                        <!-- Driver Name & Rating -->
                        <td>
                            <div class="flex items-center gap-2 min-w-0" title="{{ $name }}">
                                <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-[10px] shrink-0">
                                    {{ strtoupper(substr($name, 0, 1)) }}
                                </div>
                                <div class="truncate">
                                    <span class="font-bold text-slate-900 text-xs truncate block">{{ $name }}</span>
                                    @if($driver->avg_rating)
                                        <span class="inline-flex items-center gap-1 text-[10px] text-amber-600 font-semibold">
                                            <i class="fas fa-star text-[9px]"></i>
                                            {{ number_format($driver->avg_rating, 1) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Email -->
                        <td>
                            <span class="text-slate-600 text-xs truncate block" title="{{ $email }}">
                                {{ $email }}
                            </span>
                        </td>

                        <!-- Phone -->
                        <td>
                            <span class="text-slate-700 text-xs truncate block" title="{{ $phone }}">
                                {{ $phone }}
                            </span>
                        </td>

                        <!-- Status Badge -->
                        <td>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $driver->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200/80' : 'bg-rose-50 text-rose-700 border-rose-200/80' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $driver->is_active ? 'bg-emerald-500' : 'bg-rose-500' }} shrink-0"></span>
                                <span>{{ $driver->is_active ? 'Active' : 'Inactive' }}</span>
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <a href="{{ route('admin.drivers.show', $driver->id) }}" 
                                   class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-600 transition border border-slate-200 shadow-2xs" 
                                   title="View Driver Profile">
                                    <i class="fas fa-eye text-[9px]"></i>
                                </a>
                                <a href="{{ route('admin.drivers.edit', $driver->id) }}" 
                                   class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-600 transition border border-slate-200 shadow-2xs" 
                                   title="Edit Driver">
                                    <i class="fas fa-pen text-[9px]"></i>
                                </a>
                                <form action="{{ route('admin.drivers.destroy', $driver->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-rose-600 hover:text-white text-rose-500 transition border border-slate-200 shadow-2xs" 
                                            onclick="return confirm('Are you sure you want to delete this driver?')"
                                            title="Delete Driver">
                                        <i class="fas fa-trash text-[9px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyDriverRow">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-id-card text-2xl text-slate-300 block mb-1"></i>
                            No drivers registered in the system.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noDriverMatchesRow" class="hidden">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching drivers found</span>
                            <button type="button" onclick="resetDriverFilters()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
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
                <span id="filteredDriverCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ count($items) }} drivers
                </span>
                <button type="button" 
                        id="resetDriverFiltersBtn" 
                        onclick="resetDriverFilters()" 
                        class="hidden text-orange-600 hover:underline font-semibold text-xs">
                    Reset
                </button>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setDriverPageSize(5)" data-drsize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setDriverPageSize(10)" data-drsize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setDriverPageSize('all')" data-drsize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="driverPaginationControls">
                </div>
            </div>
        </div>

        @if($drivers instanceof \Illuminate\Pagination\AbstractPaginator && $drivers->hasPages())
        <div class="mt-2.5 pt-2 border-t border-slate-100 flex justify-end">
            {{ $drivers->links() }}
        </div>
        @endif

    </div>
</div>

<script>
let currentDriverStatusFilter = 'all';
let currentDriverPage = 1;
let driverPageSize = 5;
let filteredDriverRows = [];

function setDriverStatusFilter(status) {
    currentDriverStatusFilter = status;
    currentDriverPage = 1;

    document.querySelectorAll('#driverStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.driverfilter === status) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    applyDriverFilters();
}

function clearDriverSearch() {
    const input = document.getElementById('driverSearchInput');
    input.value = '';
    document.getElementById('clearDriverSearchBtn').classList.add('hidden');
    currentDriverPage = 1;
    applyDriverFilters();
    input.focus();
}

function resetDriverFilters() {
    document.getElementById('driverSearchInput').value = '';
    document.getElementById('clearDriverSearchBtn').classList.add('hidden');
    currentDriverStatusFilter = 'all';
    document.querySelectorAll('#driverStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.driverfilter === 'all') tab.classList.add('active');
        else tab.classList.remove('active');
    });
    currentDriverPage = 1;
    applyDriverFilters();
}

function setDriverPageSize(size) {
    driverPageSize = (size === 'all') ? 9999 : parseInt(size);
    currentDriverPage = 1;
    
    document.querySelectorAll('[data-drsize]').forEach(btn => {
        if (btn.dataset.drsize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderDriverPagination();
}

function goToDriverPage(page) {
    currentDriverPage = page;
    renderDriverPagination();
}

function applyDriverFilters() {
    const input = document.getElementById('driverSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearDriverSearchBtn');
    const resetBtn = document.getElementById('resetDriverFiltersBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const isFiltered = (currentDriverStatusFilter !== 'all' || query.length > 0);
    if (resetBtn) {
        if (isFiltered) resetBtn.classList.remove('hidden');
        else resetBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#driversTableBody .driver-row'));
    filteredDriverRows = [];

    rows.forEach(row => {
        const rowStatus = row.dataset.status || '';
        const rowSearch = row.dataset.search || '';

        let statusMatches = (currentDriverStatusFilter === 'all' || rowStatus === currentDriverStatusFilter);
        let queryMatches = (!query || rowSearch.includes(query));

        if (statusMatches && queryMatches) {
            filteredDriverRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderDriverPagination();
}

function renderDriverPagination() {
    const totalCount = filteredDriverRows.length;
    const effectivePageSize = driverPageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentDriverPage > totalPages) currentDriverPage = 1;
    if (currentDriverPage < 1) currentDriverPage = 1;

    const startIndex = (currentDriverPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#driversTableBody .driver-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredDriverRows[i]) {
            filteredDriverRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredDriverCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching drivers found';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} drivers`;
        }
    }

    const noMatchesRow = document.getElementById('noDriverMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('driverPaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToDriverPage(${currentDriverPage - 1})" 
                ${currentDriverPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentDriverPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToDriverPage(${p})" 
                    class="pager-btn ${p === currentDriverPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToDriverPage(${currentDriverPage + 1})" 
                ${currentDriverPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentDriverPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    applyDriverFilters();
});
if (document.readyState !== 'loading') {
    applyDriverFilters();
}
</script>
@endsection