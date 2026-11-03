@extends('layouts.app')

@section('title', 'Equipment requests')
@section('header', 'Equipment requests')

@section('content')
@php
    $totalCount = $requests instanceof \Illuminate\Pagination\AbstractPaginator ? $requests->total() : count($requests ?? []);
    $items = $requests instanceof \Illuminate\Pagination\AbstractPaginator ? $requests->items() : ($requests ?? []);
    $pendingCount = collect($items)->where('status', 'pending')->count();
    $assignedCount = collect($items)->whereIn('status', ['assigned', 'approved', 'active'])->count();
    $completedCount = collect($items)->where('status', 'completed')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Equipment Requests</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-tractor text-[9px]"></i>
                {{ $totalCount }} Requests
            </span>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->role === 'client' || auth()->user()->isAdmin())
            <a href="{{ route('equipment-requests.create') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-plus text-[10px]"></i>
                <span>New Request</span>
            </a>
            @endif
        </div>
    </div>

    <!-- Main Data Shell -->
    <div class="bg-white border border-slate-200/80 rounded-xl p-3 sm:p-3.5 shadow-2xs">
        <!-- Filter & Search Toolbar -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
            <!-- Status Tabs -->
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="eqReqStatusTabs">
                <button type="button" 
                        onclick="setEqReqFilter('all')" 
                        data-eqrfilter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ count($items) }}</span>
                </button>
                @if($pendingCount > 0)
                <button type="button" 
                        onclick="setEqReqFilter('pending')" 
                        data-eqrfilter="pending"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Pending</span>
                    <span class="tab-count-badge">{{ $pendingCount }}</span>
                </button>
                @endif
                @if($assignedCount > 0)
                <button type="button" 
                        onclick="setEqReqFilter('assigned')" 
                        data-eqrfilter="assigned"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 shrink-0"></span>
                    <span>Active</span>
                    <span class="tab-count-badge">{{ $assignedCount }}</span>
                </button>
                @endif
                @if($completedCount > 0)
                <button type="button" 
                        onclick="setEqReqFilter('completed')" 
                        data-eqrfilter="completed"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Completed</span>
                    <span class="tab-count-badge">{{ $completedCount }}</span>
                </button>
                @endif
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full lg:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="eqReqSearchInput" 
                       placeholder="Search equipment, location, ID..." 
                       oninput="applyEqReqFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="eqReqSearchClear" 
                        onclick="clearEqReqSearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Zero Horizontal Scroll Table Frame -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="eqRequestsTable">
                <thead>
                    <tr>
                        <th class="w-[180px]">Request / Type</th>
                        <th class="w-[200px]">Location</th>
                        <th class="w-[180px]">Deployment Dates</th>
                        <th class="w-[110px]">Status</th>
                        <th class="w-[70px] text-right"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="eqReqTableBody">
                    @forelse($items as $request)
                    @php
                        $status = strtolower($request->status ?? 'pending');
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition eq-req-row"
                        data-status="{{ $status }}"
                        data-search="{{ strtolower(($request->equipment_type ?? '') . ' #' . $request->id . ' ' . ($request->location ?? '') . ' ' . $status) }}">
                        
                        <!-- Request -->
                        <td>
                            <a href="{{ route('equipment-requests.show', $request) }}" class="font-bold text-slate-900 hover:text-orange-600 block truncate" title="{{ $request->equipment_type }}">
                                {{ $request->equipment_type }}
                            </a>
                            <div class="text-[10px] font-mono text-slate-400">
                                #{{ $request->id }}
                            </div>
                        </td>

                        <!-- Location -->
                        <td>
                            <div class="truncate text-slate-600" title="{{ $request->location }}">
                                <i class="fas fa-map-marker-alt text-orange-400 text-[10px] me-1"></i>
                                {{ \Illuminate\Support\Str::limit($request->location, 30) }}
                            </div>
                        </td>

                        <!-- Dates -->
                        <td class="text-slate-600 text-[11px]">
                            <i class="far fa-calendar-alt text-slate-400 text-[10px] me-1"></i>
                            {{ $request->start_date?->format('M j, Y') ?? '—' }} &rarr; {{ $request->end_date?->format('M j, Y') ?? '—' }}
                        </td>

                        <!-- Status -->
                        <td>
                            @php
                                $badgeCls = match($status) {
                                    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'assigned', 'approved', 'active' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    default => 'bg-amber-50 text-amber-700 border-amber-200',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeCls }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status === 'completed' ? 'bg-emerald-500' : ($status === 'assigned' || $status === 'active' ? 'bg-blue-500' : 'bg-amber-500') }}"></span>
                                {{ ucfirst($status) }}
                            </span>
                        </td>

                        <!-- Action -->
                        <td class="text-right">
                            <a href="{{ route('equipment-requests.show', $request) }}" 
                               aria-label="Open equipment request {{ $request->id }}"
                               class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-orange-500 hover:text-white text-slate-600 transition"
                               title="View Details">
                                <i class="fas fa-arrow-right text-[11px]" aria-hidden="true"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-tractor text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold text-slate-700">No equipment requests yet</span>
                                @if(auth()->user()->role === 'client' || auth()->user()->isAdmin())
                                <a href="{{ route('equipment-requests.create') }}" class="mt-2 text-xs text-orange-600 font-bold hover:underline">
                                    Create new request &rarr;
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyEqReqSearch" class="hidden">
                        <td colspan="5" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No requests match your filter</span>
                                <button type="button" onclick="clearEqReqSearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
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
            <div>
                Showing <span id="eqReqRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="eqReqTotalVisible" class="font-bold text-slate-800">{{ count($items) }}</span> requests
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="eqReqPaginationControls">
                <button type="button" id="eqReqPrevBtn" onclick="prevEqReqPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="eqReqPageNumbers" class="flex items-center gap-1"></div>
                <button type="button" id="eqReqNextBtn" onclick="nextEqReqPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentEqReqFilter = 'all';
