@extends('layouts.app')

@section('title', 'All Invoices')
@section('header', 'Invoice Management')

@section('content')
@php
    $totalCount = count($invoices ?? []);
    $paidCount = collect($invoices ?? [])->where('status', 'paid')->count();
    $pendingCount = collect($invoices ?? [])->where('status', 'pending')->count();
    $overdueCount = collect($invoices ?? [])->where('status', 'overdue')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">All Invoices</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-file-invoice-dollar text-[9px]"></i>
                {{ $totalCount }} Invoices
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.invoices.export-schedule-13') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100 transition shadow-2xs">
                <i class="fas fa-file-excel text-emerald-600"></i> IRD अनुसूची १३ (Sales Book)
            </a>
            <a href="{{ route('admin.payments.bank-transfers') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                <i class="fas fa-building-columns"></i> Bank & QR Review
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
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="invoiceStatusTabs">
                <button type="button" 
                        onclick="setInvoiceStatusFilter('all')" 
                        data-invfilter="all"
                        class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ $totalCount }}</span>
                </button>
                @if($paidCount > 0)
                <button type="button" 
                        onclick="setInvoiceStatusFilter('paid')" 
                        data-invfilter="paid"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Paid</span>
                    <span class="tab-count-badge">{{ $paidCount }}</span>
                </button>
                @endif
                @if($pendingCount > 0)
                <button type="button" 
                        onclick="setInvoiceStatusFilter('pending')" 
                        data-invfilter="pending"
                        class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Pending</span>
                    <span class="tab-count-badge">{{ $pendingCount }}</span>
                </button>
                @endif
                @if($overdueCount > 0)
                <button type="button" 
                        onclick="setInvoiceStatusFilter('overdue')" 
                        data-invfilter="overdue"
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
                       id="invoiceSearchInput" 
                       placeholder="Search invoice #, client, amount..." 
                       oninput="applyInvoiceFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearInvoiceSearchBtn" 
                        onclick="clearInvoiceSearch()" 
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
                        <th class="w-[20%]">Invoice #</th>
                        <th class="w-[26%]">Client</th>
                        <th class="w-[18%]">Amount</th>
                        <th class="w-[14%]">Status</th>
                        <th class="w-[12%]">Date</th>
                        <th class="w-[10%] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="invoicesTableBody">
                    @forelse($invoices ?? [] as $invoice)
                    @php
                        $invNum = $invoice->invoice_number ?? ('INV-' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT));
                        $clientName = $invoice->client->name ?? 'Direct Client';
                        $status = strtolower($invoice->status ?? 'pending');
                        $amount = (float)($invoice->amount ?? 0);
                        $dateStr = $invoice->created_at ? $invoice->created_at->format('M d, Y') : '-';
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $invoice->id,
                            '#' . $invoice->id,
                            $invNum,
                            $clientName,
                            $amount,
                            $status,
                            $dateStr
                        ])));
                        $showRoute = \Route::has('admin.invoices.show') ? route('admin.invoices.show', $invoice->id) : route('invoices.show', $invoice->id);
                    @endphp
                    <tr class="invoice-row hover:bg-slate-50/80 transition-colors"
                        data-status="{{ $status }}"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- Invoice # -->
                        <td>
                            <div class="flex items-center gap-1.5 min-w-0" title="{{ $invNum }}">
                                <i class="fas fa-file-invoice text-slate-400 text-[10px] shrink-0"></i>
                                <span class="font-bold text-slate-900 text-xs font-mono truncate">{{ $invNum }}</span>
                            </div>
                        </td>

                        <!-- Client -->
                        <td>
                            <span class="font-semibold text-slate-800 text-xs truncate block" title="{{ $clientName }}">
                                {{ $clientName }}
                            </span>
                        </td>

                        <!-- Amount -->
                        <td>
                            <span class="font-extrabold text-slate-900 text-xs truncate block">
                                रू {{ number_format($amount, 2) }}
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
                            <div class="flex flex-col gap-1 items-start">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeStyles }}">
                                    {{ ucfirst($status) }}
                                </span>
                                @if($inv->isBanned())
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-red-100 text-red-700 border border-red-300" title="{{ $inv->ban_reason }}">
                                    <i class="fas fa-ban text-[8px]"></i> QR Revoked
                                </span>
                                @endif
                            </div>
                        </td>

                        <!-- Date -->
                        <td>
                            <span class="text-slate-500 text-xs truncate block" title="{{ $dateStr }}">
                                {{ $dateStr }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="text-right">
                            <div class="inline-flex items-center gap-1.5 justify-end">
                                <a href="{{ $showRoute }}" 
                                   class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-[10.5px] font-semibold transition border border-slate-200 shadow-2xs group"
                                   title="View Invoice">
                                    <span>View</span>
                                    <i class="fas fa-arrow-right text-[8px] opacity-60 group-hover:translate-x-0.5 transition-transform"></i>
                                </a>

                                @if($inv->isBanned())
                                <form method="POST" action="{{ route('admin.invoices.unban-qr', $inv->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-700 text-[10px] font-bold transition border border-emerald-300"
                                            title="Restore QR Code">
                                        <i class="fas fa-undo text-[8px]"></i> Restore QR
                                    </button>
                                </form>
                                @else
                                <button type="button" 
                                        onclick="promptBanInvoice({{ $inv->id }}, '{{ $invNum }}')" 
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 text-[10px] font-bold transition border border-rose-300"
                                        title="Revoke and Ban QR Code">
                                    <i class="fas fa-ban text-[8px]"></i> Revoke QR
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyInvoiceRow">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-file-invoice text-2xl text-slate-300 block mb-1"></i>
                            No invoices generated yet.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noInvoiceMatchesRow" class="hidden">
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching invoices</span>
                            <button type="button" onclick="resetInvoiceFilters()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
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
                <span id="filteredInvoiceCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ $totalCount }} invoices
                </span>
                <button type="button" 
                        id="resetInvoiceFiltersBtn" 
                        onclick="resetInvoiceFilters()" 
                        class="hidden text-orange-600 hover:underline font-semibold text-xs">
                    Reset
                </button>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setInvoicePageSize(5)" data-invsize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setInvoicePageSize(10)" data-invsize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setInvoicePageSize('all')" data-invsize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="invoicePaginationControls">
                </div>
            </div>
        </div>

    </div>
</div>

<script>
let currentInvoiceStatusFilter = 'all';
let currentInvoicePage = 1;
let invoicePageSize = 5;
let filteredInvoiceRows = [];

function setInvoiceStatusFilter(status) {
    currentInvoiceStatusFilter = status;
    currentInvoicePage = 1;

    document.querySelectorAll('#invoiceStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.invfilter === status) {
            tab.classList.add('active');
        } else {
            tab.classList.remove('active');
        }
    });

    applyInvoiceFilters();
}

