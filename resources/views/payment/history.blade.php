@extends('layouts.app')

@section('title', 'Payment History')
@section('header', 'Payment History')

@section('content')
@php
    $totalCount = $transactions instanceof \Illuminate\Pagination\AbstractPaginator ? $transactions->total() : count($transactions ?? []);
    $items = $transactions instanceof \Illuminate\Pagination\AbstractPaginator ? $transactions->items() : ($transactions ?? []);
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Payment History</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-receipt text-[9px]"></i>
                {{ $totalCount }} Transactions
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('invoices.index') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold border border-slate-200 shadow-2xs transition">
                <i class="fas fa-file-invoice text-[10px]"></i>
                <span>View Invoices</span>
            </a>
        </div>
    </div>

    <!-- Main Data Shell -->
    <div class="bg-white border border-slate-200/80 rounded-xl p-3 sm:p-3.5 shadow-2xs">
        <!-- Search Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
            <div class="text-xs font-semibold text-slate-600">
                Settled disbursements, escrow transactions, and billing receipts
            </div>
            
            <div class="relative w-full sm:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="paySearchInput" 
                       placeholder="Search reference, invoice, method..." 
                       oninput="applyPayFilter()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="paySearchClear" 
                        onclick="clearPaySearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Table with Zero Sideways Scroll -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="payTable">
                <thead>
                    <tr>
                        <th class="w-[110px]">Date</th>
                        <th class="w-[150px]">Reference</th>
                        <th class="w-[150px]">Invoice</th>
                        <th class="w-[110px]">Method</th>
                        <th class="w-[130px]">Amount</th>
                        <th class="w-[100px] text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="payTableBody">
                    @forelse($items as $transaction)
                    @php
                        $ref = $transaction->receipt_no ?? $transaction->transaction_id ?? '#'.$transaction->id;
                        $inv = $transaction->invoice?->invoice_number ?? 'Unavailable';
                        $method = ucfirst($transaction->payment_method ?? 'Other');
                        $status = strtolower($transaction->status ?? 'completed');
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition pay-row"
                        data-search="{{ strtolower($ref . ' ' . $inv . ' ' . $method . ' ' . $status) }}">
                        
                        <td class="text-slate-500 font-mono text-[11px]">
                            {{ ($transaction->payment_date ?? $transaction->created_at)?->format('M j, Y') ?? '—' }}
                        </td>

                        <td class="font-mono font-bold text-slate-800 truncate" title="{{ $ref }}">
                            {{ $ref }}
                        </td>

                        <td class="text-slate-700 truncate" title="{{ $inv }}">
                            {{ $inv }}
                        </td>

                        <td class="text-slate-600">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-semibold">
                                <i class="fas fa-credit-card text-[9px] text-slate-400"></i>
                                {{ $method }}
                            </span>
                        </td>

                        <td class="font-bold text-slate-900 whitespace-nowrap">
                            NPR {{ number_format($transaction->amount, 2) }}
                        </td>

                        <td class="text-right">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $status === 'completed' || $status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status === 'completed' || $status === 'paid' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-receipt text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold text-slate-700">No payments recorded yet</span>
                                <span class="text-[11px] text-slate-400 mt-0.5">Payments for storage, hauling, and logistics will be cataloged here.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyPaySearch" class="hidden">
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No payments match your search</span>
                                <button type="button" onclick="clearPaySearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
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
                Showing <span id="payRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="payTotal" class="font-bold text-slate-800">{{ count($items) }}</span> transactions
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="payPagination">
                <button type="button" id="payPrevBtn" onclick="prevPayPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="payPageNumbers" class="flex items-center gap-1"></div>
                <button type="button" id="payNextBtn" onclick="nextPayPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentPayPage = 1;
const PAY_PER_PAGE = 5;

function clearPaySearch() {
    const input = document.getElementById('paySearchInput');
    if (input) input.value = '';
    applyPayFilter();
}

function applyPayFilter() {
    const searchVal = (document.getElementById('paySearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('paySearchClear');
    if (clearBtn) clearBtn.classList.toggle('visible', searchVal.length > 0);

    const rows = Array.from(document.querySelectorAll('.pay-row'));
    const matched = [];

    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        if (!searchVal || text.includes(searchVal)) {
            matched.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const empty = document.getElementById('emptyPaySearch');
    if (empty) empty.classList.toggle('hidden', matched.length > 0 || rows.length === 0);

    paginatePay(matched);
}

function paginatePay(matched) {
    const total = matched.length;
    const totalPages = Math.max(1, Math.ceil(total / PAY_PER_PAGE));
    if (currentPayPage > totalPages) currentPayPage = totalPages;
    if (currentPayPage < 1) currentPayPage = 1;

    matched.forEach((row, idx) => {
        const start = (currentPayPage - 1) * PAY_PER_PAGE;
        const end = start + PAY_PER_PAGE;
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    const startNum = total === 0 ? 0 : (currentPayPage - 1) * PAY_PER_PAGE + 1;
    const endNum = Math.min(total, currentPayPage * PAY_PER_PAGE);

    const rangeEl = document.getElementById('payRange');
    const totalEl = document.getElementById('payTotal');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('payPrevBtn');
    const nextBtn = document.getElementById('payNextBtn');
    if (prevBtn) prevBtn.disabled = currentPayPage <= 1;
    if (nextBtn) nextBtn.disabled = currentPayPage >= totalPages;

    const container = document.getElementById('payPageNumbers');
    if (container) {
        container.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = i;
                btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
                    i === currentPayPage ? 'bg-orange-500 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`;
                btn.onclick = () => { currentPayPage = i; applyPayFilter(); };
                container.appendChild(btn);
            }
        }
    }
}

function prevPayPage() {
    if (currentPayPage > 1) {
        currentPayPage--;
        applyPayFilter();
    }
}

function nextPayPage() {
    currentPayPage++;
    applyPayFilter();
}

document.addEventListener('DOMContentLoaded', () => {
    applyPayFilter();
});
</script>
@endpush
@endsection