let currentEqReqPage = 1;
const EQ_REQ_PER_PAGE = 5;

function setEqReqFilter(status) {
    currentEqReqFilter = status;
    currentEqReqPage = 1;

    document.querySelectorAll('#eqReqStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-eqrfilter') === status) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    applyEqReqFilters();
}

function clearEqReqSearch() {
    const input = document.getElementById('eqReqSearchInput');
    if (input) input.value = '';
    currentEqReqFilter = 'all';
    document.querySelectorAll('#eqReqStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-eqrfilter') === 'all') {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    applyEqReqFilters();
}

function applyEqReqFilters() {
    const searchVal = (document.getElementById('eqReqSearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('eqReqSearchClear');
    if (clearBtn) clearBtn.classList.toggle('visible', searchVal.length > 0);

    const rows = Array.from(document.querySelectorAll('.eq-req-row'));
    const matched = [];

    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status') || '';
        const searchData = row.getAttribute('data-search') || '';

        let matchesStatus = (currentEqReqFilter === 'all');
        if (currentEqReqFilter === 'assigned') {
            matchesStatus = (rowStatus === 'assigned' || rowStatus === 'approved' || rowStatus === 'active');
        } else if (currentEqReqFilter !== 'all') {
            matchesStatus = (rowStatus === currentEqReqFilter);
        }

        const matchesSearch = !searchVal || searchData.includes(searchVal);

        if (matchesStatus && matchesSearch) {
            matched.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const emptyClient = document.getElementById('emptyEqReqSearch');
    if (emptyClient) emptyClient.classList.toggle('hidden', matched.length > 0 || rows.length === 0);

    paginateEqReq(matched);
}

function paginateEqReq(matched) {
    const total = matched.length;
    const totalPages = Math.max(1, Math.ceil(total / EQ_REQ_PER_PAGE));

    if (currentEqReqPage > totalPages) currentEqReqPage = totalPages;
    if (currentEqReqPage < 1) currentEqReqPage = 1;

    matched.forEach((row, idx) => {
        const start = (currentEqReqPage - 1) * EQ_REQ_PER_PAGE;
        const end = start + EQ_REQ_PER_PAGE;
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    const startNum = total === 0 ? 0 : (currentEqReqPage - 1) * EQ_REQ_PER_PAGE + 1;
    const endNum = Math.min(total, currentEqReqPage * EQ_REQ_PER_PAGE);

    const rangeEl = document.getElementById('eqReqRange');
    const totalEl = document.getElementById('eqReqTotalVisible');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('eqReqPrevBtn');
    const nextBtn = document.getElementById('eqReqNextBtn');
    if (prevBtn) prevBtn.disabled = currentEqReqPage <= 1;
    if (nextBtn) nextBtn.disabled = currentEqReqPage >= totalPages;

    const container = document.getElementById('eqReqPageNumbers');
    if (container) {
        container.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = i;
                btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
                    i === currentEqReqPage ? 'bg-orange-500 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`;
                btn.onclick = () => { currentEqReqPage = i; applyEqReqFilters(); };
                container.appendChild(btn);
            }
        }
    }
}

function prevEqReqPage() {
    if (currentEqReqPage > 1) {
        currentEqReqPage--;
        applyEqReqFilters();
    }
}

function nextEqReqPage() {
    currentEqReqPage++;
    applyEqReqFilters();
}

document.addEventListener('DOMContentLoaded', () => {
    applyEqReqFilters();
});
</script>
@endpush
@endsection