function clearInvoiceSearch() {
    const input = document.getElementById('invoiceSearchInput');
    input.value = '';
    document.getElementById('clearInvoiceSearchBtn').classList.add('hidden');
    currentInvoicePage = 1;
    applyInvoiceFilters();
    input.focus();
}

function resetInvoiceFilters() {
    document.getElementById('invoiceSearchInput').value = '';
    document.getElementById('clearInvoiceSearchBtn').classList.add('hidden');
    currentInvoiceStatusFilter = 'all';
    document.querySelectorAll('#invoiceStatusTabs .kwdc-tab-pill').forEach(tab => {
        if (tab.dataset.invfilter === 'all') tab.classList.add('active');
        else tab.classList.remove('active');
    });
    currentInvoicePage = 1;
    applyInvoiceFilters();
}

function setInvoicePageSize(size) {
    invoicePageSize = (size === 'all') ? 9999 : parseInt(size);
    currentInvoicePage = 1;
    
    document.querySelectorAll('[data-invsize]').forEach(btn => {
        if (btn.dataset.invsize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderInvoicePagination();
}

function goToInvoicePage(page) {
    currentInvoicePage = page;
    renderInvoicePagination();
}

function applyInvoiceFilters() {
    const input = document.getElementById('invoiceSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearInvoiceSearchBtn');
    const resetBtn = document.getElementById('resetInvoiceFiltersBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const isFiltered = (currentInvoiceStatusFilter !== 'all' || query.length > 0);
    if (resetBtn) {
        if (isFiltered) resetBtn.classList.remove('hidden');
        else resetBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#invoicesTableBody .invoice-row'));
    filteredInvoiceRows = [];

    rows.forEach(row => {
        const rowStatus = row.dataset.status || '';
        const rowSearch = row.dataset.search || '';

        let statusMatches = (currentInvoiceStatusFilter === 'all' || rowStatus === currentInvoiceStatusFilter);
        let queryMatches = (!query || rowSearch.includes(query));

        if (statusMatches && queryMatches) {
            filteredInvoiceRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderInvoicePagination();
}

function renderInvoicePagination() {
    const totalCount = filteredInvoiceRows.length;
    const effectivePageSize = invoicePageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentInvoicePage > totalPages) currentInvoicePage = 1;
    if (currentInvoicePage < 1) currentInvoicePage = 1;

    const startIndex = (currentInvoicePage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#invoicesTableBody .invoice-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredInvoiceRows[i]) {
            filteredInvoiceRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredInvoiceCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching invoices';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} invoices`;
        }
    }

    const noMatchesRow = document.getElementById('noInvoiceMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('invoicePaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToInvoicePage(${currentInvoicePage - 1})" 
                ${currentInvoicePage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentInvoicePage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToInvoicePage(${p})" 
                    class="pager-btn ${p === currentInvoicePage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToInvoicePage(${currentInvoicePage + 1})" 
                ${currentInvoicePage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentInvoicePage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

function promptBanInvoice(invoiceId, invoiceNumber) {
    const reason = prompt(`Enter mandatory compliance reason for revoking QR on Invoice #${invoiceNumber}:`);
    if (reason && reason.trim()) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/invoices/${invoiceId}/ban-qr`;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);

        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'ban_reason';
        reasonInput.value = reason.trim();
        form.appendChild(reasonInput);

        document.body.appendChild(form);
        form.submit();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    applyInvoiceFilters();
});
if (document.readyState !== 'loading') {
    applyInvoiceFilters();
}
</script>
@endsection