@extends('layouts.app')

@section('title', 'Pending Warehouse Approvals')
@section('header', 'Pending Warehouses')

@section('content')
@php
    $totalCount = $warehouses->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Pending Warehouses</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200/80">
                <i class="fas fa-clock text-[9px]"></i>
                {{ $totalCount }} Awaiting Review
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
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
            <div class="text-xs text-slate-500 font-medium">
                <span id="filteredPendingCount">Showing 1–5 of {{ $totalCount }} pending listings</span>
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full sm:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="pendingWhSearchInput" 
                       placeholder="Search warehouse, address, owner..." 
                       oninput="applyPendingWhFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearPendingSearchBtn" 
                        onclick="clearPendingWhSearch()" 
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
                        <th class="w-[32%] px-3 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Property & Location</th>
                        <th class="w-[22%] px-3 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Owner</th>
                        <th class="w-[16%] px-3 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Capacity</th>
                        <th class="w-[16%] px-3 py-2 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Rate</th>
                        <th class="w-[14%] px-3 py-2 text-right text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="pendingWhTableBody">
                    @forelse($warehouses as $wh)
                    @php
                        $ownerName = $wh->owner->name ?? null;
                        $ownerEmail = $wh->owner->email ?? null;
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $wh->name,
                            $wh->address,
                            $wh->location,
                            $ownerName,
                            $ownerEmail
                        ])));
                        $fullLocation = $wh->address ?? $wh->location ?? 'Location Pending';
                    @endphp
                    <tr class="pending-wh-row hover:bg-slate-50/80 transition-colors"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- Property & Location -->
                        <td class="px-3 py-1.5">
                            <span class="font-bold text-slate-900 text-xs truncate block" title="{{ $wh->name }}">{{ $wh->name }}</span>
                            <span class="text-[11px] text-slate-500 inline-flex items-center gap-1 truncate max-w-full" title="{{ $fullLocation }}">
                                <i class="fas fa-location-dot text-slate-400 text-[10px] shrink-0"></i>
                                <span class="truncate">{{ $fullLocation }}</span>
                            </span>
                        </td>

                        <!-- Owner -->
                        <td class="px-3 py-1.5 text-xs text-slate-700">
                            @if($ownerName)
                                <span class="font-medium text-slate-800 truncate block" title="{{ $ownerName }} ({{ $ownerEmail }})">{{ $ownerName }}</span>
                            @else
                                <span class="text-slate-400 text-[11px]">Direct Submission</span>
                            @endif
                        </td>

                        <!-- Capacity & Dimensions -->
                        <td class="px-3 py-1.5 text-xs text-slate-700">
                            <span class="font-semibold text-slate-800 truncate block">
                                {{ number_format($wh->total_capacity ?? 0) }} {{ $wh->type === 'building' ? 'm³' : 'sq ft' }}
                            </span>
                        </td>

                        <!-- Rate / Pricing -->
                        <td class="px-3 py-1.5 text-xs">
                            <span class="font-bold text-slate-900 truncate block">
                                रू {{ number_format($wh->price_per_unit ?? $wh->price_per_sqft ?? 0) }}
                                <span class="text-[10px] text-slate-500 font-normal">/ mo</span>
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="px-3 py-1.5 text-right text-xs">
                            <div class="inline-flex items-center justify-end gap-1">
                                <a href="{{ route('warehouses.show', $wh->id) }}" 
                                   class="px-2 py-0.5 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 font-semibold text-[10.5px] transition border border-slate-200 shadow-2xs"
                                   title="View Details">
                                    View
                                </a>

                                <form method="POST" action="{{ route('admin.approve', $wh) }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="px-2 py-0.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[10.5px] transition shadow-2xs inline-flex items-center gap-1"
                                            title="Approve Listing">
                                        <i class="fas fa-check text-[8px]"></i>
                                        <span>Approve</span>
                                    </button>
                                </form>

                                <form method="POST" action="{{ route('admin.reject', $wh) }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="px-2 py-0.5 rounded-md bg-rose-50 hover:bg-rose-100 text-rose-600 font-semibold text-[10.5px] transition border border-rose-200/70"
                                            onclick="return confirm('Reject this warehouse listing?')"
                                            title="Reject Listing">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyPendingRow">
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-clipboard-check text-2xl text-slate-300 block mb-1"></i>
                            No pending warehouses awaiting review.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noPendingMatchesRow" class="hidden">
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-search text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching pending properties</span>
                            <button type="button" onclick="clearPendingWhSearch()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
                                Clear search
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
                <span id="filteredPendingCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ $totalCount }} pending listings
                </span>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setPendingPageSize(5)" data-pendsize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setPendingPageSize(10)" data-pendsize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setPendingPageSize('all')" data-pendsize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="pendingPaginationControls">
                </div>
            </div>
        </div>

    </div>
</div>

<style>
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
let currentPendingPage = 1;
let pendingPageSize = 5;
let filteredPendingRows = [];

function clearPendingWhSearch() {
    const input = document.getElementById('pendingWhSearchInput');
    input.value = '';
    document.getElementById('clearPendingSearchBtn').classList.add('hidden');
    currentPendingPage = 1;
    applyPendingWhFilters();
    input.focus();
}

function setPendingPageSize(size) {
    pendingPageSize = (size === 'all') ? 9999 : parseInt(size);
    currentPendingPage = 1;
    
    document.querySelectorAll('[data-pendsize]').forEach(btn => {
        if (btn.dataset.pendsize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderPendingPagination();
}

function goToPendingPage(page) {
    currentPendingPage = page;
    renderPendingPagination();
}

function applyPendingWhFilters() {
    const input = document.getElementById('pendingWhSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearPendingSearchBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#pendingWhTableBody .pending-wh-row'));
    filteredPendingRows = [];

    rows.forEach(row => {
        const rowSearch = row.dataset.search || '';
        const queryMatches = (!query || rowSearch.includes(query));

        if (queryMatches) {
            filteredPendingRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderPendingPagination();
}

function renderPendingPagination() {
    const totalCount = filteredPendingRows.length;
    const effectivePageSize = pendingPageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentPendingPage > totalPages) currentPendingPage = 1;
    if (currentPendingPage < 1) currentPendingPage = 1;

    const startIndex = (currentPendingPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#pendingWhTableBody .pending-wh-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredPendingRows[i]) {
            filteredPendingRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredPendingCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching pending properties';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} pending listings`;
        }
    }

    const noMatchesRow = document.getElementById('noPendingMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('pendingPaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToPendingPage(${currentPendingPage - 1})" 
                ${currentPendingPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentPendingPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToPendingPage(${p})" 
                    class="pager-btn ${p === currentPendingPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToPendingPage(${currentPendingPage + 1})" 
                ${currentPendingPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentPendingPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    applyPendingWhFilters();
});
if (document.readyState !== 'loading') {
    applyPendingWhFilters();
}
</script>
@endsection