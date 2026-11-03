@extends('layouts.app')

@section('title', 'Security Agencies')
@section('header', 'Security Agencies')

@section('content')
@php
    $totalCount = $agencies instanceof \Illuminate\Pagination\AbstractPaginator ? $agencies->total() : count($agencies ?? []);
    $items = $agencies instanceof \Illuminate\Pagination\AbstractPaginator ? $agencies->items() : ($agencies ?? []);
    $pendingCount = collect($items)->where('status', 'pending')->count();
    $approvedCount = collect($items)->where('status', 'approved')->count();
    $rejectedCount = collect($items)->where('status', 'rejected')->count();
@endphp

<div class="max-w-7xl mx-auto space-y-2.5">
    <!-- Compact Executive Header -->
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Security Agencies</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200/80">
                <i class="fas fa-shield-halved text-[9px]"></i>
                {{ $totalCount }} Agencies
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.dashboard') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-slate-900 hover:text-white text-slate-700 text-xs font-semibold border border-slate-200 shadow-2xs transition">
                <i class="fas fa-gauge-high text-[10px]"></i>
                <span>Admin Hub</span>
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

    @if(session('error'))
    <div class="bg-rose-50 border border-rose-200 text-rose-800 text-xs px-3.5 py-2 rounded-xl flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-circle-exclamation text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- Main Data Shell -->
    <div class="bg-white border border-slate-200/80 rounded-xl p-3 sm:p-3.5 shadow-2xs">
        <!-- Filter & Search Toolbar -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-2.5 pb-2.5 border-b border-slate-100">
            <!-- Status Tabs -->
            <div class="inline-flex items-center p-1 rounded-xl bg-slate-100/90 border border-slate-200/60 overflow-x-auto no-scrollbar gap-1" id="agencyStatusTabs">
                <button type="button" onclick="setAgencyFilter('all')" data-agfilter="all" class="kwdc-tab-pill active">
                    <span>All</span>
                    <span class="tab-count-badge">{{ count($items) }}</span>
                </button>
                @if($pendingCount > 0)
                <button type="button" onclick="setAgencyFilter('pending')" data-agfilter="pending" class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Pending</span>
                    <span class="tab-count-badge">{{ $pendingCount }}</span>
                </button>
                @endif
                @if($approvedCount > 0)
                <button type="button" onclick="setAgencyFilter('approved')" data-agfilter="approved" class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Approved</span>
                    <span class="tab-count-badge">{{ $approvedCount }}</span>
                </button>
                @endif
                @if($rejectedCount > 0)
                <button type="button" onclick="setAgencyFilter('rejected')" data-agfilter="rejected" class="kwdc-tab-pill">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 shrink-0"></span>
                    <span>Rejected</span>
                    <span class="tab-count-badge">{{ $rejectedCount }}</span>
                </button>
                @endif
            </div>

            <!-- Instant Search Input -->
            <div class="relative w-full lg:w-72">
                <span class="kwdc-search-icon-slot">
                    <i class="fas fa-magnifying-glass"></i>
                </span>
                <input type="text" 
                       id="agencySearchInput" 
                       placeholder="Search agency, email, ID..." 
                       oninput="applyAgencyFilters()"
                       autocomplete="off"
                       class="kwdc-clean-search-input">
                <button type="button" 
                        id="agencySearchClear" 
                        onclick="clearAgencySearch()"
                        class="kwdc-search-clear-btn"
                        title="Clear search">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>
        </div>

        <!-- Table with Zero Sideways Scroll -->
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed" id="agencyTable">
                <thead>
                    <tr>
                        <th class="w-[60px]">ID</th>
                        <th class="w-[200px]">Agency Name</th>
                        <th class="w-[180px]">Contact Email</th>
                        <th class="w-[100px]">Status</th>
                        <th class="w-[180px] text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs" id="agencyTableBody">
                    @forelse($items as $agency)
                    @php
                        $status = strtolower($agency->status ?? 'pending');
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition agency-row"
                        data-status="{{ $status }}"
                        data-search="{{ strtolower('#' . $agency->id . ' ' . ($agency->agency_name ?? '') . ' ' . ($agency->email ?? '') . ' ' . $status) }}">
                        
                        <td class="font-mono text-slate-500 font-semibold">
                            #{{ $agency->id }}
                        </td>

                        <td>
                            <div class="font-bold text-slate-900 truncate" title="{{ $agency->agency_name }}">
                                {{ $agency->agency_name }}
                            </div>
                        </td>

                        <td class="font-mono text-slate-600 truncate" title="{{ $agency->email }}">
                            {{ $agency->email ?: '—' }}
                        </td>

                        <td>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $status === 'approved' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($status === 'rejected' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status === 'approved' ? 'bg-emerald-500' : ($status === 'rejected' ? 'bg-rose-500' : 'bg-amber-500') }}"></span>
                                {{ ucfirst($status) }}
                            </span>
                        </td>

                        <td class="text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <a href="{{ route('admin.security.agency.show', $agency) }}" 
                                   class="inline-flex items-center px-2 py-1 rounded-md bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-600 text-[10px] font-bold transition">
                                    View
                                </a>
                                @if($agency->status === 'pending')
                                <form action="{{ route('admin.security.agency.approve', $agency) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-2 py-1 rounded-md bg-emerald-50 hover:bg-emerald-600 hover:text-white text-emerald-700 text-[10px] font-bold transition">
                                        Approve
                                    </button>
                                </form>
                                <button type="button" 
                                        onclick="openRejectAgencyModal('{{ $agency->id }}')" 
                                        class="inline-flex items-center px-2 py-1 rounded-md bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-700 text-[10px] font-bold transition">
                                    Reject
                                </button>
                                @endif
                                <a href="{{ route('admin.security.agency.export', $agency) }}" 
                                   class="inline-flex items-center px-2 py-1 rounded-md bg-orange-50 hover:bg-orange-600 hover:text-white text-orange-600 text-[10px] font-bold transition"
                                   title="Export PDF Profile">
                                    PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-shield-halved text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold text-slate-700">No security agencies registered yet</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse

                    <tr id="emptyAgencySearch" class="hidden">
                        <td colspan="5" class="text-center py-8 text-slate-400">
                            <div class="flex flex-col items-center justify-center">
                                <i class="fas fa-filter text-2xl text-slate-300 mb-1.5"></i>
                                <span class="text-xs font-semibold">No agencies match your search</span>
                                <button type="button" onclick="clearAgencySearch()" class="mt-1.5 text-xs text-orange-600 font-bold hover:underline">
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
                Showing <span id="agencyRange" class="font-bold text-slate-800">1 - {{ min(5, count($items)) }}</span> of <span id="agencyTotal" class="font-bold text-slate-800">{{ count($items) }}</span> agencies
            </div>
            
            <div class="flex items-center gap-1 self-end sm:self-auto" id="agencyPagination">
                <button type="button" id="agencyPrevBtn" onclick="prevAgencyPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <i class="fas fa-chevron-left text-[9px]"></i>
                    <span>Prev</span>
                </button>
                <div id="agencyPageNumbers" class="flex items-center gap-1"></div>
                <button type="button" id="agencyNextBtn" onclick="nextAgencyPage()" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 disabled:opacity-40 disabled:cursor-not-allowed transition font-medium">
                    <span>Next</span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectAgencyModal" class="fixed inset-0 bg-black/50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-5">
        <h3 class="text-sm font-bold text-slate-900 mb-2">Reject Security Agency</h3>
        <form id="rejectAgencyForm" method="POST" action="">
            @csrf
            <div class="mb-3">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Reason for Rejection <span class="text-rose-500">*</span></label>
                <textarea name="reason" rows="3" required class="w-full text-xs p-2 rounded-lg border border-slate-200 focus:outline-none focus:border-orange-500" placeholder="Specify verification failure or missing docs..."></textarea>
            </div>
            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="closeRejectAgencyModal()" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition">Cancel</button>
                <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold transition">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let currentAgencyFilter = 'all';
