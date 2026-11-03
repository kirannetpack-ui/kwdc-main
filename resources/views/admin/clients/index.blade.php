@extends('layouts.app')

@section('title', 'Registered Clients')
@section('header', 'Client Management')

@section('content')
@php
    $totalCount = count($clients ?? []);
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Registered Clients</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80">
                <i class="fas fa-users text-[9px]"></i>
                {{ $totalCount }} Clients
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
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
            <div class="text-xs text-slate-500 font-medium">
                <span id="filteredClientCount">Showing 1–5 of {{ $totalCount }} clients</span>
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full sm:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="clientSearchInput" 
                       placeholder="Search name, email, phone..." 
                       oninput="applyClientFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="clearClientSearchBtn" 
                        onclick="clearClientSearch()" 
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
                        <th class="w-[26%]">Name</th>
                        <th class="w-[28%]">Email</th>
                        <th class="w-[18%]">Phone</th>
                        <th class="w-[16%]">Registered</th>
                        <th class="w-[12%] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100" id="clientsTableBody">
                    @forelse($clients ?? [] as $client)
                    @php
                        $name = $client->name ?? 'Client';
                        $email = $client->email ?? '';
                        $phone = $client->phone ?? 'N/A';
                        $registered = $client->created_at ? $client->created_at->format('M d, Y') : '-';
                        $searchCorpus = strtolower(implode(' ', array_filter([
                            $name,
                            $email,
                            $phone,
                            $registered
                        ])));
                    @endphp
                    <tr class="client-row hover:bg-slate-50/80 transition-colors"
                        data-search="{{ $searchCorpus }}">
                        
                        <!-- Name -->
                        <td>
                            <div class="flex items-center gap-2 min-w-0" title="{{ $name }}">
                                <div class="w-6 h-6 rounded-full bg-slate-100 text-slate-700 flex items-center justify-center font-bold text-[10px] shrink-0">
                                    {{ strtoupper(substr($name, 0, 1)) }}
                                </div>
                                <span class="font-bold text-slate-900 text-xs truncate">{{ $name }}</span>
                            </div>
                        </td>

                        <!-- Email -->
                        <td>
                            <span class="text-slate-600 text-xs truncate block" title="{{ $email }}">
                                {{ $email }}
                            </span>
                        </td>

                        <!-- Phone -->
                        <td>
                            <span class="text-slate-700 text-xs truncate block" title="{{ $phone }}">
                                {{ $phone }}
                            </span>
                        </td>

                        <!-- Registered Date -->
                        <td>
                            <span class="text-slate-500 text-xs truncate block" title="{{ $registered }}">
                                {{ $registered }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="text-right">
                            <a href="{{ route('admin.clients.show', $client->id) }}" 
                               class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-[10.5px] font-semibold transition border border-slate-200 shadow-2xs group"
                               title="View Client Details">
                                <span>View</span>
                                <i class="fas fa-arrow-right text-[8px] opacity-60 group-hover:translate-x-0.5 transition-transform"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr id="emptyClientRow">
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
                            <i class="fas fa-users text-2xl text-slate-300 block mb-1"></i>
                            No registered clients found.
                        </td>
                    </tr>
                    @endforelse

                    <tr id="noClientMatchesRow" class="hidden">
                        <td colspan="5" class="px-4 py-8 text-center text-slate-400 text-xs">
                            <i class="fas fa-magnifying-glass text-xl text-slate-300 block mb-1"></i>
                            <span class="font-bold text-slate-700 text-xs block mb-0.5">No matching clients found</span>
                            <button type="button" onclick="clearClientSearch()" class="text-orange-600 hover:underline text-xs font-semibold mt-1">
                                Clear search
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
                <span id="filteredClientCount" class="font-medium text-slate-600 text-xs">
                    Showing 1–5 of {{ $totalCount }} clients
                </span>
            </div>

            <!-- Page Size & Pager Controls -->
            <div class="flex items-center gap-3">
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400">
                    <span>Rows:</span>
                    <button type="button" onclick="setClientPageSize(5)" data-clientsize="5" class="page-size-btn active">5</button>
                    <button type="button" onclick="setClientPageSize(10)" data-clientsize="10" class="page-size-btn">10</button>
                    <button type="button" onclick="setClientPageSize('all')" data-clientsize="all" class="page-size-btn">All</button>
                </div>

                <div class="inline-flex items-center gap-1" id="clientPaginationControls">
                </div>
            </div>
        </div>

    </div>
</div>

<script>
let currentClientPage = 1;
let clientPageSize = 5;
let filteredClientRows = [];

function clearClientSearch() {
    const input = document.getElementById('clientSearchInput');
    input.value = '';
    document.getElementById('clearClientSearchBtn').classList.add('hidden');
    currentClientPage = 1;
    applyClientFilters();
    input.focus();
}

function setClientPageSize(size) {
    clientPageSize = (size === 'all') ? 9999 : parseInt(size);
    currentClientPage = 1;
    
    document.querySelectorAll('[data-clientsize]').forEach(btn => {
        if (btn.dataset.clientsize == size) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    renderClientPagination();
}

function goToClientPage(page) {
    currentClientPage = page;
    renderClientPagination();
}

function applyClientFilters() {
    const input = document.getElementById('clientSearchInput');
    const query = (input ? input.value : '').toLowerCase().trim();
    const clearBtn = document.getElementById('clearClientSearchBtn');

    if (clearBtn) {
        if (query.length > 0) clearBtn.classList.remove('hidden');
        else clearBtn.classList.add('hidden');
    }

    const rows = Array.from(document.querySelectorAll('#clientsTableBody .client-row'));
    filteredClientRows = [];

    rows.forEach(row => {
        const rowSearch = row.dataset.search || '';
        const queryMatches = (!query || rowSearch.includes(query));

        if (queryMatches) {
            filteredClientRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    renderClientPagination();
}

function renderClientPagination() {
    const totalCount = filteredClientRows.length;
    const effectivePageSize = clientPageSize;
    const totalPages = Math.ceil(totalCount / effectivePageSize) || 1;
    if (currentClientPage > totalPages) currentClientPage = 1;
    if (currentClientPage < 1) currentClientPage = 1;

    const startIndex = (currentClientPage - 1) * effectivePageSize;
    const endIndex = Math.min(startIndex + effectivePageSize, totalCount);

    const allRows = document.querySelectorAll('#clientsTableBody .client-row');
    allRows.forEach(row => row.style.display = 'none');

    for (let i = startIndex; i < endIndex; i++) {
        if (filteredClientRows[i]) {
            filteredClientRows[i].style.display = '';
        }
    }

    const counterEl = document.getElementById('filteredClientCount');
    if (counterEl) {
        if (totalCount === 0) {
            counterEl.textContent = 'No matching clients found';
        } else {
            counterEl.textContent = `Showing ${startIndex + 1}–${endIndex} of ${totalCount} clients`;
        }
    }

    const noMatchesRow = document.getElementById('noClientMatchesRow');
    if (noMatchesRow) {
        if (totalCount === 0 && allRows.length > 0) {
            noMatchesRow.classList.remove('hidden');
        } else {
            noMatchesRow.classList.add('hidden');
        }
    }

    const paginationContainer = document.getElementById('clientPaginationControls');
    if (!paginationContainer) return;

    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }

    let html = `
        <button type="button" 
                onclick="goToClientPage(${currentClientPage - 1})" 
                ${currentClientPage === 1 ? 'disabled' : ''}
                class="pager-btn ${currentClientPage === 1 ? 'disabled' : ''}">
            <i class="fas fa-chevron-left text-[9px]"></i> Prev
        </button>
    `;

    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" 
                    onclick="goToClientPage(${p})" 
                    class="pager-btn ${p === currentClientPage ? 'active' : ''}">
                ${p}
            </button>
        `;
    }

    html += `
        <button type="button" 
                onclick="goToClientPage(${currentClientPage + 1})" 
                ${currentClientPage === totalPages ? 'disabled' : ''}
                class="pager-btn ${currentClientPage === totalPages ? 'disabled' : ''}">
            Next <i class="fas fa-chevron-right text-[9px]"></i>
        </button>
    `;

    paginationContainer.innerHTML = html;
}

document.addEventListener('DOMContentLoaded', function() {
    applyClientFilters();
});
if (document.readyState !== 'loading') {
    applyClientFilters();
}
</script>
@endsection