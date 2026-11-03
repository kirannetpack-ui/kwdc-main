@extends('layouts.security')

@section('title', 'Assignments')
@section('header', 'Security Assignments')

@section('content')
@php
    $totalCount = $assignments instanceof \Illuminate\Pagination\AbstractPaginator ? $assignments->total() : count($assignments ?? []);
    $items = $assignments instanceof \Illuminate\Pagination\AbstractPaginator ? $assignments->items() : ($assignments ?? []);
    $activeCount = collect($items)->where('status', 'active')->count();
    $completedCount = collect($items)->where('status', 'completed')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Security Assignments</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-600 border border-blue-200/80">
                <i class="fas fa-clipboard-user text-[9px]"></i>
                {{ $totalCount }} Shifts
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('security.assignments.create') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-plus text-[10px]"></i>
                <span>Create Assignment</span>
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
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="assignStatusTabs">
                <button type="button" onclick="setAssignFilter('all')" data-asfilter="all" class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ count($items) }}</span>
                </button>
                <button type="button" onclick="setAssignFilter('active')" data-asfilter="active" class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Active</span>
                    <span class="tab-count-badge">{{ $activeCount }}</span>
                </button>
                @if($completedCount > 0)
                <button type="button" onclick="setAssignFilter('completed')" data-asfilter="completed" class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 shrink-0"></span>
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
                       id="assignSearchInput" 
                       placeholder="Search warehouse, officer, shift..." 
                       oninput="applyAssignFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="assignSearchClear" 
                        onclick="clearAssignSearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Table with Zero Sideways Scroll -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="assignTable">
                <thead>
                    <tr>
                        <th class="w-[50px]">#</th>
                        <th class="w-[180px]">Warehouse</th>
                        <th class="w-[160px]">Guard Assigned</th>
                        <th class="w-[130px]">Deployment</th>
                        <th class="w-[90px]">Shift</th>
                        <th class="w-[95px]">Status</th>
                        <th class="w-[95px]">Total</th>
                        <th class="w-[95px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="assignTableBody">
                    @forelse($items as $assignment)
                    @php
                        $status = strtolower($assignment->status ?? 'active');
                        $wh = $assignment->warehouse->name ?? 'N/A';
                        $officer = $assignment->personnel->name ?? 'Unassigned';
                        $shift = ucfirst(str_replace('_', ' ', $assignment->shift ?? 'day'));
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition assign-row"
                        data-status="{{ $status }}"
                        data-search="{{ strtolower($wh . ' ' . $officer . ' ' . $shift . ' ' . $status) }}">
                        
                        <td class="font-mono text-slate-500 font-semibold">
                            {{ $loop->iteration }}
                        </td>

                        <td>
                            <div class="font-bold text-slate-900 truncate" title="{{ $wh }}">
                                {{ $wh }}
                            </div>
                        </td>

                        <td class="text-slate-700 truncate" title="{{ $officer }}">
                            <div class="flex items-center gap-1">
                                <i class="fas fa-user-shield text-slate-400 text-[10px]"></i>
                                <span class="font-semibold">{{ $officer }}</span>
                            </div>
                        </td>

                        <td class="text-slate-500 text-[11px]">
                            {{ $assignment->start_date }}
                            @if($assignment->end_date)
                                &rarr; {{ $assignment->end_date }}
                            @endif
                        </td>

                        <td>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-semibold">
                                {{ $shift }}
                            </span>
                        </td>

                        <td>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($status === 'completed' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-rose-50 text-rose-700 border-rose-200') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status === 'active' ? 'bg-emerald-500' : ($status === 'completed' ? 'bg-blue-500' : 'bg-rose-500') }}"></span>
                                {{ ucfirst($status) }}
                            </span>
                        </td>

                        <td class="font-semibold text-slate-900">
                            ${{ number_format($assignment->total_cost ?? 0, 2) }}
                        </td>

                        <td class="text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <a href="{{ route('security.assignments.show', $assignment) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 transition" 
                                   title="View Assignment">
                                    <i class="fas fa-eye text-[11px]"></i>
                                </a>
                                <a href="{{ route('security.assignments.edit', $assignment) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 transition" 
                                   title="Edit Assignment">
                                    <i class="fas fa-edit text-[11px]"></i>
                                </a>
                                <form action="{{ route('security.assignments.destroy', $assignment) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this assignment?')">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-600 transition" 
                                            title="Delete Assignment">
                                        <i class="fas fa-trash text-[11px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-clipboard-user text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold text-slate-700">No assignments created yet</span>
                                <a href="{{ route('security.assignments.create') }}" class="mt-2 text-xs text-orange-600 font-bold hover:underline">
                                    Create your first assignment &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyAssignSearch" class="hidden">
                        <td colspan="8" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No assignments match your search</span>
                                <button type="button" onclick="clearAssignSearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
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
                Showing <span id="assignRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="assignTotal" class="font-bold text-slate-800">{{ count($items) }}</span> assignments
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="assignPagination">
                <button type="button" id="assignPrevBtn" onclick="prevAssignPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="assignPageNumbers" class="flex items-center gap-1"></div>
                <button type="button" id="assignNextBtn" onclick="nextAssignPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentAssignFilter = 'all';
let currentAssignPage = 1;
const ASSIGN_PER_PAGE = 5;

function setAssignFilter(status) {
    currentAssignFilter = status;
    currentAssignPage = 1;

    document.querySelectorAll('#assignStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-asfilter') === status) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    applyAssignFilters();
}

function clearAssignSearch() {
    const input = document.getElementById('assignSearchInput');
    if (input) input.value = '';
    currentAssignFilter = 'all';
    document.querySelectorAll('#assignStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-asfilter') === 'all') {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    applyAssignFilters();
}

function applyAssignFilters() {
    const searchVal = (document.getElementById('assignSearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('assignSearchClear');
    if (clearBtn) clearBtn.classList.toggle('visible', searchVal.length > 0);

    const rows = Array.from(document.querySelectorAll('.assign-row'));
    const matched = [];

    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status') || '';
        const searchData = row.getAttribute('data-search') || '';

        const matchesStatus = (currentAssignFilter === 'all') || (rowStatus === currentAssignFilter);
        const matchesSearch = !searchVal || searchData.includes(searchVal);

        if (matchesStatus && matchesSearch) {
            matched.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const empty = document.getElementById('emptyAssignSearch');
    if (empty) empty.classList.toggle('hidden', matched.length > 0 || rows.length === 0);

    paginateAssign(matched);
}

function paginateAssign(matched) {
    const total = matched.length;
    const totalPages = Math.max(1, Math.ceil(total / ASSIGN_PER_PAGE));
    if (currentAssignPage > totalPages) currentAssignPage = totalPages;
    if (currentAssignPage < 1) currentAssignPage = 1;

    matched.forEach((row, idx) => {
        const start = (currentAssignPage - 1) * ASSIGN_PER_PAGE;
        const end = start + ASSIGN_PER_PAGE;
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    const startNum = total === 0 ? 0 : (currentAssignPage - 1) * ASSIGN_PER_PAGE + 1;
    const endNum = Math.min(total, currentAssignPage * ASSIGN_PER_PAGE);

    const rangeEl = document.getElementById('assignRange');
    const totalEl = document.getElementById('assignTotal');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('assignPrevBtn');
    const nextBtn = document.getElementById('assignNextBtn');
    if (prevBtn) prevBtn.disabled = currentAssignPage <= 1;
    if (nextBtn) nextBtn.disabled = currentAssignPage >= totalPages;

    const container = document.getElementById('assignPageNumbers');
    if (container) {
        container.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = i;
                btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
                    i === currentAssignPage ? 'bg-orange-500 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`;
                btn.onclick = () => { currentAssignPage = i; applyAssignFilters(); };
                container.appendChild(btn);
            }
        }
    }
}

function prevAssignPage() {
    if (currentAssignPage > 1) {
        currentAssignPage--;
        applyAssignFilters();
    }
}

function nextAssignPage() {
    currentAssignPage++;
    applyAssignFilters();
}

document.addEventListener('DOMContentLoaded', () => {
    applyAssignFilters();
});
</script>
@endpush
@endsection