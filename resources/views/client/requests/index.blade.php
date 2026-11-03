@extends('layouts.app')

@section('title', 'My Requests')
@section('header', 'My Warehouse Requests')

@section('content')
@php
    $totalCount = $requests instanceof \Illuminate\Pagination\AbstractPaginator ? $requests->total() : count($requests ?? []);
    $items = $requests instanceof \Illuminate\Pagination\AbstractPaginator ? $requests->items() : ($requests ?? []);
    $pendingCount = collect($items)->where('status', 'pending')->count();
    $approvedCount = collect($items)->where('status', 'approved')->count();
    $rejectedCount = collect($items)->where('status', 'rejected')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">My Requests</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-clipboard-list text-[9px]"></i>
                {{ $totalCount }} Total
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('my-requests.create') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-plus text-[10px]"></i>
                <span>New Request</span>
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
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="clientReqStatusTabs">
                <button type="button" 
                        onclick="setClientReqFilter('all')" 
                        data-creqfilter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ count($items) }}</span>
                </button>
                @if($pendingCount > 0)
                <button type="button" 
                        onclick="setClientReqFilter('pending')" 
                        data-creqfilter="pending"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Pending</span>
                    <span class="tab-count-badge">{{ $pendingCount }}</span>
                </button>
                @endif
                @if($approvedCount > 0)
                <button type="button" 
                        onclick="setClientReqFilter('approved')" 
                        data-creqfilter="approved"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Approved</span>
                    <span class="tab-count-badge">{{ $approvedCount }}</span>
                </button>
                @endif
                @if($rejectedCount > 0)
                <button type="button" 
                        onclick="setClientReqFilter('rejected')" 
                        data-creqfilter="rejected"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
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
                       id="clientReqSearchInput" 
                       placeholder="Search request #, warehouse..." 
                       oninput="applyClientReqFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clientReqSearchClear" 
                        onclick="clearClientReqSearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Zero Horizontal Scroll Table Frame -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="clientRequestsTable">
                <thead>
                    <tr>
                        <th class="w-[75px]">ID</th>
                        <th class="w-[200px]">Warehouse</th>
                        <th class="w-[120px]">Required Area</th>
                        <th class="w-[110px]">Duration</th>
                        <th class="w-[110px]">Status</th>
                        <th class="w-[110px]">Submitted</th>
                        <th class="w-[80px] text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="clientReqTableBody">
                    @forelse($items as $req)
                    <tr class="hover:bg-slate-50/70 transition client-req-row"
                        data-reqid="{{ $req->id }}"
                        data-status="{{ strtolower($req->status ?? 'pending') }}"
                        data-search="{{ strtolower($req->id . ' ' . ($req->warehouse->name ?? '') . ' ' . ($req->warehouse->location ?? '') . ' ' . $req->status) }}">
                        
                        <!-- ID -->
                        <td class="font-mono font-bold text-slate-800">
                            #{{ $req->id }}
                        </td>

                        <!-- Warehouse -->
                        <td>
                            <div class="truncate font-semibold text-slate-900" title="{{ $req->warehouse->name ?? 'N/A' }}">
                                {{ $req->warehouse->name ?? 'Warehouse' }}
                            </div>
                            @if(optional($req->warehouse)->location)
                            <div class="truncate text-[10px] text-slate-500">
                                <i class="fas fa-map-marker-alt text-orange-400 text-[9px] me-0.5"></i>
                                {{ $req->warehouse->location }}
                            </div>
                            @endif
                        </td>

                        <!-- Area -->
                        <td class="font-semibold text-slate-800">
                            {{ number_format($req->required_area) }} <span class="text-[10px] font-normal text-slate-500">sq ft</span>
                        </td>

                        <!-- Duration -->
                        <td class="text-slate-600">
                            {{ $req->duration_months }} mos
                        </td>

                        <!-- Status Badge -->
                        <td>
                            @php
                                $status = strtolower($req->status ?? 'pending');
                                $badgeCls = match($status) {
                                    'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'rejected' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default    => 'bg-amber-50 text-amber-700 border-amber-200',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeCls }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status === 'approved' ? 'bg-emerald-500' : ($status === 'rejected' ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
                                {{ ucfirst($status) }}
                            </span>
                        </td>

                        <!-- Date -->
                        <td class="text-slate-500 text-[11px]">
                            {{ $req->created_at ? $req->created_at->format('M d, Y') : '—' }}
                        </td>

                        <!-- Action -->
                        <td class="text-right">
                            <a href="{{ route('my-requests.show', $req->id) }}" 
                               class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-orange-500 hover:text-white text-slate-600 transition"
                               title="View Details">
                                <i class="fas fa-eye text-[11px]"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyRowServer">
                        <td colspan="7" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-inbox text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No warehouse requests submitted yet</span>
                                <a href="{{ route('my-requests.create') }}" class="mt-2 text-xs text-orange-600 font-bold hover:underline">
                                    Create your first request &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyRowClient" class="hidden">
                        <td colspan="7" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No requests match your search criteria</span>
                                <button type="button" onclick="clearClientReqSearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
                                    Reset filters
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 5-Row Pagination Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-2.5 mt-2 border-t border-slate-100 text-xs text-slate-500">
            <div id="clientReqPageInfo">
                Showing <span id="clientReqRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="clientReqTotalVisible" class="font-bold text-slate-800">{{ count($items) }}</span> requests
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="clientReqPaginationControls">
                <button type="button" 
                        id="clientReqPrevBtn" 
                        onclick="prevClientReqPage()" 
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="clientReqPageNumbers" class="flex items-center gap-1"></div>
                <button type="button" 
                        id="clientReqNextBtn" 
                        onclick="nextClientReqPage()" 
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentClientReqFilter = 'all';
let currentClientReqPage = 1;
const CLIENT_REQ_PER_PAGE = 5;

function setClientReqFilter(status) {
    currentClientReqFilter = status;
    currentClientReqPage = 1;

    document.querySelectorAll('#clientReqStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-creqfilter') === status) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    applyClientReqFilters();
}

