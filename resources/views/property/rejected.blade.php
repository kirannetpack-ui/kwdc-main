@extends('layouts.app')

@section('title', 'Rejected Properties')
@section('header', 'Rejected Properties')

@section('content')
@php
    $totalCount = $rejectedWarehouses instanceof \Illuminate\Pagination\AbstractPaginator ? $rejectedWarehouses->total() : count($rejectedWarehouses ?? []);
    $items = $rejectedWarehouses instanceof \Illuminate\Pagination\AbstractPaginator ? $rejectedWarehouses->items() : ($rejectedWarehouses ?? []);
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Rejected Properties</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200/80">
                <i class="fas fa-times-circle text-[9px]"></i>
                {{ $totalCount }} Rejected
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('property.approved') }}" 
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold transition border border-slate-200 shadow-2xs">
                <i class="fas fa-check-circle text-emerald-500 text-[10px]"></i>
                <span>Approved</span>
            </a>
            <a href="{{ route('property.pending') }}" 
               class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold transition border border-slate-200 shadow-2xs">
                <i class="fas fa-clock text-amber-500 text-[10px]"></i>
                <span>Pending</span>
            </a>
            <a href="{{ route('warehouses.create') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-plus text-[10px]"></i>
                <span>Register Facility</span>
            </a>
        </div>
    </div>

    <!-- Main Data Shell -->
    <div class="bg-white border border-slate-200/80 rounded-xl p-3 sm:p-3.5 shadow-2xs">
        <!-- Search Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
            <div class="text-xs font-semibold text-slate-600">
                Properties requiring modification or address verification
            </div>
            
            <div class="relative w-full sm:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="propRejectedSearchInput" 
                       placeholder="Search facility name, location..." 
                       oninput="applyPropRejectedFilter()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="propRejectedSearchClear" 
                        onclick="clearPropRejectedSearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Table with Zero Sideways Scroll -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="propRejectedTable">
                <thead>
                    <tr>
                        <th class="w-[60px]">#</th>
                        <th class="w-[230px]">Facility Name</th>
                        <th class="w-[180px]">Location</th>
                        <th class="w-[120px]">Area (sq ft)</th>
                        <th class="w-[120px]">Reviewed On</th>
                        <th class="w-[100px]">Status</th>
                        <th class="w-[90px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="propRejectedTableBody">
                    @forelse($items as $warehouse)
                    <tr class="hover:bg-slate-50/70 transition prop-rejected-row"
                        data-search="{{ strtolower($warehouse->name . ' ' . $warehouse->location . ' ' . ($warehouse->owner_name ?? '')) }}">
                        
                        <td class="font-mono text-slate-500 font-semibold">
                            {{ $loop->iteration }}
                        </td>

                        <td>
                            <strong class="text-slate-900 font-semibold block truncate" title="{{ $warehouse->name }}">{{ $warehouse->name }}</strong>
                            <span class="text-[10px] text-slate-500">
                                Owner: {{ $warehouse->owner_name ?? 'N/A' }}
                            </span>
                        </td>

                        <td>
                            <div class="truncate text-slate-600" title="{{ $warehouse->location }}">
                                <i class="fas fa-map-marker-alt text-orange-400 text-[10px] me-1"></i>
                                {{ \Illuminate\Support\Str::limit($warehouse->location, 30) }}
                            </div>
                        </td>

                        <td class="font-semibold text-slate-800">
                            {{ $warehouse->area_sqft ? number_format((float) $warehouse->area_sqft) . ' sq ft' : 'N/A' }}
                        </td>

                        <td class="text-slate-500 text-[11px]">
                            {{ $warehouse->updated_at ? $warehouse->updated_at->format('M d, Y') : '—' }}
                        </td>

                        <td>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border bg-rose-50 text-rose-700 border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                Rejected
                            </span>
                        </td>

                        <td class="text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <a href="{{ route('property.warehouses.show', $warehouse->id) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 transition"
                                   title="View Facility">
                                    <i class="fas fa-eye text-[11px]"></i>
                                </a>
                                <a href="{{ route('property.warehouses.edit', $warehouse->id) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-amber-50 hover:bg-amber-600 hover:text-white text-amber-700 transition"
                                   title="Edit Facility">
                                    <i class="fas fa-edit text-[11px]"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-check-circle text-2xl text-emerald-500 mb-1.5"></i>
                                <span class="text-xs font-semibold text-slate-700">No rejected properties</span>
                                <span class="text-[11px] text-slate-400 mt-0.5">All your registered properties have been approved or are pending.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyRejectedSearch" class="hidden">
                        <td colspan="7" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No rejected facilities match your search</span>
                                <button type="button" onclick="clearPropRejectedSearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
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
                Showing <span id="rejectedRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="rejectedTotal" class="font-bold text-slate-800">{{ count($items) }}</span> facilities
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="rejectedPagination">
                <button type="button" id="rejectedPrevBtn" onclick="prevRejectedPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="rejectedPageNumbers" class="flex items-center gap-1"></div>
                <button type="button" id="rejectedNextBtn" onclick="nextRejectedPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentRejectedPage = 1;
const REJECTED_PER_PAGE = 5;

function clearPropRejectedSearch() {
    const input = document.getElementById('propRejectedSearchInput');
    if (input) input.value = '';
    applyPropRejectedFilter();
}

function applyPropRejectedFilter() {
    const searchVal = (document.getElementById('propRejectedSearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('propRejectedSearchClear');
    if (clearBtn) clearBtn.classList.toggle('visible', searchVal.length > 0);

    const rows = Array.from(document.querySelectorAll('.prop-rejected-row'));
    const matched = [];

    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        if (!searchVal || text.includes(searchVal)) {
            matched.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const empty = document.getElementById('emptyRejectedSearch');
    if (empty) empty.classList.toggle('hidden', matched.length > 0 || rows.length === 0);

    paginateRejected(matched);
}

function paginateRejected(matched) {
    const total = matched.length;
    const totalPages = Math.max(1, Math.ceil(total / REJECTED_PER_PAGE));
    if (currentRejectedPage > totalPages) currentRejectedPage = totalPages;
    if (currentRejectedPage < 1) currentRejectedPage = 1;

    matched.forEach((row, idx) => {
        const start = (currentRejectedPage - 1) * REJECTED_PER_PAGE;
        const end = start + REJECTED_PER_PAGE;
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    const startNum = total === 0 ? 0 : (currentRejectedPage - 1) * REJECTED_PER_PAGE + 1;
    const endNum = Math.min(total, currentRejectedPage * REJECTED_PER_PAGE);

    const rangeEl = document.getElementById('rejectedRange');
    const totalEl = document.getElementById('rejectedTotal');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('rejectedPrevBtn');
    const nextBtn = document.getElementById('rejectedNextBtn');
    if (prevBtn) prevBtn.disabled = currentRejectedPage <= 1;
    if (nextBtn) nextBtn.disabled = currentRejectedPage >= totalPages;

    const container = document.getElementById('rejectedPageNumbers');
    if (container) {
        container.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = i;
                btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
                    i === currentRejectedPage ? 'bg-orange-500 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`;
                btn.onclick = () => { currentRejectedPage = i; applyPropRejectedFilter(); };
                container.appendChild(btn);
            }
        }
    }
}

function prevRejectedPage() {
    if (currentRejectedPage > 1) {
        currentRejectedPage--;
        applyPropRejectedFilter();
    }
}

function nextRejectedPage() {
    currentRejectedPage++;
    applyPropRejectedFilter();
}

document.addEventListener('DOMContentLoaded', () => {
    applyPropRejectedFilter();
});
</script>
@endpush
@endsection