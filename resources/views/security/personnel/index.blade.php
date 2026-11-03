@extends('layouts.security')

@section('title', 'Security Personnel')
@section('header', 'Security Personnel')

@section('content')
@php
    $totalCount = $personnel instanceof \Illuminate\Pagination\AbstractPaginator ? $personnel->total() : count($personnel ?? []);
    $items = $personnel instanceof \Illuminate\Pagination\AbstractPaginator ? $personnel->items() : ($personnel ?? []);
    $activeCount = collect($items)->where('status', 'active')->count();
    $inactiveCount = collect($items)->where('status', 'inactive')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Security Personnel</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-200/80">
                <i class="fas fa-user-shield text-[9px]"></i>
                {{ $totalCount }} Guards
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('security.personnel.create') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-plus text-[10px]"></i>
                <span>Add Personnel</span>
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
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="guardStatusTabs">
                <button type="button" onclick="setGuardFilter('all')" data-gfilter="all" class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ count($items) }}</span>
                </button>
                <button type="button" onclick="setGuardFilter('active')" data-gfilter="active" class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Active</span>
                    <span class="tab-count-badge">{{ $activeCount }}</span>
                </button>
                @if($inactiveCount > 0)
                <button type="button" onclick="setGuardFilter('inactive')" data-gfilter="inactive" class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400 shrink-0"></span>
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
                       id="guardSearchInput" 
                       placeholder="Search guard name, phone..." 
                       oninput="applyGuardFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="guardSearchClear" 
                        onclick="clearGuardSearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Table with Zero Sideways Scroll -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="guardTable">
                <thead>
                    <tr>
                        <th class="w-[60px]">#</th>
                        <th class="w-[200px]">Officer Name</th>
                        <th class="w-[170px]">Position / Role</th>
                        <th class="w-[140px]">Contact Phone</th>
                        <th class="w-[100px]">Status</th>
                        <th class="w-[100px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="guardTableBody">
                    @forelse($items as $p)
                    @php
                        $status = strtolower($p->status ?? 'active');
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition guard-row"
                        data-status="{{ $status }}"
                        data-search="{{ strtolower(($p->name ?? '') . ' ' . ($p->position ?? '') . ' ' . ($p->phone ?? '') . ' ' . $status) }}">
                        
                        <td class="font-mono text-slate-500 font-semibold">
                            {{ $loop->iteration }}
                        </td>

                        <td>
                            <div class="font-bold text-slate-900 truncate" title="{{ $p->name }}">
                                {{ $p->name }}
                            </div>
                        </td>

                        <td class="text-slate-600 truncate">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-semibold">
                                <i class="fas fa-id-badge text-[9px] text-slate-400"></i>
                                {{ $p->position }}
                            </span>
                        </td>

                        <td class="font-mono text-slate-600">
                            {{ $p->phone ?: '—' }}
                        </td>

                        <td>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status === 'active' ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                {{ ucfirst($status) }}
                            </span>
                        </td>

                        <td class="text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <a href="{{ route('security.personnel.edit', $p) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 transition" 
                                   title="Edit Officer">
                                    <i class="fas fa-edit text-[11px]"></i>
                                </a>
                                <form action="{{ route('security.personnel.destroy', $p) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this officer record?')">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-600 transition" 
                                            title="Delete Officer">
                                        <i class="fas fa-trash text-[11px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-user-shield text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold text-slate-700">No personnel registered yet</span>
                                <a href="{{ route('security.personnel.create') }}" class="mt-2 text-xs text-orange-600 font-bold hover:underline">
                                    Add your first guard &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyGuardSearch" class="hidden">
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No personnel match your search</span>
                                <button type="button" onclick="clearGuardSearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
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
                Showing <span id="guardRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="guardTotal" class="font-bold text-slate-800">{{ count($items) }}</span> officers
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="guardPagination">
                <button type="button" id="guardPrevBtn" onclick="prevGuardPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="guardPageNumbers" class="flex items-center gap-1"></div>
                <button type="button" id="guardNextBtn" onclick="nextGuardPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentGuardFilter = 'all';
let currentGuardPage = 1;
const GUARD_PER_PAGE = 5;

function setGuardFilter(status) {
    currentGuardFilter = status;
    currentGuardPage = 1;

    document.querySelectorAll('#guardStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-gfilter') === status) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    applyGuardFilters();
}

function clearGuardSearch() {
    const input = document.getElementById('guardSearchInput');
    if (input) input.value = '';
    currentGuardFilter = 'all';
    document.querySelectorAll('#guardStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-gfilter') === 'all') {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    applyGuardFilters();
}

function applyGuardFilters() {
    const searchVal = (document.getElementById('guardSearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('guardSearchClear');
    if (clearBtn) clearBtn.classList.toggle('visible', searchVal.length > 0);

    const rows = Array.from(document.querySelectorAll('.guard-row'));
    const matched = [];

    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status') || '';
        const searchData = row.getAttribute('data-search') || '';

        const matchesStatus = (currentGuardFilter === 'all') || (rowStatus === currentGuardFilter);
        const matchesSearch = !searchVal || searchData.includes(searchVal);

        if (matchesStatus && matchesSearch) {
            matched.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const empty = document.getElementById('emptyGuardSearch');
    if (empty) empty.classList.toggle('hidden', matched.length > 0 || rows.length === 0);

    paginateGuards(matched);
}

function paginateGuards(matched) {
    const total = matched.length;
    const totalPages = Math.max(1, Math.ceil(total / GUARD_PER_PAGE));
    if (currentGuardPage > totalPages) currentGuardPage = totalPages;
    if (currentGuardPage < 1) currentGuardPage = 1;

    matched.forEach((row, idx) => {
        const start = (currentGuardPage - 1) * GUARD_PER_PAGE;
        const end = start + GUARD_PER_PAGE;
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    const startNum = total === 0 ? 0 : (currentGuardPage - 1) * GUARD_PER_PAGE + 1;
    const endNum = Math.min(total, currentGuardPage * GUARD_PER_PAGE);

    const rangeEl = document.getElementById('guardRange');
    const totalEl = document.getElementById('guardTotal');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('guardPrevBtn');
    const nextBtn = document.getElementById('guardNextBtn');
    if (prevBtn) prevBtn.disabled = currentGuardPage <= 1;
    if (nextBtn) nextBtn.disabled = currentGuardPage >= totalPages;

    const container = document.getElementById('guardPageNumbers');
    if (container) {
        container.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = i;
                btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
                    i === currentGuardPage ? 'bg-orange-500 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`;
                btn.onclick = () => { currentGuardPage = i; applyGuardFilters(); };
                container.appendChild(btn);
            }
        }
    }
}

function prevGuardPage() {
    if (currentGuardPage > 1) {
        currentGuardPage--;
        applyGuardFilters();
    }
}

function nextGuardPage() {
    currentGuardPage++;
    applyGuardFilters();
}

document.addEventListener('DOMContentLoaded', () => {
    applyGuardFilters();
});
</script>
@endpush
@endsection