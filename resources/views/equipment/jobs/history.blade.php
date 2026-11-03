@extends('layouts.app')

@section('title', 'Equipment Jobs History')
@section('header', 'Equipment Jobs History')

@section('content')
@php
    $totalCount = isset($history) && $history instanceof \Illuminate\Pagination\AbstractPaginator ? $history->total() : (isset($history) ? count($history) : 0);
    $items = isset($history) && $history instanceof \Illuminate\Pagination\AbstractPaginator ? $history->items() : ($history ?? []);
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Jobs History</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                <i class="fas fa-history text-[9px]"></i>
                {{ $totalCount }} Completed
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('equipment.jobs.requests') }}" 
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold transition border border-slate-200 shadow-2xs">
                <i class="fas fa-clipboard-list text-amber-500 text-[9px]"></i>
                <span>Pending Requests</span>
            </a>
            <a href="{{ route('equipment.jobs.active') }}" 
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold transition border border-slate-200 shadow-2xs">
                <i class="fas fa-play text-blue-500 text-[9px]"></i>
                <span>Active Jobs</span>
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
                Archived logs and completed contracts for fleet machinery
            </div>
            
            <div class="relative w-full sm:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="eqHistorySearchInput" 
                       placeholder="Search equipment, client, ID..." 
                       oninput="applyEqHistoryFilter()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="eqHistorySearchClear" 
                        onclick="clearEqHistorySearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Table with Zero Sideways Scroll -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="eqHistoryTable">
                <thead>
                    <tr>
                        <th class="w-[70px]">ID</th>
                        <th class="w-[200px]">Client</th>
                        <th class="w-[220px]">Equipment</th>
                        <th class="w-[120px]">Contract Fee</th>
                        <th class="w-[130px]">Completed On</th>
                        <th class="w-[90px] text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="eqHistoryTableBody">
                    @forelse($items as $job)
                    @php
                        $cl = $job->client->name ?? 'N/A';
                        $eq = $job->equipment->name ?? 'N/A';
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition eq-hist-row"
                        data-search="{{ strtolower('#' . $job->id . ' ' . $cl . ' ' . $eq) }}">
                        
                        <td class="font-mono font-bold text-slate-800">
                            #{{ $job->id }}
                        </td>

                        <td>
                            <div class="font-semibold text-slate-900 truncate" title="{{ $cl }}">
                                {{ $cl }}
                            </div>
                        </td>

                        <td>
                            <div class="font-semibold text-slate-800 truncate" title="{{ $eq }}">
                                {{ $eq }}
                            </div>
                        </td>

                        <td class="font-bold text-slate-900">
                            रु {{ number_format($job->price ?? 0, 2) }}
                        </td>

                        <td class="text-slate-500 text-[11px]">
                            {{ $job->completed_at ? $job->completed_at->format('M d, Y H:i') : '—' }}
                        </td>

                        <td class="text-right">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border bg-emerald-50 text-emerald-700 border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                {{ ucfirst($job->status ?? 'completed') }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-history text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold text-slate-700">No completed jobs yet</span>
                                <span class="text-[11px] text-slate-400 mt-0.5">Completed equipment rentals and services will be archived here.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyEqHistSearch" class="hidden">
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No jobs match your search</span>
                                <button type="button" onclick="clearEqHistorySearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
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
                Showing <span id="eqHistRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="eqHistTotal" class="font-bold text-slate-800">{{ count($items) }}</span> jobs
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="eqHistPagination">
                <button type="button" id="eqHistPrevBtn" onclick="prevEqHistPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="eqHistPageNumbers" class="flex items-center gap-1"></div>
                <button type="button" id="eqHistNextBtn" onclick="nextEqHistPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentEqHistPage = 1;
const EQ_HIST_PER_PAGE = 5;

function clearEqHistorySearch() {
    const input = document.getElementById('eqHistorySearchInput');
    if (input) input.value = '';
    applyEqHistoryFilter();
}

function applyEqHistoryFilter() {
    const searchVal = (document.getElementById('eqHistorySearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('eqHistorySearchClear');
    if (clearBtn) clearBtn.classList.toggle('visible', searchVal.length > 0);

    const rows = Array.from(document.querySelectorAll('.eq-hist-row'));
    const matched = [];

    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        if (!searchVal || text.includes(searchVal)) {
            matched.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const empty = document.getElementById('emptyEqHistSearch');
    if (empty) empty.classList.toggle('hidden', matched.length > 0 || rows.length === 0);

    paginateEqHist(matched);
}

function paginateEqHist(matched) {
    const total = matched.length;
    const totalPages = Math.max(1, Math.ceil(total / EQ_HIST_PER_PAGE));
    if (currentEqHistPage > totalPages) currentEqHistPage = totalPages;
    if (currentEqHistPage < 1) currentEqHistPage = 1;

    matched.forEach((row, idx) => {
        const start = (currentEqHistPage - 1) * EQ_HIST_PER_PAGE;
        const end = start + EQ_HIST_PER_PAGE;
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    const startNum = total === 0 ? 0 : (currentEqHistPage - 1) * EQ_HIST_PER_PAGE + 1;
    const endNum = Math.min(total, currentEqHistPage * EQ_HIST_PER_PAGE);

    const rangeEl = document.getElementById('eqHistRange');
    const totalEl = document.getElementById('eqHistTotal');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('eqHistPrevBtn');
    const nextBtn = document.getElementById('eqHistNextBtn');
    if (prevBtn) prevBtn.disabled = currentEqHistPage <= 1;
    if (nextBtn) nextBtn.disabled = currentEqHistPage >= totalPages;

    const container = document.getElementById('eqHistPageNumbers');
    if (container) {
        container.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = i;
                btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
                    i === currentEqHistPage ? 'bg-orange-500 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`;
                btn.onclick = () => { currentEqHistPage = i; applyEqHistoryFilter(); };
                container.appendChild(btn);
            }
        }
    }
}

function prevEqHistPage() {
    if (currentEqHistPage > 1) {
        currentEqHistPage--;
        applyEqHistoryFilter();
    }
}

function nextEqHistPage() {
    currentEqHistPage++;
    applyEqHistoryFilter();
}

document.addEventListener('DOMContentLoaded', () => {
    applyEqHistoryFilter();
});
</script>
@endpush
@endsection