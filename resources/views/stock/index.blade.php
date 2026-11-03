@extends('layouts.app')

@section('title', 'Manage Stocks')
@section('header', 'Manage Stocks')

@section('content')
@php
    $totalCount = $stocks instanceof \Illuminate\Pagination\AbstractPaginator ? $stocks->total() : count($stocks ?? []);
    $items = $stocks instanceof \Illuminate\Pagination\AbstractPaginator ? $stocks->items() : ($stocks ?? []);
    $inStockCount = collect($items)->where('status', 'in_stock')->count();
    $partialCount = collect($items)->whereIn('status', ['partial', 'dispatched'])->count();
    $expiredCount = collect($items)->where('status', 'expired')->count();
    $totalRemainingQty = collect($items)->sum('remaining_quantity');
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Stock Inventory</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-boxes text-[9px]"></i>
                {{ $totalCount }} Batches
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('stock.create') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-plus text-[10px]"></i>
                <span>Add Stock</span>
            </a>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs px-3.5 py-2 rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-rose-50 border border-rose-200 text-rose-800 text-xs px-3.5 py-2 rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-circle-exclamation text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- Sleek KPI Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
        <div class="bg-white border border-slate-200/80 rounded-xl p-2.5 shadow-2xs">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Batches</div>
            <div class="text-lg font-black text-slate-900 mt-0.5">{{ $totalCount }}</div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-xl p-2.5 shadow-2xs">
            <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">In Stock</div>
            <div class="text-lg font-black text-emerald-700 mt-0.5">{{ $inStockCount }}</div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-xl p-2.5 shadow-2xs">
            <div class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Partial / Transit</div>
            <div class="text-lg font-black text-amber-700 mt-0.5">{{ $partialCount }}</div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-xl p-2.5 shadow-2xs">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Remaining Qty</div>
            <div class="text-lg font-black text-slate-900 mt-0.5">{{ number_format($totalRemainingQty) }}</div>
        </div>
    </div>

    <!-- Main Data Shell -->
    <div class="bg-white border border-slate-200/80 rounded-xl p-3 sm:p-3.5 shadow-2xs">
        
        <!-- Filter & Search Toolbar -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
            <!-- Status Tabs -->
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="stockStatusTabs">
                <button type="button" 
                        onclick="setStockFilter('all')" 
                        data-sfilter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ count($items) }}</span>
                </button>
                <button type="button" 
                        onclick="setStockFilter('in_stock')" 
                        data-sfilter="in_stock"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>In Stock</span>
                    <span class="tab-count-badge">{{ $inStockCount }}</span>
                </button>
                @if($partialCount > 0)
                <button type="button" 
                        onclick="setStockFilter('partial')" 
                        data-sfilter="partial"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Partial / Out</span>
                    <span class="tab-count-badge">{{ $partialCount }}</span>
                </button>
                @endif
                @if($expiredCount > 0)
                <button type="button" 
                        onclick="setStockFilter('expired')" 
                        data-sfilter="expired"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                    <span>Expired</span>
                    <span class="tab-count-badge">{{ $expiredCount }}</span>
                </button>
                @endif
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full lg:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="stockSearchInput" 
                       placeholder="Search product, batch, SKU..." 
                       oninput="applyStockFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="stockSearchClear" 
                        onclick="clearStockSearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Zero Horizontal Scroll Table Frame -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="stocksTable">
                <thead>
                    <tr>
                        <th class="w-[125px]">Batch / SKU</th>
                        <th class="w-[190px]">Product</th>
                        <th class="w-[100px]">Boxes</th>
                        <th class="w-[110px]">Stock Qty</th>
                        <th class="w-[105px]">Status</th>
                        <th class="w-[65px] text-center">QR</th>
                        <th class="w-[95px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="stocksTableBody">
                    @forelse($items as $stock)
                    <tr class="hover:bg-slate-50/70 transition stock-row-item"
                        data-product="{{ strtolower($stock->product_name ?? '') }}"
                        data-batch="{{ strtolower($stock->batch_id ?? '') }}"
                        data-sku="{{ strtolower($stock->sku ?? '') }}"
                        data-status="{{ strtolower($stock->status ?? 'in_stock') }}"
                        data-search="{{ strtolower(($stock->product_name ?? '') . ' ' . ($stock->batch_id ?? '') . ' ' . ($stock->sku ?? '') . ' ' . ($stock->status ?? '')) }}">
                        
                        <!-- Batch / SKU -->
                        <td>
                            <div class="font-mono font-bold text-slate-800 truncate" title="{{ $stock->batch_id }}">
                                {{ $stock->batch_id }}
                            </div>
                            <div class="text-[10px] text-slate-500 font-mono truncate">
                                {{ $stock->sku ?: 'No SKU' }}
                            </div>
                        </td>

                        <!-- Product -->
                        <td>
                            <div class="font-semibold text-slate-900 truncate" title="{{ $stock->product_name }}">
                                {{ $stock->product_name }}
                            </div>
                            <div class="text-[10px] text-slate-500">
                                Unit: {{ $stock->unit ?: 'Piece' }}
                            </div>
                        </td>

                        <!-- Boxes -->
                        <td class="text-slate-600">
                            {{ $stock->number_of_boxes }} × {{ $stock->quantity_per_box }}
                        </td>

                        <!-- Stock Qty -->
                        <td>
                            <div class="font-bold text-slate-800">
                                {{ number_format($stock->remaining_quantity) }} <span class="text-[10px] font-normal text-slate-500">rem</span>
                            </div>
                            <div class="text-[10px] text-slate-400">
                                of {{ number_format($stock->total_quantity) }}
                            </div>
                        </td>

                        <!-- Status Badge -->
                        <td>
                            @php
                                $status = strtolower($stock->status ?? 'in_stock');
                                $badgeCls = match($status) {
                                    'in_stock' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'partial' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'dispatched' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'expired' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $badgeCls }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status === 'in_stock' ? 'bg-emerald-500' : ($status === 'expired' ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                            </span>
                        </td>

                        <!-- QR Code -->
                        <td class="text-center">
                            <button type="button" 
                                    onclick="showQRModal('{{ $stock->id }}', '{{ $stock->batch_id }}')" 
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-orange-50 hover:bg-orange-500 hover:text-white text-orange-600 transition" 
                                    title="View QR Code">
                                <i class="fas fa-qrcode text-[12px]"></i>
                            </button>
                        </td>

                        <!-- Actions -->
                        <td class="text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <a href="{{ route('stock.show', $stock->id) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 transition" 
                                   title="View Details">
                                    <i class="fas fa-eye text-[10px]"></i>
                                </a>
                                <a href="{{ route('stock.download-qr', $stock->id) }}" 
                                   class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-600 transition" 
                                   title="Download QR">
                                    <i class="fas fa-download text-[10px]"></i>
                                </a>
                                <button type="button" 
                                        onclick="copyBatchInfo('{{ $stock->batch_id }}', '{{ $stock->product_name }}', '{{ $stock->remaining_quantity }}')" 
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 transition" 
                                        title="Copy Details">
                                    <i class="fas fa-copy text-[10px]"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-box-open text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold text-slate-700">No stock batches found</span>
                                <a href="{{ route('stock.create') }}" class="mt-2 text-xs text-orange-600 font-bold hover:underline">
                                    Add your first stock batch &rarr;
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyStockClient" class="hidden">
                        <td colspan="7" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No stock batches match your filters</span>
                                <button type="button" onclick="clearStockSearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
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
                Showing <span id="stockRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="stockTotalVisible" class="font-bold text-slate-800">{{ count($items) }}</span> batches
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="stockPaginationControls">
                <button type="button" 
                        id="stockPrevBtn" 
                        onclick="prevStockPage()" 
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="stockPageNumbers" class="flex items-center gap-1"></div>
                <button type="button" 
                        id="stockNextBtn" 
                        onclick="nextStockPage()" 
                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div id="qrModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-5 text-center">
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-sm font-bold text-slate-900">Batch QR Code</h3>
            <button onclick="closeQRModal()" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <div id="qrCodeImage" class="mb-3 flex justify-center p-3 bg-slate-50 rounded-xl border border-slate-100">
            <!-- QR image -->
        </div>
        <p id="qrBatchId" class="text-xs font-mono font-bold text-slate-700 mb-4"></p>
        <div class="flex items-center justify-center gap-2">
            <a id="downloadQrBtn" href="#" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold transition">
                <i class="fas fa-download text-[10px]"></i>
                <span>Download</span>
            </a>
            <button onclick="closeQRModal()" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">
                Close
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentStockFilter = 'all';
let currentStockPage = 1;
const STOCK_PER_PAGE = 5;

function setStockFilter(status) {
    currentStockFilter = status;
    currentStockPage = 1;

    document.querySelectorAll('#stockStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-sfilter') === status) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    applyStockFilters();
}

function clearStockSearch() {
    const input = document.getElementById('stockSearchInput');
    if (input) input.value = '';
    currentStockFilter = 'all';
    document.querySelectorAll('#stockStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-sfilter') === 'all') {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    applyStockFilters();
}

function applyStockFilters() {
    const searchVal = (document.getElementById('stockSearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('stockSearchClear');
    if (clearBtn) clearBtn.classList.toggle('visible', searchVal.length > 0);

    const rows = Array.from(document.querySelectorAll('.stock-row-item'));
    const matched = [];

    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status') || '';
        const searchData = row.getAttribute('data-search') || '';

        let matchesStatus = (currentStockFilter === 'all');
        if (currentStockFilter === 'in_stock') {
            matchesStatus = (rowStatus === 'in_stock');
        } else if (currentStockFilter === 'partial') {
            matchesStatus = (rowStatus === 'partial' || rowStatus === 'dispatched');
        } else if (currentStockFilter === 'expired') {
            matchesStatus = (rowStatus === 'expired');
        }

        const matchesSearch = !searchVal || searchData.includes(searchVal);

        if (matchesStatus && matchesSearch) {
            matched.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const emptyClient = document.getElementById('emptyStockClient');
    if (emptyClient) {
        emptyClient.classList.toggle('hidden', matched.length > 0 || rows.length === 0);
    }

    paginateStock(matched);
}

function paginateStock(matched) {
    const total = matched.length;
    const totalPages = Math.max(1, Math.ceil(total / STOCK_PER_PAGE));

    if (currentStockPage > totalPages) currentStockPage = totalPages;
    if (currentStockPage < 1) currentStockPage = 1;

    matched.forEach((row, idx) => {
        const start = (currentStockPage - 1) * STOCK_PER_PAGE;
        const end = start + STOCK_PER_PAGE;
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    const startNum = total === 0 ? 0 : (currentStockPage - 1) * STOCK_PER_PAGE + 1;
    const endNum = Math.min(total, currentStockPage * STOCK_PER_PAGE);

    const rangeEl = document.getElementById('stockRange');
    const totalEl = document.getElementById('stockTotalVisible');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('stockPrevBtn');
    const nextBtn = document.getElementById('stockNextBtn');
    if (prevBtn) prevBtn.disabled = currentStockPage <= 1;
    if (nextBtn) nextBtn.disabled = currentStockPage >= totalPages;

    renderStockPageNumbers(totalPages);
}

function renderStockPageNumbers(totalPages) {
    const container = document.getElementById('stockPageNumbers');
    if (!container) return;
    container.innerHTML = '';

    if (totalPages <= 1) return;

    for (let i = 1; i <= totalPages; i++) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = i;
        btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
            i === currentStockPage
                ? 'bg-orange-500 text-white shadow-xs'
                : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
        }`;
        btn.onclick = () => {
            currentStockPage = i;
            applyStockFilters();
        };
        container.appendChild(btn);
    }
}

function prevStockPage() {
    if (currentStockPage > 1) {
        currentStockPage--;
        applyStockFilters();
    }
}

function nextStockPage() {
    currentStockPage++;
    applyStockFilters();
}

// QR Code Modal
function showQRModal(stockId, batchId) {
    const modal = document.getElementById('qrModal');
    const qrImage = document.getElementById('qrCodeImage');
    const qrBatchId = document.getElementById('qrBatchId');
    const downloadBtn = document.getElementById('downloadQrBtn');
    
    const qrUrl = `/stock/${stockId}/qr-code`;
    qrImage.innerHTML = `<img src="${qrUrl}" alt="QR Code" class="w-36 h-36 mx-auto rounded-lg">`;
    qrBatchId.textContent = batchId;
    downloadBtn.href = `/stock/${stockId}/download-qr`;
    
    modal.classList.remove('hidden');
}

function closeQRModal() {
    document.getElementById('qrModal').classList.add('hidden');
}

function copyBatchInfo(batchId, productName, quantity) {
    const text = `Batch ID: ${batchId}\nProduct: ${productName}\nRemaining: ${quantity}`;
    navigator.clipboard.writeText(text);
    
    const notification = document.createElement('div');
    notification.className = 'fixed bottom-4 right-4 bg-slate-900 text-white text-xs px-3.5 py-2 rounded-xl shadow-lg z-50 flex items-center gap-2';
    notification.innerHTML = '<i class="fas fa-check text-emerald-400"></i> Batch info copied!';
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 2000);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeQRModal();
});

document.addEventListener('DOMContentLoaded', () => {
    applyStockFilters();
});
</script>
@endpush
@endsection