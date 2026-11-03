@extends('layouts.security')

@section('title', 'My Goods')
@section('header', 'Security Goods')

@section('content')
@php
    $totalCount = $goods instanceof \Illuminate\Pagination\AbstractPaginator ? $goods->total() : count($goods ?? []);
    $items = $goods instanceof \Illuminate\Pagination\AbstractPaginator ? $goods->items() : ($goods ?? []);
    $availableCount = collect($items)->where('status', 'available')->count();
    $rentedCount = collect($items)->where('status', 'rented')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Security Goods</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-boxes-stacked text-[9px]"></i>
                {{ $totalCount }} Equipment / Items
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('security.goods.create') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-plus text-[10px]"></i>
                <span>Add Item</span>
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
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="goodsStatusTabs">
                <button type="button" onclick="setGoodsFilter('all')" data-goodfilter="all" class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ count($items) }}</span>
                </button>
                <button type="button" onclick="setGoodsFilter('available')" data-goodfilter="available" class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Available</span>
                    <span class="tab-count-badge">{{ $availableCount }}</span>
                </button>
                @if($rentedCount > 0)
                <button type="button" onclick="setGoodsFilter('rented')" data-goodfilter="rented" class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Rented</span>
                    <span class="tab-count-badge">{{ $rentedCount }}</span>
                </button>
                @endif
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full lg:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="goodsSearchInput" 
                       placeholder="Search item, category..." 
                       oninput="applyGoodsFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="goodsSearchClear" 
                        onclick="clearGoodsSearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Table with Zero Sideways Scroll -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="goodsTable">
                <thead>
                    <tr>
                        <th class="w-[50px]">#</th>
                        <th class="w-[180px]">Item Name</th>
                        <th class="w-[140px]">Category</th>
                        <th class="w-[90px]">Qty</th>
                        <th class="w-[110px]">Unit Price</th>
                        <th class="w-[100px]">Status</th>
                        <th class="w-[110px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="goodsTableBody">
                    @forelse($items as $good)
                    @php
                        $status = strtolower($good->status ?? 'available');
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition good-row"
                        data-status="{{ $status }}"
                        data-search="{{ strtolower(($good->item_name ?? '') . ' ' . ($good->category ?? '') . ' ' . $status) }}">
                        
                        <td class="font-mono text-slate-500 font-semibold">
                            {{ $loop->iteration }}
                        </td>

                        <td>
                            <div class="font-bold text-slate-900 truncate" title="{{ $good->item_name }}">
                                {{ $good->item_name }}
                            </div>
                        </td>

                        <td class="text-slate-600 truncate">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-semibold">
                                {{ $good->category }}
                            </span>
                        </td>

                        <td class="font-semibold text-slate-800">
                            {{ $good->quantity_available }}
                        </td>

                        <td class="font-semibold text-slate-900">
                            ${{ number_format($good->unit_price, 2) }}
                        </td>

                        <td>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $status === 'available' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($status === 'rented' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status === 'available' ? 'bg-emerald-500' : ($status === 'rented' ? 'bg-amber-500' : 'bg-rose-500') }}"></span>
                                {{ ucfirst($status) }}
                            </span>
                        </td>

                        <td class="text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <a href="{{ route('security.goods.show', $good) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 transition" 
                                   title="View Item">
                                    <i class="fas fa-eye text-[11px]"></i>
                                </a>
                                <a href="{{ route('security.goods.edit', $good) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 transition" 
                                   title="Edit Item">
                                    <i class="fas fa-edit text-[11px]"></i>
                                </a>
                                <form action="{{ route('security.goods.destroy', $good) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this item?')">
                                    @csrf 
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-600 transition" 
                                            title="Delete Item">
                                        <i class="fas fa-trash text-[11px]"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-boxes-stacked text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold text-slate-700">No goods registered yet</span>
                                <a href="{{ route('security.goods.create') }}" class="mt-2 text-xs text-orange-600 font-bold hover:underline">
                                    Add your first item &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyGoodsSearch" class="hidden">
                        <td colspan="7" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No items match your search</span>
                                <button type="button" onclick="clearGoodsSearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
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
                Showing <span id="goodsRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="goodsTotal" class="font-bold text-slate-800">{{ count($items) }}</span> items
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="goodsPagination">
                <button type="button" id="goodsPrevBtn" onclick="prevGoodsPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="goodsPageNumbers" class="flex items-center gap-1"></div>
                <button type="button" id="goodsNextBtn" onclick="nextGoodsPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentGoodFilter = 'all';
let currentGoodPage = 1;
const GOOD_PER_PAGE = 5;

function setGoodsFilter(status) {
    currentGoodFilter = status;
    currentGoodPage = 1;

    document.querySelectorAll('#goodsStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-goodfilter') === status) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    applyGoodsFilters();
}

function clearGoodsSearch() {
    const input = document.getElementById('goodsSearchInput');
    if (input) input.value = '';
    currentGoodFilter = 'all';
    document.querySelectorAll('#goodsStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-goodfilter') === 'all') {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    applyGoodsFilters();
}

function applyGoodsFilters() {
    const searchVal = (document.getElementById('goodsSearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('goodsSearchClear');
    if (clearBtn) clearBtn.classList.toggle('visible', searchVal.length > 0);

    const rows = Array.from(document.querySelectorAll('.good-row'));
    const matched = [];

    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status') || '';
        const searchData = row.getAttribute('data-search') || '';

        const matchesStatus = (currentGoodFilter === 'all') || (rowStatus === currentGoodFilter);
        const matchesSearch = !searchVal || searchData.includes(searchVal);

        if (matchesStatus && matchesSearch) {
            matched.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const empty = document.getElementById('emptyGoodsSearch');
    if (empty) empty.classList.toggle('hidden', matched.length > 0 || rows.length === 0);

    paginateGoods(matched);
}

function paginateGoods(matched) {
    const total = matched.length;
    const totalPages = Math.max(1, Math.ceil(total / GOOD_PER_PAGE));
    if (currentGoodPage > totalPages) currentGoodPage = totalPages;
    if (currentGoodPage < 1) currentGoodPage = 1;

    matched.forEach((row, idx) => {
        const start = (currentGoodPage - 1) * GOOD_PER_PAGE;
        const end = start + GOOD_PER_PAGE;
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    const startNum = total === 0 ? 0 : (currentGoodPage - 1) * GOOD_PER_PAGE + 1;
    const endNum = Math.min(total, currentGoodPage * GOOD_PER_PAGE);

    const rangeEl = document.getElementById('goodsRange');
    const totalEl = document.getElementById('goodsTotal');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('goodsPrevBtn');
    const nextBtn = document.getElementById('goodsNextBtn');
    if (prevBtn) prevBtn.disabled = currentGoodPage <= 1;
    if (nextBtn) nextBtn.disabled = currentGoodPage >= totalPages;

    const container = document.getElementById('goodsPageNumbers');
    if (container) {
        container.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = i;
                btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
                    i === currentGoodPage ? 'bg-orange-500 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`;
                btn.onclick = () => { currentGoodPage = i; applyGoodsFilters(); };
                container.appendChild(btn);
            }
        }
    }
}

function prevGoodsPage() {
    if (currentGoodPage > 1) {
        currentGoodPage--;
        applyGoodsFilters();
    }
}

function nextGoodsPage() {
    currentGoodPage++;
    applyGoodsFilters();
}

document.addEventListener('DOMContentLoaded', () => {
    applyGoodsFilters();
});
</script>
@endpush
@endsection