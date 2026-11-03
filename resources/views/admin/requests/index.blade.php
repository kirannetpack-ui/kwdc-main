@extends('layouts.app')

@section('title', 'Client Requests')
@section('header', 'Client Requests')

@section('content')
@php
    $totalCount = $requests->count();
    $pendingCount = $requests->where('status', 'pending')->count();
    $approvedCount = $requests->where('status', 'approved')->count();
    $rejectedCount = $requests->where('status', 'rejected')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Client Requests</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-inbox text-[9px]"></i>
                {{ $totalCount }} Inquiries
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
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="reqStatusFilterTabs">
                <button type="button" 
                        onclick="setRequestStatusFilter('all')" 
                        data-filter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ $totalCount }}</span>
                </button>
                @if($pendingCount > 0)
                <button type="button" 
                        onclick="setRequestStatusFilter('pending')" 
                        data-filter="pending"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Pending</span>
                    <span class="tab-count-badge">{{ $pendingCount }}</span>
                </button>
                @endif
                @if($approvedCount > 0)
                <button type="button" 
                        onclick="setRequestStatusFilter('approved')" 
                        data-filter="approved"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Approved</span>
                    <span class="tab-count-badge">{{ $approvedCount }}</span>
                </button>
                @endif
                @if($rejectedCount > 0)
                <button type="button" 
                        onclick="setRequestStatusFilter('rejected')" 
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
                       id="requestSearchInput" 
                       placeholder="Search request #, client, warehouse..." 
                       oninput="applyRequestFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearReqSearchBtn" 
                        onclick="clearRequestSearch()" 
                        class="hidden kwdc-search-clear-slot" 
                        title="Clear search">
                    <i class="fas fa-circle-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-slate-200/80 mt-2.5">
            <table class="w-full table-fixed text-left border-collapse">
                <thead class="bg-slate-50/95 border-b border-slate-200/80">
                    <tr>
                        <th class="w-[14%] px-3 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Request ID</th>
                        <th class="w-[24%] px-3 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Client</th>
                        <th class="w-[26%] px-3 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Warehouse</th>
                        <th class="w-[14%] px-3 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="w-[12%] px-3 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="w-[10%] px-3 py-2 text-right text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="requestsTableBody">
                    @forelse($requests ?? [] as $request)
                    @php
                        $status = strtolower($request->status ?? 'pending');
                        $clientName = $request->client->name ?? null;
                        $whName = $request->warehouse->name ?? null;
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $request->id,
                            '#' . $request->id,
                            $clientName,
                            $whName,
                            $status,
                            $request->created_at ? $request->created_at->format('Y-m-d M d') : ''
                        ])));
                    @endphp
                    <tr class="request-row hover:bg-slate-50/80 transition-colors"
                        data-status="{{ $status }}"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- Request ID -->
                        <td class="px-3 py-1.5">
                            <span class="font-bold text-slate-900 text-xs truncate block" title="#{{ $request->id }}">#{{ $request->id }}</span>
                        </td>

                        <!-- Client -->
                        <td class="px-3 py-1.5">
                            @if($clientName)
                                <span class="font-semibold text-slate-800 text-xs truncate block" title="{{ $clientName }}">{{ $clientName }}</span>
                            @else
                                <span class="text-slate-400 text-xs">Direct Shipper</span>
                            @endif
                        </td>

                        <!-- Warehouse -->
                        <td class="px-3 py-1.5 text-xs text-slate-700">
                            @if($whName)
                                <span class="inline-flex items-center gap-1.5 font-medium text-slate-800 truncate max-w-full" title="{{ $whName }}">
                                    <i class="fas fa-warehouse text-slate-400 text-[10px] shrink-0"></i>
                                    <span class="truncate">{{ $whName }}</span>
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                    Unassigned
                                </span>
                            @endif
                        </td>
                        
                        <!-- Status Column -->
                        <td class="px-3 py-1.5">
                            @php
                                $badgeStyles = match($status) {
                                    'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                    'pending' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                    'rejected' => 'bg-rose-50 text-rose-700 border-rose-200/80',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeStyles }}">
                                @if($status === 'approved')
                                    <i class="fas fa-check text-[8px]"></i>
                                @endif
                                {{ ucfirst($status) }}
                            </span>
                        </td>

                        <!-- Date Column -->
                        <td class="px-3 py-1.5 text-xs text-slate-500 truncate" title="{{ $request->created_at ? $request->created_at->format('M d, Y') : '-' }}">
                            {{ $request->created_at ? $request->created_at->format('M d, Y') : '-' }}
                        </td>
                        
                        <!-- Actions Column -->
                        <td class="px-3 py-1.5 text-right text-xs">
                            <a href="{{ route('warehouse-requests.show', $request->id) }}" 
                               class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-[10.5px] font-semibold transition border border-slate-200 shadow-2xs group"
                               title="View Request">
                                <span>View</span>
                                <i class="fas fa-arrow-right text-[8px] opacity-60 group-hover:translate-x-0.5 transition-transform"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyReqRow">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-inbox text-2xl text-slate-300 block mb-1"></i>
                            No client requests found.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noReqMatchesRow" class="hidden">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching requests</span>
                            <button type="button" onclick="resetRequestFilters()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
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
                <span id="filteredReqCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ $totalCount }} requests
                </span>
                <button type="button" 
                        id="resetReqFiltersBtn" 
                        onclick="resetRequestFilters()" 
                        class="hidden text-orange-600 hover:underline font-semibold text-xs">
                    Reset
                </button>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setReqPageSize(5)" data-reqsize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setReqPageSize(10)" data-reqsize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setReqPageSize('all')" data-reqsize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="reqPaginationControls">
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
let currentReqFilter = 'all';
let currentReqPage = 1;
let reqPageSize = 5;
let filteredReqRows = [];

function setRequestStatusFilter(filter) {
    currentReqFilter = filter;
    currentReqPage = 1;
    
    document.querySelectorAll('#reqStatusFilterTabs .kwdc-tab-pill').forEach(btn => {
        if (btn.dataset.filter === filter) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    applyRequestFilters();
}

function clearRequestSearch() {
    const input = document.getElementById('requestSearchInput');
    input.value = '';
    document.getElementById('clearReqSearchBtn').classList.add('hidden');
    currentReqPage = 1;
    applyRequestFilters();
    input.focus();
}

function resetRequestFilters() {
    document.getElementById('requestSearchInput').value = '';
    document.getElementById('clearReqSearchBtn').classList.add('hidden');
    currentReqPage = 1;
    setRequestStatusFilter('all');
}

function setReqPageSize(size) {
    reqPageSize = (size === 'all') ? 9999 : parseInt(size);
    currentReqPage = 1;
    
    document.querySelectorAll('[data-reqsize]').forEach(btn => {
        if (btn.dataset.reqsize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderReqPagination();
}

function goToReqPage(page) {
    currentReqPage = page;
    renderReqPagination();
}

function applyRequestFilters() {
    const input = document.getElementById('requestSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearReqSearchBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#requestsTableBody .request-row'));
    filteredReqRows = [];

    rows.forEach(row => {
        const rowStatus = row.dataset.status || '';
        const rowSearch = row.dataset.search || '';

        const statusMatches = (currentReqFilter === 'all' || rowStatus === currentReqFilter);
        const queryMatches = (!query || rowSearch.includes(query));

        if (statusMatches && queryMatches) {
            filteredReqRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderReqPagination();
}

function renderReqPagination() {
    const totalCount = filteredReqRows.length;
    const effectivePageSize = reqPageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentReqPage > totalPages) currentReqPage = 1;
    if (currentReqPage < 1) currentReqPage = 1;

    const startIndex = (currentReqPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#requestsTableBody .request-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredReqRows[i]) {
            filteredReqRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredReqCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching requests';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} requests`;
        }
    }

    const searchInput = document.getElementById('requestSearchInput');
    const query = (searchInput ? searchInput.value : '').trim();
    const isFiltered = (currentReqFilter !== 'all' || query.length > 0);
    const resetBtn = document.getElementById('resetReqFiltersBtn');
    if (resetBtn) {
        if (isFiltered) resetBtn.classList.remove('hidden');
        else resetBtn.classList.add('hidden');
    }

    const noMatchesRow = document.getElementById('noReqMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('reqPaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToReqPage(${currentReqPage - 1})" 
                ${currentReqPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentReqPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToReqPage(${p})" 
                    class="pager-btn ${p === currentReqPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToReqPage(${currentReqPage + 1})" 
                ${currentReqPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentReqPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    applyRequestFilters();
});
if (document.readyState !== 'loading') {
    applyRequestFilters();
}
</script>
@endsection
