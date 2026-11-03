@extends('layouts.app')

@section('title', 'My Earnings')
@section('header', 'My Earnings')

@section('content')
@php
    $totalCount = isset($transactions) && $transactions instanceof \Illuminate\Pagination\AbstractPaginator ? $transactions->total() : (isset($transactions) ? count($transactions) : 0);
    $items = isset($transactions) && $transactions instanceof \Illuminate\Pagination\AbstractPaginator ? $transactions->items() : ($transactions ?? []);
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Driver Earnings</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200/80">
                <i class="fas fa-coins text-[9px]"></i>
                {{ $totalCount }} Runs
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('driver.jobs') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold border border-slate-200 shadow-2xs transition">
                <i class="fas fa-truck-ramp-box text-[10px]"></i>
                <span>Jobs List</span>
            </a>
            <a href="{{ route('driver.pickups') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold border border-slate-200 shadow-2xs transition">
                <i class="fas fa-box text-[10px]"></i>
                <span>Pickups</span>
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

    <!-- Sleek Earnings KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
        <div class="bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
            <div class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Total Net Earnings</div>
            <div class="text-xl font-black text-emerald-700 mt-0.5">रु {{ number_format($totalEarnings ?? 0, 2) }}</div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Completed Deliveries</div>
            <div class="text-xl font-black text-slate-900 mt-0.5">{{ $completedJobsCount ?? 0 }}</div>
        </div>
        <div class="bg-white border border-slate-200/80 rounded-xl p-3 shadow-2xs">
            <div class="text-[10px] font-bold text-orange-600 uppercase tracking-wider">Average per Job</div>
            <div class="text-xl font-black text-orange-700 mt-0.5">रु {{ number_format($averageEarning ?? 0, 2) }}</div>
        </div>
    </div>

    <!-- Main Data Shell -->
    <div class="bg-white border border-slate-200/80 rounded-xl p-3 sm:p-3.5 shadow-2xs">
        <!-- Search Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
            <div class="text-xs font-semibold text-slate-600">
                Itemized payout receipts for assigned transport deliveries
            </div>
            
            <div class="relative w-full sm:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="driverEarnSearchInput" 
                       placeholder="Search run #, destination..." 
                       oninput="applyDriverEarnFilter()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="driverEarnSearchClear" 
                        onclick="clearDriverEarnSearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Table with Zero Sideways Scroll -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="driverEarnTable">
                <thead>
                    <tr>
                        <th class="w-[120px]">Job Ref</th>
                        <th class="w-[240px]">Delivery Destination</th>
                        <th class="w-[110px]">Distance</th>
                        <th class="w-[130px]">Driver Earning</th>
                        <th class="w-[120px]">Delivered On</th>
                        <th class="w-[90px] text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="driverEarnTableBody">
                    @forelse($items as $transaction)
                    @php
                        $ref = $transaction->request_number ?? '#' . $transaction->id;
                        $loc = $transaction->delivery_location ?? 'N/A';
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition driver-earn-row"
                        data-search="{{ strtolower($ref . ' ' . $loc) }}">
                        
                        <td class="font-mono font-bold text-slate-800">
                            {{ $ref }}
                        </td>

                        <td>
                            <div class="truncate text-slate-800 font-semibold" title="{{ $loc }}">
                                <i class="fas fa-map-marker-alt text-orange-400 text-[10px] me-1"></i>
                                {{ \Illuminate\Support\Str::limit($loc, 35) }}
                            </div>
                        </td>

                        <td class="text-slate-600">
                            {{ $transaction->distance_km ? number_format($transaction->distance_km, 2) . ' km' : '—' }}
                        </td>

                        <td class="font-bold text-emerald-700">
                            रु {{ number_format($transaction->driver_earning ?? 0, 2) }}
                        </td>

                        <td class="text-slate-500 text-[11px]">
                            {{ $transaction->delivered_at ? \Carbon\Carbon::parse($transaction->delivered_at)->format('M d, Y') : '—' }}
                        </td>

                        <td class="text-right">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border bg-emerald-50 text-emerald-700 border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Delivered
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-chart-line text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold text-slate-700">No earnings recorded yet</span>
                                <span class="text-[11px] text-slate-400 mt-0.5">Complete deliveries and cargo transports to earn payouts.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyDriverEarnSearch" class="hidden">
                        <td colspan="6" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No runs match your search</span>
                                <button type="button" onclick="clearDriverEarnSearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
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
                Showing <span id="driverEarnRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="driverEarnTotal" class="font-bold text-slate-800">{{ count($items) }}</span> records
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="driverEarnPagination">
                <button type="button" id="driverEarnPrevBtn" onclick="prevDriverEarnPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="driverEarnPageNumbers" class="flex items-center gap-1"></div>
                <button type="button" id="driverEarnNextBtn" onclick="nextDriverEarnPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let currentDriverEarnPage = 1;
const DRIVER_EARN_PER_PAGE = 5;

function clearDriverEarnSearch() {
    const input = document.getElementById('driverEarnSearchInput');
    if (input) input.value = '';
    applyDriverEarnFilter();
}

function applyDriverEarnFilter() {
    const searchVal = (document.getElementById('driverEarnSearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('driverEarnSearchClear');
    if (clearBtn) clearBtn.classList.toggle('visible', searchVal.length > 0);

    const rows = Array.from(document.querySelectorAll('.driver-earn-row'));
    const matched = [];

    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        if (!searchVal || text.includes(searchVal)) {
            matched.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const empty = document.getElementById('emptyDriverEarnSearch');
    if (empty) empty.classList.toggle('hidden', matched.length > 0 || rows.length === 0);

    paginateDriverEarn(matched);
}

function paginateDriverEarn(matched) {
    const total = matched.length;
    const totalPages = Math.max(1, Math.ceil(total / DRIVER_EARN_PER_PAGE));
    if (currentDriverEarnPage > totalPages) currentDriverEarnPage = totalPages;
    if (currentDriverEarnPage < 1) currentDriverEarnPage = 1;

    matched.forEach((row, idx) => {
        const start = (currentDriverEarnPage - 1) * DRIVER_EARN_PER_PAGE;
        const end = start + DRIVER_EARN_PER_PAGE;
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    const startNum = total === 0 ? 0 : (currentDriverEarnPage - 1) * DRIVER_EARN_PER_PAGE + 1;
    const endNum = Math.min(total, currentDriverEarnPage * DRIVER_EARN_PER_PAGE);

    const rangeEl = document.getElementById('driverEarnRange');
    const totalEl = document.getElementById('driverEarnTotal');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('driverEarnPrevBtn');
    const nextBtn = document.getElementById('driverEarnNextBtn');
    if (prevBtn) prevBtn.disabled = currentDriverEarnPage <= 1;
    if (nextBtn) nextBtn.disabled = currentDriverEarnPage >= totalPages;

    const container = document.getElementById('driverEarnPageNumbers');
    if (container) {
        container.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = i;
                btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
                    i === currentDriverEarnPage ? 'bg-orange-500 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`;
                btn.onclick = () => { currentDriverEarnPage = i; applyDriverEarnFilter(); };
                container.appendChild(btn);
            }
        }
    }
}

function prevDriverEarnPage() {
    if (currentDriverEarnPage > 1) {
        currentDriverEarnPage--;
        applyDriverEarnFilter();
    }
}

function nextDriverEarnPage() {
    currentDriverEarnPage++;
    applyDriverEarnFilter();
}

document.addEventListener('DOMContentLoaded', () => {
    applyDriverEarnFilter();
});
</script>
@endpush
@endsection