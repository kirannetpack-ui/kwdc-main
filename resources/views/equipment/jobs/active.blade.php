@extends('layouts.app')

@section('title', 'Active Equipment Jobs')
@section('header', 'Active Jobs')

@section('content')
@php
    $totalCount = isset($activeJobs) && $activeJobs instanceof \Illuminate\Pagination\AbstractPaginator ? $activeJobs->total() : (isset($activeJobs) ? count($activeJobs) : 0);
    $items = isset($activeJobs) && $activeJobs instanceof \Illuminate\Pagination\AbstractPaginator ? $activeJobs->items() : ($activeJobs ?? []);
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Active Jobs</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200/80">
                <i class="fas fa-play-circle text-[9px]"></i>
                {{ $totalCount }} Active Jobs
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('equipment.jobs.requests') }}" 
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold transition border border-slate-200 shadow-2xs">
                <i class="fas fa-clipboard-list text-amber-500 text-[9px]"></i>
                <span>Pending Requests</span>
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
                Live equipment deployment jobs currently in operation
            </div>
            
            <div class="relative w-full sm:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="eqActiveSearchInput" 
                       placeholder="Search equipment, client, location..." 
                       oninput="applyEqActiveFilter()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="eqActiveSearchClear" 
                        onclick="clearEqActiveSearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Table with Zero Sideways Scroll -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="eqActiveTable">
                <thead>
                    <tr>
                        <th class="w-[180px]">Equipment</th>
                        <th class="w-[150px]">Client</th>
                        <th class="w-[150px]">Period</th>
                        <th class="w-[110px]">Status</th>
                        <th class="w-[150px]">Location</th>
                        <th class="w-[100px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="eqActiveTableBody">
                    @forelse($items as $job)
                    @php
                        $eq = $job->equipment->name ?? 'N/A';
                        $cl = $job->client->name ?? 'N/A';
                        $loc = $job->location ?? 'Site';
                        $status = strtolower($job->status ?? 'accepted');
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition eq-active-row"
                        data-search="{{ strtolower($eq . ' ' . $cl . ' ' . $loc . ' ' . $status) }}">
                        
                        <!-- Equipment -->
                        <td>
                            <div class="font-bold text-slate-900 truncate" title="{{ $eq }}">
                                {{ $eq }}
                            </div>
                            <div class="text-[10px] text-slate-500 truncate">
                                {{ $job->equipment->type ?? '' }}
                            </div>
                        </td>

                        <!-- Client -->
                        <td>
                            <div class="font-semibold text-slate-800 truncate" title="{{ $cl }}">
                                {{ $cl }}
                            </div>
                            <div class="text-[10px] text-slate-500 font-mono">
                                {{ $job->client->phone ?? '' }}
                            </div>
                        </td>

                        <!-- Period -->
                        <td class="text-slate-600 text-[11px]">
                            {{ \Carbon\Carbon::parse($job->start_date)->format('M d') }} &rarr; {{ \Carbon\Carbon::parse($job->end_date)->format('M d, Y') }}
                        </td>

                        <!-- Status -->
                        <td>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $status === 'in_progress' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-purple-50 text-purple-700 border-purple-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status === 'in_progress' ? 'bg-blue-500' : 'bg-purple-500' }}"></span>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </td>

                        <!-- Location -->
                        <td>
                            <div class="truncate text-slate-600" title="{{ $loc }}">
                                <i class="fas fa-map-marker-alt text-orange-400 text-[10px] me-1"></i>
                                {{ \Illuminate\Support\Str::limit($loc, 24) }}
                            </div>
                        </td>

                        <!-- Actions -->
                        <td class="text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <a href="{{ route('equipment.jobs.show', $job->id) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 transition" 
                                   title="View Details">
                                    <i class="fas fa-eye text-[11px]"></i>
                                </a>
                                @if($job->status == 'accepted')
                                <form action="{{ route('equipment.jobs.start', $job->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-600 transition" 
                                            title="Mark In Progress"
                                            onclick="return confirm('Mark this job as in progress?')">
                                        <i class="fas fa-play text-[10px]"></i>
                                    </button>
                                </form>
                                @endif
                                @if($job->status == 'in_progress')
                                <form action="{{ route('equipment.jobs.complete', $job->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-600 transition" 
                                            title="Mark Completed"
                                            onclick="return confirm('Mark this job as completed?')">
                                        <i class="fas fa-check text-[10px]"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-play-circle text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold text-slate-700">No active jobs in progress</span>
                                <span class="text-[11px] text-slate-400 mt-0.5">When you accept client rental inquiries, they will appear here.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyEqActiveSearch" class="hidden">
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No active jobs match your search</span>
                                <button type="button" onclick="clearEqActiveSearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
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
                Showing <span id="eqActiveRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="eqActiveTotal" class="font-bold text-slate-800">{{ count($items) }}</span> jobs
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="eqActivePagination">
                <button type="button" id="eqActivePrevBtn" onclick="prevEqActivePage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="eqActivePageNumbers" class="flex items-center gap-1"></div>
                <button type="button" id="eqActiveNextBtn" onclick="nextEqActivePage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentEqActivePage = 1;
const EQ_ACTIVE_PER_PAGE = 5;

function clearEqActiveSearch() {
    const input = document.getElementById('eqActiveSearchInput');
    if (input) input.value = '';
    applyEqActiveFilter();
}

function applyEqActiveFilter() {
    const searchVal = (document.getElementById('eqActiveSearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('eqActiveSearchClear');
    if (clearBtn) clearBtn.classList.toggle('visible', searchVal.length > 0);

    const rows = Array.from(document.querySelectorAll('.eq-active-row'));
    const matched = [];

    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        if (!searchVal || text.includes(searchVal)) {
            matched.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const empty = document.getElementById('emptyEqActiveSearch');
    if (empty) empty.classList.toggle('hidden', matched.length > 0 || rows.length === 0);

    paginateEqActive(matched);
}

function paginateEqActive(matched) {
    const total = matched.length;
    const totalPages = Math.max(1, Math.ceil(total / EQ_ACTIVE_PER_PAGE));
    if (currentEqActivePage > totalPages) currentEqActivePage = totalPages;
    if (currentEqActivePage < 1) currentEqActivePage = 1;

    matched.forEach((row, idx) => {
        const start = (currentEqActivePage - 1) * EQ_ACTIVE_PER_PAGE;
        const end = start + EQ_ACTIVE_PER_PAGE;
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    const startNum = total === 0 ? 0 : (currentEqActivePage - 1) * EQ_ACTIVE_PER_PAGE + 1;
    const endNum = Math.min(total, currentEqActivePage * EQ_ACTIVE_PER_PAGE);

    const rangeEl = document.getElementById('eqActiveRange');
    const totalEl = document.getElementById('eqActiveTotal');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('eqActivePrevBtn');
    const nextBtn = document.getElementById('eqActiveNextBtn');
    if (prevBtn) prevBtn.disabled = currentEqActivePage <= 1;
    if (nextBtn) nextBtn.disabled = currentEqActivePage >= totalPages;

    const container = document.getElementById('eqActivePageNumbers');
    if (container) {
        container.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = i;
                btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
                    i === currentEqActivePage ? 'bg-orange-500 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`;
                btn.onclick = () => { currentEqActivePage = i; applyEqActiveFilter(); };
                container.appendChild(btn);
            }
        }
    }
}

function prevEqActivePage() {
    if (currentEqActivePage > 1) {
        currentEqActivePage--;
        applyEqActiveFilter();
    }
}

function nextEqActivePage() {
    currentEqActivePage++;
    applyEqActiveFilter();
}

document.addEventListener('DOMContentLoaded', () => {
    applyEqActiveFilter();
});
</script>
@endpush
@endsection