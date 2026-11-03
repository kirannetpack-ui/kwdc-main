@extends('layouts.app')

@section('title', 'Equipment Job Requests')
@section('header', 'Equipment Job Requests')

@section('content')
@php
    $totalCount = isset($requests) && $requests instanceof \Illuminate\Pagination\AbstractPaginator ? $requests->total() : (isset($requests) ? count($requests) : 0);
    $items = isset($requests) && $requests instanceof \Illuminate\Pagination\AbstractPaginator ? $requests->items() : ($requests ?? []);
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Job Requests</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200/80">
                <i class="fas fa-clipboard-list text-[9px]"></i>
                {{ $totalCount }} Requests
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('equipment.jobs.active') }}" 
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold transition border border-slate-200 shadow-2xs">
                <i class="fas fa-play text-blue-500 text-[9px]"></i>
                <span>Active Jobs</span>
            </a>
            <a href="{{ route('equipment.jobs.history') }}" 
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold transition border border-slate-200 shadow-2xs">
                <i class="fas fa-history text-slate-400 text-[9px]"></i>
                <span>History</span>
            </a>
            <a href="{{ route('equipment.list') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-tractor text-[10px]"></i>
                <span>My Fleet</span>
            </a>
        </div>
    </div>

    <!-- Main Data Shell -->
    <div class="bg-white border border-slate-200/80 rounded-xl p-3 sm:p-3.5 shadow-2xs">
        <!-- Search Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
            <div class="text-xs font-semibold text-slate-600">
                Client equipment rental inquiries requiring quotes and confirmation
            </div>
            
            <div class="relative w-full sm:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="eqJobReqSearchInput" 
                       placeholder="Search client, machinery, ID..." 
                       oninput="applyEqJobReqFilter()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="eqJobReqSearchClear" 
                        onclick="clearEqJobReqSearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Table with Zero Sideways Scroll -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="eqJobReqTable">
                <thead>
                    <tr>
                        <th class="w-[70px]">ID</th>
                        <th class="w-[180px]">Client</th>
                        <th class="w-[200px]">Equipment</th>
                        <th class="w-[110px]">Rate / Price</th>
                        <th class="w-[110px]">Requested</th>
                        <th class="w-[90px]">Status</th>
                        <th class="w-[120px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="eqJobReqTableBody">
                    @forelse($items as $request)
                    @php
                        $clientName = $request->client->name ?? 'N/A';
                        $eqName = $request->equipment->name ?? 'N/A';
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition eq-job-req-row"
                        data-search="{{ strtolower('#' . $request->id . ' ' . $clientName . ' ' . $eqName) }}">
                        
                        <td class="font-mono font-bold text-slate-800">
                            #{{ $request->id }}
                        </td>

                        <td>
                            <div class="font-semibold text-slate-900 truncate" title="{{ $clientName }}">
                                {{ $clientName }}
                            </div>
                        </td>

                        <td>
                            <div class="font-semibold text-slate-800 truncate" title="{{ $eqName }}">
                                {{ $eqName }}
                            </div>
                        </td>

                        <td class="font-bold text-slate-900">
                            रु {{ number_format($request->price ?? 0, 2) }}
                        </td>

                        <td class="text-slate-500 text-[11px]">
                            {{ $request->created_at ? $request->created_at->format('M d, Y') : '—' }}
                        </td>

                        <td>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border bg-amber-50 text-amber-700 border-amber-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Pending
                            </span>
                        </td>

                        <td class="text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <form action="{{ route('equipment.jobs.accept', $request->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-1 rounded-md bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-700 text-[10px] font-bold transition">
                                        Accept
                                    </button>
                                </form>
                                <form action="{{ route('equipment.jobs.reject', $request->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Reject this request?')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-1 rounded-md bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 text-[10px] font-bold transition">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-clipboard-list text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold text-slate-700">No pending job requests</span>
                                <span class="text-[11px] text-slate-400 mt-0.5">New equipment hire requests from clients will appear here.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyEqJobReqSearch" class="hidden">
                        <td colspan="7" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No job requests match your search</span>
                                <button type="button" onclick="clearEqJobReqSearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
                                    Clear search
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
                Showing <span id="eqJobReqRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="eqJobReqTotal" class="font-bold text-slate-800">{{ count($items) }}</span> requests
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="eqJobReqPagination">
                <button type="button" id="eqJobReqPrevBtn" onclick="prevEqJobReqPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="eqJobReqPageNumbers" class="flex items-center gap-1"></div>
                <button type="button" id="eqJobReqNextBtn" onclick="nextEqJobReqPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentEqJobReqPage = 1;
const EQ_JOB_REQ_PER_PAGE = 5;

function clearEqJobReqSearch() {
    const input = document.getElementById('eqJobReqSearchInput');
    if (input) input.value = '';
    applyEqJobReqFilter();
}

function applyEqJobReqFilter() {
    const searchVal = (document.getElementById('eqJobReqSearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('eqJobReqSearchClear');
    if (clearBtn) clearBtn.classList.toggle('visible', searchVal.length > 0);

    const rows = Array.from(document.querySelectorAll('.eq-job-req-row'));
    const matched = [];

    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        if (!searchVal || text.includes(searchVal)) {
            matched.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const empty = document.getElementById('emptyEqJobReqSearch');
    if (empty) empty.classList.toggle('hidden', matched.length > 0 || rows.length === 0);

    paginateEqJobReq(matched);
}

function paginateEqJobReq(matched) {
    const total = matched.length;
    const totalPages = Math.max(1, Math.ceil(total / EQ_JOB_REQ_PER_PAGE));
    if (currentEqJobReqPage > totalPages) currentEqJobReqPage = totalPages;
    if (currentEqJobReqPage < 1) currentEqJobReqPage = 1;

    matched.forEach((row, idx) => {
        const start = (currentEqJobReqPage - 1) * EQ_JOB_REQ_PER_PAGE;
        const end = start + EQ_JOB_REQ_PER_PAGE;
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    const startNum = total === 0 ? 0 : (currentEqJobReqPage - 1) * EQ_JOB_REQ_PER_PAGE + 1;
    const endNum = Math.min(total, currentEqJobReqPage * EQ_JOB_REQ_PER_PAGE);

    const rangeEl = document.getElementById('eqJobReqRange');
    const totalEl = document.getElementById('eqJobReqTotal');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('eqJobReqPrevBtn');
    const nextBtn = document.getElementById('eqJobReqNextBtn');
    if (prevBtn) prevBtn.disabled = currentEqJobReqPage <= 1;
    if (nextBtn) nextBtn.disabled = currentEqJobReqPage >= totalPages;

    const container = document.getElementById('eqJobReqPageNumbers');
    if (container) {
        container.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = i;
                btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
                    i === currentEqJobReqPage ? 'bg-orange-500 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`;
                btn.onclick = () => { currentEqJobReqPage = i; applyEqJobReqFilter(); };
                container.appendChild(btn);
            }
        }
    }
}

function prevEqJobReqPage() {
    if (currentEqJobReqPage > 1) {
        currentEqJobReqPage--;
        applyEqJobReqFilter();
    }
}

function nextEqJobReqPage() {
    currentEqJobReqPage++;
    applyEqJobReqFilter();
}

document.addEventListener('DOMContentLoaded', () => {
    applyEqJobReqFilter();
});
</script>
@endpush
@endsection