function clearClientReqSearch() {
    const input = document.getElementById('clientReqSearchInput');
    if (input) {
        input.value = '';
    }
    currentClientReqFilter = 'all';
    document.querySelectorAll('#clientReqStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-creqfilter') === 'all') {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    applyClientReqFilters();
}

function applyClientReqFilters() {
    const searchVal = (document.getElementById('clientReqSearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('clientReqSearchClear');
    if (clearBtn) {
        clearBtn.classList.toggle('visible', searchVal.length > 0);
    }

    const rows = Array.from(document.querySelectorAll('.client-req-row'));
    const matchedRows = [];

    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status') || '';
        const searchData = row.getAttribute('data-search') || '';

        const matchesStatus = currentClientReqFilter === 'all' || rowStatus === currentClientReqFilter;
        const matchesSearch = !searchVal || searchData.includes(searchVal);

        if (matchesStatus && matchesSearch) {
            matchedRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const emptyClient = document.getElementById('emptyRowClient');
    if (emptyClient) {
        emptyClient.classList.toggle('hidden', matchedRows.length > 0 || rows.length === 0);
    }

    paginateClientReqRows(matchedRows);
}

function paginateClientReqRows(matchedRows) {
    const total = matchedRows.length;
    const totalPages = Math.max(1, Math.ceil(total / CLIENT_REQ_PER_PAGE));

    if (currentClientReqPage > totalPages) {
        currentClientReqPage = totalPages;
    }
    if (currentClientReqPage < 1) {
        currentClientReqPage = 1;
    }

    matchedRows.forEach((row, idx) => {
        const start = (currentClientReqPage - 1) * CLIENT_REQ_PER_PAGE;
        const end = start + CLIENT_REQ_PER_PAGE;
        if (idx >= start && idx < end) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    const startNum = total === 0 ? 0 : (currentClientReqPage - 1) * CLIENT_REQ_PER_PAGE + 1;
    const endNum = Math.min(total, currentClientReqPage * CLIENT_REQ_PER_PAGE);

    const rangeEl = document.getElementById('clientReqRange');
    const totalEl = document.getElementById('clientReqTotalVisible');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('clientReqPrevBtn');
    const nextBtn = document.getElementById('clientReqNextBtn');
    if (prevBtn) prevBtn.disabled = currentClientReqPage <= 1;
    if (nextBtn) nextBtn.disabled = currentClientReqPage >= totalPages;

    renderClientReqPageNumbers(totalPages);
}

function renderClientReqPageNumbers(totalPages) {
    const container = document.getElementById('clientReqPageNumbers');
    if (!container) return;
    container.innerHTML = '';

    if (totalPages <= 1) return;

    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = i;
        btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
            i === currentClientReqPage
                ? 'bg-orange-500 text-white shadow-xs'
                : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
        }`;
        btn.onclick = () => {
            currentClientReqPage = i;
            applyClientReqFilters();
        };
        container.appendChild(btn);
    }
}

function prevClientReqPage() {
    if (currentClientReqPage > 1) {
        currentClientReqPage--;
        applyClientReqFilters();
    }
}

function nextClientReqPage() {
    currentClientReqPage++;
    applyClientReqFilters();
}

document.addEventListener('DOMContentLoaded', () => {
    applyClientReqFilters();
});
</script>
@endpush
@endsection