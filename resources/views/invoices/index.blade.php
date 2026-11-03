@extends('layouts.app')

@section('title', 'My Invoices')
@section('header', 'My Invoices')

@section('content')
@php
    $totalCount = $invoices instanceof \Illuminate\Pagination\AbstractPaginator ? $invoices->total() : count($invoices ?? []);
    $items = $invoices instanceof \Illuminate\Pagination\AbstractPaginator ? $invoices->items() : ($invoices ?? []);
    $paidCount = collect($items)->where('status', 'paid')->count();
    $pendingCount = collect($items)->where('status', 'pending')->count();
    $overdueCount = collect($items)->where('status', 'overdue')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">My Invoices</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-file-invoice-dollar text-[9px]"></i>
                {{ $totalCount }} Invoices
            </span>
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
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="myInvoiceStatusTabs">
                <button type="button" 
                        onclick="setMyInvStatusFilter('all')" 
                        data-myinvfilter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ count($items) }}</span>
                </button>
                @if($paidCount > 0)
                <button type="button" 
                        onclick="setMyInvStatusFilter('paid')" 
                        data-myinvfilter="paid"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Paid</span>
                    <span class="tab-count-badge">{{ $paidCount }}</span>
                </button>
                @endif
                @if($pendingCount > 0)
                <button type="button" 
                        onclick="setMyInvStatusFilter('pending')" 
                        data-myinvfilter="pending"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Pending</span>
                    <span class="tab-count-badge">{{ $pendingCount }}</span>
                </button>
                @endif
                @if($overdueCount > 0)
                <button type="button" 
                        onclick="setMyInvStatusFilter('overdue')" 
                        data-myinvfilter="overdue"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                    <span>Overdue</span>
                    <span class="tab-count-badge">{{ $overdueCount }}</span>
                </button>
                @endif
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full lg:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="myInvoiceSearchInput" 
                       placeholder="Search invoice #, amount, date..." 
                       oninput="applyMyInvFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearMyInvSearchBtn" 
                        onclick="clearMyInvSearch()" 
                        class="hidden kwdc-search-clear-slot" 
                        title="Clear search">
                    <i class="fas fa-circle-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Table (Zero Sideways Scroll) -->
        <div class="overflow-hidden rounded-xl border border-slate-200/80 mt-2.5">
            <table class="kwdc-table-fixed text-left">
                <thead class="bg-slate-50/95 border-b border-slate-200/80">
                    <tr>
                        <th class="w-[24%]">Invoice #</th>
                        <th class="w-[24%]">Amount</th>
                        <th class="w-[22%]">Due Date</th>
                        <th class="w-[18%]">Status</th>
                        <th class="w-[12%] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="myInvoicesTableBody">
                    @forelse($items as $invoice)
                    @php
                        $amount = (float) ($invoice->grand_total ?? $invoice->amount ?? $invoice->total_amount ?? 0);
                        $invNum = $invoice->invoice_number ?? ('INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT));
                        $status = strtolower($invoice->status ?? 'pending');
                        $dueDateCarbon = $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date) : ($invoice->created_at ?? null);
                        $dueDateStr = $dueDateCarbon ? $dueDateCarbon->format('M d, Y') : '—';
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $invoice->id,
                            '#' . $invoice->id,
                            $invNum,
                            $amount,
                            $status,
                            $dueDateStr
                        ])));
                    @endphp
                    <tr class="my-inv-row hover:bg-slate-50/80 transition-colors"
                        data-status="{{ $status }}"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- Invoice # -->
                        <td>
                            <div class="flex items-center gap-1.5 min-w-0" title="{{ $invNum }}">
                                <i class="fas fa-file-invoice text-slate-400 text-[10px] shrink-0"></i>
                                <span class="font-bold text-slate-900 text-xs font-mono truncate">{{ $invNum }}</span>
                            </div>
                        </td>

                        <!-- Amount -->
                        <td>
                            <span class="font-extrabold text-slate-900 text-xs truncate block">
                                रू {{ number_format($amount, 2) }}
                            </span>
                        </td>

                        <!-- Due Date -->
                        <td>
                            <span class="text-slate-600 text-xs truncate block" title="{{ $dueDateStr }}">
                                {{ $dueDateStr }}
                            </span>
                        </td>

                        <!-- Status Badge -->
                        <td>
                            @php
                                $badgeStyles = match($status) {
                                    'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
                                    'overdue' => 'bg-rose-50 text-rose-700 border-rose-200/80',
                                    'pending' => 'bg-amber-50 text-amber-700 border-amber-200/80',
                                    default => 'bg-slate-100 text-slate-700 border-slate-200'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeStyles }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="text-right">
                            <a href="{{ route('invoices.show', $invoice->id) }}" 
                               class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-[10.5px] font-semibold transition border border-slate-200 shadow-2xs group"
                               title="View Invoice Details">
                                <span>View</span>
                                <i class="fas fa-arrow-right text-[8px] opacity-60 group-hover:translate-x-0.5 transition-transform"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyMyInvRow">
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-file-invoice text-2xl text-slate-300 block mb-1"></i>
                            No invoices found.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noMyInvMatchesRow" class="hidden">
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching invoices</span>
                            <button type="button" onclick="resetMyInvFilters()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
                                Clear filters
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Sleek Bottom Pager Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2.5 mt-1 border-t border-slate-100 text-xs text-slate-500">
            <!-- Count Readout -->
            <div class="flex items-center gap-3">
                <span id="filteredMyInvCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ count($items) }} invoices
                </span>
                <button type="button" 
                        id="resetMyInvFiltersBtn" 
                        onclick="resetMyInvFilters()" 
                        class="hidden text-orange-600 hover:underline font-semibold text-xs">
                    Reset
                </button>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setMyInvPageSize(5)" data-myinvsize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setMyInvPageSize(10)" data-myinvsize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setMyInvPageSize('all')" data-myinvsize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="myInvPaginationControls">
                </div>
            </div>
        </div>

        @if($invoices instanceof \Illuminate\Pagination\AbstractPaginator && $invoices->hasPages())
        <div class="mt-2.5 pt-2 border-t border-slate-100 flex justify-end">
            {{ $invoices->links() }}
        </div>
        @endif

    </div>
