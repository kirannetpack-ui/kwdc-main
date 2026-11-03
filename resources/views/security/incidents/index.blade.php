@extends('layouts.security')

@section('title', 'Security Incidents')
@section('header', 'Security Incidents')

@section('content')
@php
    $totalCount = $incidents instanceof \Illuminate\Pagination\AbstractPaginator ? $incidents->total() : count($incidents ?? []);
    $items = $incidents instanceof \Illuminate\Pagination\AbstractPaginator ? $incidents->items() : ($incidents ?? []);
    $openCount = collect($items)->whereIn('status', ['open', 'pending', 'investigating'])->count();
    $resolvedCount = collect($items)->whereIn('status', ['resolved', 'closed'])->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Security Incidents</h2>
            @if(isset($agency) && $agency->agency_name)
                <span class="text-xs font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">{{ $agency->agency_name }}</span>
            @endif
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200/80">
                <i class="fas fa-triangle-exclamation text-[9px]"></i>
                {{ $totalCount }} Incidents
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('security.dashboard') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold border border-slate-200 shadow-2xs transition">
                <i class="fas fa-shield-halved text-[10px]"></i>
                <span>Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Main Data Shell -->
    <div class="bg-white border border-slate-200/80 rounded-xl p-3 sm:p-3.5 shadow-2xs">
        <!-- Filter & Search Toolbar -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
            <!-- Status Tabs -->
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="incidentsStatusTabs">
                <button type="button" onclick="setIncFilter('all')" data-incfilter="all" class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ count($items) }}</span>
                </button>
                @if($openCount > 0)
                <button type="button" onclick="setIncFilter('open')" data-incfilter="open" class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                    <span>Open / Under Review</span>
                    <span class="tab-count-badge">{{ $openCount }}</span>
                </button>
                @endif
                @if($resolvedCount > 0)
                <button type="button" onclick="setIncFilter('resolved')" data-incfilter="resolved" class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Resolved</span>
                    <span class="tab-count-badge">{{ $resolvedCount }}</span>
                </button>
                @endif
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full lg:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="incSearchInput" 
                       placeholder="Search category, warehouse..." 
                       oninput="applyIncFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="incSearchClear" 
                        onclick="clearIncSearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Table with Zero Sideways Scroll -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="incTable">
                <thead>
                    <tr>
                        <th class="w-[125px]">Reported Time</th>
                        <th class="w-[180px]">Warehouse</th>
                        <th class="w-[140px]">Category</th>
                        <th class="w-[100px]">Severity</th>
                        <th class="w-[110px]">Status</th>
                        <th class="w-[130px] text-right">Reported By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="incTableBody">
                    @forelse($items as $incident)
                    @php
                        $status = strtolower($incident->status ?? 'open');
                        $sev = strtolower($incident->severity ?? 'medium');
                        $wh = $incident->warehouse?->name ?? 'N/A';
                        $rep = $incident->reportedBy?->name ?? 'N/A';
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition inc-row"
                        data-status="{{ $status }}"
                        data-search="{{ strtolower(($incident->category ?? '') . ' ' . $wh . ' ' . $rep . ' ' . $sev . ' ' . $status) }}">
                        
                        <td class="font-mono text-slate-500 text-[11px]">
                            {{ optional($incident->incident_time)->format('d M Y, H:i') ?? '—' }}
                        </td>

                        <td>
                            <div class="font-semibold text-slate-900 truncate" title="{{ $wh }}">
                                {{ $wh }}
                            </div>
                        </td>

                        <td class="text-slate-700 truncate">
                            {{ $incident->category }}
                        </td>

                        <td>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold {{ $sev === 'high' || $sev === 'critical' ? 'bg-rose-100 text-rose-800' : ($sev === 'medium' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700') }}">
                                {{ ucfirst($incident->severity) }}
                            </span>
                        </td>

                        <td>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $status === 'resolved' || $status === 'closed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status === 'resolved' || $status === 'closed' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                {{ ucfirst($incident->status) }}
                            </span>
                        </td>

                        <td class="text-right text-slate-600 truncate" title="{{ $rep }}">
                            {{ $rep }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-shield-check text-2xl text-emerald-500 mb-1.5"></i>
                                <span class="text-xs font-semibold text-slate-700">No incidents reported</span>
                                <span class="text-[11px] text-slate-400 mt-0.5">All warehouse facilities have clean safety records.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyIncSearch" class="hidden">
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No incidents match your search</span>
                                <button type="button" onclick="clearIncSearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
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
                Showing <span id="incRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="incTotal" class="font-bold text-slate-800">{{ count($items) }}</span> incidents
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="incPagination">
                <button type="button" id="incPrevBtn" onclick="prevIncPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="incPageNumbers" class="flex items-center gap-1"></div>
                <button type="button" id="incNextBtn" onclick="nextIncPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentIncFilter = 'all';
let currentIncPage = 1;
const INC_PER_PAGE = 5;

function setIncFilter(status) {
    currentIncFilter = status;
    currentIncPage = 1;

    document.querySelectorAll('#incidentsStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-incfilter') === status) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    applyIncFilters();
}

function clearIncSearch() {
    const input = document.getElementById('incSearchInput');
    if (input) input.value = '';
    currentIncFilter = 'all';
    document.querySelectorAll('#incidentsStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-incfilter') === 'all') {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    applyIncFilters();
}

function applyIncFilters() {
    const searchVal = (document.getElementById('incSearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('incSearchClear');
    if (clearBtn) clearBtn.classList.toggle('visible', searchVal.length > 0);

    const rows = Array.from(document.querySelectorAll('.inc-row'));
    const matched = [];

    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status') || '';
        const searchData = row.getAttribute('data-search') || '';

        let matchesStatus = (currentIncFilter === 'all');
        if (currentIncFilter === 'open') {
            matchesStatus = (rowStatus === 'open' || rowStatus === 'pending' || rowStatus === 'investigating');
        } else if (currentIncFilter === 'resolved') {
            matchesStatus = (rowStatus === 'resolved' || rowStatus === 'closed');
        }

        const matchesSearch = !searchVal || searchData.includes(searchVal);

        if (matchesStatus && matchesSearch) {
            matched.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const empty = document.getElementById('emptyIncSearch');
    if (empty) empty.classList.toggle('hidden', matched.length > 0 || rows.length === 0);

    paginateInc(matched);
}

function paginateInc(matched) {
    const total = matched.length;
    const totalPages = Math.max(1, Math.ceil(total / INC_PER_PAGE));
    if (currentIncPage > totalPages) currentIncPage = totalPages;
    if (currentIncPage < 1) currentIncPage = 1;

    matched.forEach((row, idx) => {
        const start = (currentIncPage - 1) * INC_PER_PAGE;
        const end = start + INC_PER_PAGE;
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    const startNum = total === 0 ? 0 : (currentIncPage - 1) * INC_PER_PAGE + 1;
    const endNum = Math.min(total, currentIncPage * INC_PER_PAGE);

    const rangeEl = document.getElementById('incRange');
    const totalEl = document.getElementById('incTotal');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('incPrevBtn');
    const nextBtn = document.getElementById('incNextBtn');
    if (prevBtn) prevBtn.disabled = currentIncPage <= 1;
    if (nextBtn) nextBtn.disabled = currentIncPage >= totalPages;

    const container = document.getElementById('incPageNumbers');
    if (container) {
        container.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = i;
                btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
                    i === currentIncPage ? 'bg-orange-500 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`;
                btn.onclick = () => { currentIncPage = i; applyIncFilters(); };
                container.appendChild(btn);
            }
        }
    }
}

function prevIncPage() {
    if (currentIncPage > 1) {
        currentIncPage--;
        applyIncFilters();
    }
}

function nextIncPage() {
    currentIncPage++;
    applyIncFilters();
}

document.addEventListener('DOMContentLoaded', () => {
    applyIncFilters();
});
</script>
@endpush
@endsection