let currentAgencyPage = 1;
const AGENCY_PER_PAGE = 5;

function openRejectAgencyModal(agencyId) {
    const form = document.getElementById('rejectAgencyForm');
    if (form) {
        form.action = `/admin/security/agencies/${agencyId}/reject`;
    }
    document.getElementById('rejectAgencyModal')?.classList.remove('hidden');
}

function closeRejectAgencyModal() {
    document.getElementById('rejectAgencyModal')?.classList.add('hidden');
}

function setAgencyFilter(status) {
    currentAgencyFilter = status;
    currentAgencyPage = 1;

    document.querySelectorAll('#agencyStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-agfilter') === status) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });

    applyAgencyFilters();
}

function clearAgencySearch() {
    const input = document.getElementById('agencySearchInput');
    if (input) input.value = '';
    currentAgencyFilter = 'all';
    document.querySelectorAll('#agencyStatusTabs button').forEach(btn => {
        if (btn.getAttribute('data-agfilter') === 'all') {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    applyAgencyFilters();
}

function applyAgencyFilters() {
    const searchVal = (document.getElementById('agencySearchInput')?.value || '').trim().toLowerCase();
    const clearBtn = document.getElementById('agencySearchClear');
    if (clearBtn) clearBtn.classList.toggle('visible', searchVal.length > 0);

    const rows = Array.from(document.querySelectorAll('.agency-row'));
    const matched = [];

    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status') || '';
        const searchData = row.getAttribute('data-search') || '';

        const matchesStatus = (currentAgencyFilter === 'all') || (rowStatus === currentAgencyFilter);
        const matchesSearch = !searchVal || searchData.includes(searchVal);

        if (matchesStatus && matchesSearch) {
            matched.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const empty = document.getElementById('emptyAgencySearch');
    if (empty) empty.classList.toggle('hidden', matched.length > 0 || rows.length === 0);

    paginateAgencies(matched);
}

function paginateAgencies(matched) {
    const total = matched.length;
    const totalPages = Math.max(1, Math.ceil(total / AGENCY_PER_PAGE));
    if (currentAgencyPage > totalPages) currentAgencyPage = totalPages;
    if (currentAgencyPage < 1) currentAgencyPage = 1;

    matched.forEach((row, idx) => {
        const start = (currentAgencyPage - 1) * AGENCY_PER_PAGE;
        const end = start + AGENCY_PER_PAGE;
        row.style.display = (idx >= start && idx < end) ? '' : 'none';
    });

    const startNum = total === 0 ? 0 : (currentAgencyPage - 1) * AGENCY_PER_PAGE + 1;
    const endNum = Math.min(total, currentAgencyPage * AGENCY_PER_PAGE);

    const rangeEl = document.getElementById('agencyRange');
    const totalEl = document.getElementById('agencyTotal');
    if (rangeEl) rangeEl.textContent = `${startNum} - ${endNum}`;
    if (totalEl) totalEl.textContent = total;

    const prevBtn = document.getElementById('agencyPrevBtn');
    const nextBtn = document.getElementById('agencyNextBtn');
    if (prevBtn) prevBtn.disabled = currentAgencyPage <= 1;
    if (nextBtn) nextBtn.disabled = currentAgencyPage >= totalPages;

    const container = document.getElementById('agencyPageNumbers');
    if (container) {
        container.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = i;
                btn.className = `w-6 h-6 rounded-md text-xs font-semibold transition ${
                    i === currentAgencyPage ? 'bg-orange-500 text-white shadow-xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50'
                }`;
                btn.onclick = () => { currentAgencyPage = i; applyAgencyFilters(); };
                container.appendChild(btn);
            }
        }
    }
}

function prevAgencyPage() {
    if (currentAgencyPage > 1) {
        currentAgencyPage--;
        applyAgencyFilters();
    }
}

function nextAgencyPage() {
    currentAgencyPage++;
    applyAgencyFilters();
}

document.addEventListener('DOMContentLoaded', () => {
    applyAgencyFilters();
});
</script>
@endpush
@endsection