</div>

<script>
let currentMyInvStatusFilter = 'all';
let currentMyInvPage = 1;
let myInvPageSize = 5;
let filteredMyInvRows = [];

function setMyInvStatusFilter(status) {
    currentMyInvStatusFilter = status;
    currentMyInvPage = 1;

    document.querySelectorAll('#myInvoiceStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.myinvfilter === status) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    applyMyInvFilters();
}

function clearMyInvSearch() {
    const input = document.getElementById('myInvoiceSearchInput');
    input.value = '';
    document.getElementById('clearMyInvSearchBtn').classList.add('hidden');
    currentMyInvPage = 1;
    applyMyInvFilters();
    input.focus();
}

function resetMyInvFilters() {
    document.getElementById('myInvoiceSearchInput').value = '';
    document.getElementById('clearMyInvSearchBtn').classList.add('hidden');
    currentMyInvStatusFilter = 'all';
    document.querySelectorAll('#myInvoiceStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.myinvfilter === 'all') tab.classList.add('active');
        else tab.classList.remove('active');
    });
    currentMyInvPage = 1;
    applyMyInvFilters();
}

function setMyInvPageSize(size) {
    myInvPageSize = (size === 'all') ? 9999 : parseInt(size);
    currentMyInvPage = 1;
    
    document.querySelectorAll('[data-myinvsize]').forEach(btn => {
        if (btn.dataset.myinvsize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderMyInvPagination();
}

function goToMyInvPage(page) {
    currentMyInvPage = page;
    renderMyInvPagination();
}

function applyMyInvFilters() {
    const input = document.getElementById('myInvoiceSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearMyInvSearchBtn');
    const resetBtn = document.getElementById('resetMyInvFiltersBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const isFiltered = (currentMyInvStatusFilter !== 'all' || query.length > 0);
    if (resetBtn) {
        if (isFiltered) resetBtn.classList.remove('hidden');
        else resetBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#myInvoicesTableBody .my-inv-row'));
    filteredMyInvRows = [];

    rows.forEach(row => {
        const rowStatus = row.dataset.status || '';
        const rowSearch = row.dataset.search || '';

        let statusMatches = (currentMyInvStatusFilter === 'all' || rowStatus === currentMyInvStatusFilter);
        let queryMatches = (!query || rowSearch.includes(query));

        if (statusMatches && queryMatches) {
            filteredMyInvRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderMyInvPagination();
}

function renderMyInvPagination() {
    const totalCount = filteredMyInvRows.length;
    const effectivePageSize = myInvPageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentMyInvPage > totalPages) currentMyInvPage = 1;
    if (currentMyInvPage < 1) currentMyInvPage = 1;

    const startIndex = (currentMyInvPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#myInvoicesTableBody .my-inv-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredMyInvRows[i]) {
            filteredMyInvRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredMyInvCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching invoices';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} invoices`;
        }
    }

    const noMatchesRow = document.getElementById('noMyInvMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('myInvPaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToMyInvPage(${currentMyInvPage - 1})" 
                ${currentMyInvPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentMyInvPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToMyInvPage(${p})" 
                    class="pager-btn ${p === currentMyInvPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToMyInvPage(${currentMyInvPage + 1})" 
                ${currentMyInvPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentMyInvPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    applyMyInvFilters();
});
if (document.readyState !== 'loading') {
    applyMyInvFilters();
}
</script>
@endsection