@extends('layouts.app')

@section('title', 'All Dispatch Orders')
@section('header', 'All Dispatch Orders')

@section('content')
@php
    $totalCount = $orders->count();
    $inTransitCount = $orders->whereIn('status', ['on_the_way', 'in_transit'])->count();
    $pickedUpCount = $orders->where('status', 'picked_up')->count();
    $assignedCount = $orders->where('status', 'assigned')->count();
    $pendingCount = $orders->where('status', 'pending')->count();
    $completedCount = $orders->whereIn('status', ['delivered', 'completed'])->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">

    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">All Dispatch Orders</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-truck-fast text-[9px]"></i>
                {{ $totalCount }} Manifests
            </span>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="{{ route('dispatch.direct-create') }}" 
               class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition inline-flex items-center gap-1.5 shadow-2xs">
                <i class="fas fa-plus text-[10px]"></i>
                <span>New Dispatch</span>
            </a>
        </div>
    </div>

    <!-- Ultra-Low-Profile Status Signal Strip (Click to Filter) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
        <!-- In Transit -->
        <div onclick="setStatusFilter('in_transit')" 
             class="kwdc-kpi-card px-2.5 py-1.5 rounded-lg bg-white border border-slate-200/80 hover:border-sky-300 hover:bg-sky-50/20 transition cursor-pointer flex items-center justify-between shadow-2xs group">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-2 h-2 rounded-full bg-sky-500 animate-pulse shrink-0"></span>
                <span class="text-[11px] font-semibold text-slate-600 group-hover:text-sky-600 transition truncate">On Road</span>
            </div>
            <span class="text-xs font-black text-slate-900 ml-1.5">{{ $inTransitCount }}</span>
        </div>

        <!-- Picked Up -->
        <div onclick="setStatusFilter('picked_up')" 
             class="kwdc-kpi-card px-2.5 py-1.5 rounded-lg bg-white border border-slate-200/80 hover:border-indigo-300 hover:bg-indigo-50/20 transition cursor-pointer flex items-center justify-between shadow-2xs group">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-2 h-2 rounded-full bg-indigo-500 shrink-0"></span>
                <span class="text-[11px] font-semibold text-slate-600 group-hover:text-indigo-600 transition truncate">Picked Up</span>
            </div>
            <span class="text-xs font-black text-slate-900 ml-1.5">{{ $pickedUpCount }}</span>
        </div>

        <!-- Pending Assignment -->
        <div onclick="setStatusFilter('pending')" 
             class="kwdc-kpi-card px-2.5 py-1.5 rounded-lg bg-white border border-slate-200/80 hover:border-amber-300 hover:bg-amber-50/20 transition cursor-pointer flex items-center justify-between shadow-2xs group">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                <span class="text-[11px] font-semibold text-slate-600 group-hover:text-amber-600 transition truncate">Awaiting</span>
            </div>
            <span class="text-xs font-black text-slate-900 ml-1.5">{{ $pendingCount }}</span>
        </div>

        <!-- Completed -->
        <div onclick="setStatusFilter('completed')" 
             class="kwdc-kpi-card px-2.5 py-1.5 rounded-lg bg-white border border-slate-200/80 hover:border-emerald-300 hover:bg-emerald-50/20 transition cursor-pointer flex items-center justify-between shadow-2xs group">
            <div class="flex items-center gap-2 min-w-0">
                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                <span class="text-[11px] font-semibold text-slate-600 group-hover:text-emerald-600 transition truncate">Delivered</span>
            </div>
            <span class="text-xs font-black text-slate-900 ml-1.5">{{ $completedCount }}</span>
        </div>
    </div>

    <!-- Main Data Shell -->
    <div class="bg-white border border-slate-200/80 rounded-xl p-3 sm:p-3.5 shadow-2xs">

        <!-- Unified Search & Filter Command Bar -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 pb-3 border-b border-slate-100">
            
            <!-- Segmented Status Tabs -->
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="statusFilterTabs">
                <button type="button" 
                        onclick="setStatusFilter('all')" 
                        data-filter="all"
                        class="kwdc-tab-pill active">
                    <span>All Orders</span>
                    <span class="tab-count-badge">{{ $totalCount }}</span>
                </button>
                @if($inTransitCount > 0)
                <button type="button" 
                        onclick="setStatusFilter('in_transit')" 
                        data-filter="in_transit"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 shrink-0"></span>
                    <span>On the Way</span>
                    <span class="tab-count-badge">{{ $inTransitCount }}</span>
                </button>
                @endif
                @if($pickedUpCount > 0)
                <button type="button" 
                        onclick="setStatusFilter('picked_up')" 
                        data-filter="picked_up"
                        class="kwdc-tab-pill">
                    <span>Picked Up</span>
                    <span class="tab-count-badge">{{ $pickedUpCount }}</span>
                </button>
                @endif
                @if($assignedCount > 0)
                <button type="button" 
                        onclick="setStatusFilter('assigned')" 
                        data-filter="assigned"
                        class="kwdc-tab-pill">
                    <span>Assigned</span>
                    <span class="tab-count-badge">{{ $assignedCount }}</span>
                </button>
                @endif
                @if($pendingCount > 0)
                <button type="button" 
                        onclick="setStatusFilter('pending')" 
                        data-filter="pending"
                        class="kwdc-tab-pill">
                    <span>Pending</span>
                    <span class="tab-count-badge">{{ $pendingCount }}</span>
                </button>
                @endif
                @if($completedCount > 0)
                <button type="button" 
                        onclick="setStatusFilter('completed')" 
                        data-filter="completed"
                        class="kwdc-tab-pill">
                    <span>Delivered</span>
                    <span class="tab-count-badge">{{ $completedCount }}</span>
                </button>
                @endif
            </div>

            <!-- Sleek Search Input -->
            <div class="relative w-full lg:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="orderSearchInput" 
                       placeholder="Search ID, client, driver, city..." 
                       oninput="applyFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearSearchBtn" 
                        onclick="clearSearch()" 
                        class="hidden kwdc-search-clear-slot" 
                        title="Clear search">
                    <i class="fas fa-circle-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Compact Data Table (Zero-Scroll Table-Fixed Fit) -->
        <div class="overflow-hidden rounded-xl border border-slate-200/80 mt-2.5">
            <table class="w-full table-fixed text-left border-collapse">
                <thead class="bg-slate-50/95 border-b border-slate-200/80">
                    <tr>
                        <th class="w-[14%] px-2.5 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">ID / Tracking</th>
                        <th class="w-[20%] px-2.5 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Shipper</th>
                        <th class="w-[18%] px-2.5 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Driver</th>
                        <th class="w-[24%] px-2.5 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Route Corridor</th>
                        <th class="w-[9%] px-2 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Tariff</th>
                        <th class="w-[10%] px-2 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                        <th class="w-[5%] px-2 py-2 text-right text-[10.5px] font-bold text-slate-500 uppercase tracking-wider whitespace-nowrap"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="ordersTableBody">
                    @forelse($orders ?? [] as $order)
                    @php
                        $clientName = $order->client->name ?? $order->warehouseRequest->client->name ?? 'Commercial Shipper';
                        $phone = $order->pickup_contact_phone ?? null;
                        $driverName = $order->driver->name ?? null;
                        $vehicle = $order->vehicle->model ?? null;
                        $pickup = $order->pickup_address ?? 'Kathmandu';
                        $delivery = $order->delivery_address ?? 'Destination';
                        $amount = (float) ($order->grand_total ?? $order->base_price ?? 0);
                        $distance = (float) ($order->total_distance ?? 0);
                        $status = strtolower($order->status ?? 'pending');

                        // Initial Avatar
                        $words = preg_split('/\s+/', trim($clientName));
                        $initials = (count($words) >= 2) 
                            ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                            : strtoupper(substr($clientName, 0, 2));

                        $avatarPalettes = [
                            'bg-sky-100 text-sky-700 border-sky-200',
                            'bg-indigo-100 text-indigo-700 border-indigo-200',
                            'bg-amber-100 text-amber-800 border-amber-200',
                            'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'bg-rose-100 text-rose-700 border-rose-200',
                            'bg-purple-100 text-purple-700 border-purple-200',
                        ];
                        $avatarStyle = $avatarPalettes[$order->id % count($avatarPalettes)];

                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $order->id,
                            '#' . $order->id,
                            $order->tracking_id,
                            $clientName,
                            $phone,
                            $driverName,
                            $vehicle,
                            $pickup,
                            $delivery,
                            $status,
                            str_replace('_', ' ', $status)
                        ])));
                    @endphp
                    <tr class="order-row hover:bg-slate-50/70 transition-colors" 
                        data-status="{{ $status }}" 
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- Order / Tracking ID -->
                        <td class="px-2.5 py-1.5 overflow-hidden">
                            <div class="flex items-center gap-1 min-w-0">
                                <span class="font-extrabold text-slate-900 text-xs shrink-0">#{{ $order->id }}</span>
                                @if($order->tracking_id)
                                    <span class="text-[9.5px] text-slate-500 font-mono bg-slate-100 px-1 py-0.5 rounded border border-slate-200/70 truncate" title="{{ $order->tracking_id }}">
                                        {{ $order->tracking_id }}
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Shipper -->
                        <td class="px-2.5 py-1.5 overflow-hidden">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="w-5 h-5 rounded-full {{ $avatarStyle }} border font-bold text-[9px] flex items-center justify-center shrink-0">
                                    {{ $initials }}
                                </span>
                                <span class="font-bold text-slate-800 text-xs truncate" title="{{ $clientName }}">{{ $clientName }}</span>
                            </div>
                        </td>

                        <!-- Driver -->
                        <td class="px-2.5 py-1.5 overflow-hidden">
                            @if($driverName)
                                <div class="flex items-center gap-1 min-w-0" title="{{ $driverName }}{{ $vehicle ? ' (' . $vehicle . ')' : '' }}">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span class="font-medium text-slate-800 text-xs truncate">{{ $driverName }}</span>
                                </div>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200/70 shrink-0">
                                    Unassigned
                                </span>
                            @endif
                        </td>

                        <!-- Route Corridor (Single-Line Compact) -->
                        <td class="px-2.5 py-1.5 overflow-hidden">
                            <div class="flex items-center gap-1 text-xs text-slate-700 font-medium min-w-0" title="{{ $pickup }} → {{ $delivery }} ({{ number_format($distance, 1) }} km)">
                                <span class="truncate">{{ $pickup }}</span>
                                <i class="fas fa-arrow-right text-[7px] text-slate-300 shrink-0"></i>
                                <span class="truncate">{{ $delivery }}</span>
                                @if($distance > 0)
                                    <span class="text-[9.5px] text-slate-400 shrink-0 font-normal">({{ number_format($distance, 0) }}km)</span>
                                @endif
                            </div>
                        </td>

                        <!-- Billing -->
                        <td class="px-2 py-1.5 overflow-hidden">
                            @if($amount > 0)
                                <span class="font-extrabold text-slate-900 text-xs truncate block">रू {{ number_format($amount) }}</span>
                            @else
                                <span class="text-slate-400 text-xs font-medium truncate block">Standard</span>
                            @endif
                        </td>

                        <!-- Status Badge -->
                        <td class="px-2 py-1.5 overflow-hidden">
                            @php
                                $badgeStyles = match($status) {
                                    'delivered', 'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'on_the_way', 'in_transit' => 'bg-sky-50 text-sky-700 border-sky-200',
                                    'picked_up' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                    'assigned' => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'cancelled' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[9.5px] font-bold border truncate {{ $badgeStyles }}">
                                @if(in_array($status, ['on_the_way', 'in_transit']))
                                    <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse shrink-0"></span>
                                @endif
                                <span class="truncate">{{ ucwords(str_replace('_', ' ', $status)) }}</span>
                            </span>
                        </td>

                        <!-- Action Button -->
                        <td class="px-2 py-1.5 text-right overflow-hidden">
                            <a href="{{ route('dispatch.show', $order->id) }}" 
                                class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 transition border border-slate-200 shadow-2xs group"
                                title="View Order">
                                <i class="fas fa-arrow-right text-[8px] opacity-70 group-hover:translate-x-0.5 transition-transform"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyStaticRow">
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-truck-ramp-box text-2xl text-slate-300 block mb-1"></i>
                            No dispatch orders found.
                        </td>
                    </tr>
                    @endforelse

                    <!-- Zero Search Matches State -->
                    <tr id="noMatchesRow" class="hidden">
                        <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching orders</span>
                            <button type="button" onclick="resetAllFilters()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
                                Clear filters
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Sleek Bottom Pager Toolbar (Zero-Scroll Controls) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2.5 mt-1 border-t border-slate-100 text-xs text-slate-500">
            <!-- Count Readout -->
            <div class="flex items-center gap-3">
                <span id="filteredResultsCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ $totalCount }} orders
                </span>
                <button type="button" 
                        id="resetFiltersBtn" 
                        onclick="resetAllFilters()" 
                        class="hidden text-orange-600 hover:underline font-semibold text-xs">
                    Reset
                </button>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <!-- Page Size Selector -->
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setPageSize(5)" data-size="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setPageSize(10)" data-size="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setPageSize('all')" data-size="all" class="page-size-btn">All</button>
                </div>

                <!-- Page Buttons -->
                <div class="inline-flex items-center gap-1" id="paginationControls">
                    <!-- Injected by JavaScript -->
                </div>
            </div>
        </div>

    </div>
</div>

<style>
/* Modern Scoped Tab Pills */
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

/* Bulletproof Search Input Styling */
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

/* Pager Buttons */
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
let currentStatusFilter = 'all';
let currentPage = 1;
let pageSize = 5;
let filteredRows = [];

function setStatusFilter(filter) {
    currentStatusFilter = filter;
    currentPage = 1;
    
    document.querySelectorAll('#statusFilterTabs .kwdc-tab-pill').forEach(btn => {
        if (btn.dataset.filter === filter) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    applyFilters();
}

function clearSearch() {
    const searchInput = document.getElementById('orderSearchInput');
    searchInput.value = '';
    document.getElementById('clearSearchBtn').classList.add('hidden');
    currentPage = 1;
    applyFilters();
    searchInput.focus();
}

function resetAllFilters() {
    document.getElementById('orderSearchInput').value = '';
    document.getElementById('clearSearchBtn').classList.add('hidden');
    currentPage = 1;
    setStatusFilter('all');
}

function setPageSize(size) {
    pageSize = (size === 'all') ? 9999 : parseInt(size);
    currentPage = 1;
    
    document.querySelectorAll('.page-size-btn').forEach(btn => {
        if (btn.dataset.size == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderPagination();
}

function goToPage(page) {
    currentPage = page;
    renderPagination();
}

function applyFilters() {
    const searchInput = document.getElementById('orderSearchInput');
    const query = (searchInput.value || '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearSearchBtn');
    
    if (query.length > 0) {
        clearBtn.classList.remove('hidden');
    } else {
        clearBtn.classList.add('hidden');
    }

    const allRows = Array.from(document.querySelectorAll('#ordersTableBody .order-row'));
    filteredRows = [];

    allRows.forEach(row => {
        const rowStatus = row.dataset.status || '';
        const rowSearch = row.dataset.search || '';

        let statusMatches = false;
        if (currentStatusFilter === 'all') {
            statusMatches = true;
        } else if (currentStatusFilter === 'in_transit') {
            statusMatches = (rowStatus === 'on_the_way' || rowStatus === 'in_transit');
        } else if (currentStatusFilter === 'completed') {
            statusMatches = (rowStatus === 'delivered' || rowStatus === 'completed');
        } else {
            statusMatches = (rowStatus === currentStatusFilter);
        }

        const queryMatches = !query || rowSearch.includes(query);

        if (statusMatches && queryMatches) {
            filteredRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderPagination();
}

function renderPagination() {
    const totalCount = filteredRows.length;
    const effectivePageSize = pageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentPage > totalPages) currentPage = 1;
    if (currentPage < 1) currentPage = 1;

    const startIndex = (currentPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    // Hide all rows, then display only current page slice
    const allRows = document.querySelectorAll('#ordersTableBody .order-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredRows[i]) {
            filteredRows[i].style.display = '';
        }
    }

    // Update Counter Readout
    const counterEl = document.getElementById('filteredResultsCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching dispatch orders';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} orders`;
        }
    }

    // Toggle reset link
    const searchInput = document.getElementById('orderSearchInput');
    const query = (searchInput ? searchInput.value : '').trim();
    const isFiltered = (currentStatusFilter !== 'all' || query.length > 0);
    const resetBtn = document.getElementById('resetFiltersBtn');
    if (resetBtn) {
        if (isFiltered) resetBtn.classList.remove('hidden');
        else resetBtn.classList.add('hidden');
    }

    // Toggle no matches row
    const noMatchesRow = document.getElementById('noMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    // Build Pager Buttons
    const paginationContainer = document.getElementById('paginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToPage(${currentPage - 1})" 
                ${currentPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToPage(${p})" 
                    class="pager-btn ${p === currentPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToPage(${currentPage + 1})" 
                ${currentPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

// Initial Run
document.addEventListener('DOMContentLoaded', function() {
    applyFilters();
});
if (document.readyState !== 'loading') {
    applyFilters();
}
</script>
@endsection



