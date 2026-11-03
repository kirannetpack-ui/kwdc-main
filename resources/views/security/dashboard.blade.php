@extends('layouts.app')

@section('title', 'Security Command Center')
@section('header', 'Security Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Apple-style Hero Banner -->
    <div class="kwdc-dashboard-hero flex flex-col md:flex-row md:items-center md:justify-between gap-6">
        <div>
            <div class="flex items-center gap-2.5 mb-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-50 text-orange-700 border border-orange-200">
                    Licensed Security Agency
                </span>
                <span class="text-xs text-gray-400 font-medium">KTM-WDC Protection</span>
            </div>
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 tracking-tight">
                Namaste, {{ Auth::user()->name }}
            </h1>
            <p class="text-sm text-gray-500 mt-1 max-w-xl">
                Real-time security operations command. Deploy verified guard personnel, monitor bonded logistics facilities, and track high-value cargo.
            </p>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <div class="kwdc-beacon-container">
                <span class="kwdc-beacon-dot"></span>
                <span>Secured & Guarded</span>
            </div>
            <div class="px-4 py-2 rounded-full bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-600">
                <i class="far fa-calendar-alt text-gray-400 mr-1.5"></i> {{ now()->format('D, M j, Y') }}
            </div>
        </div>
    </div>

    @if(!$agencyExists)
    <div class="bg-amber-50 border border-amber-200 text-amber-800 p-6 rounded-3xl flex items-center gap-4">
        <i class="fas fa-triangle-exclamation text-2xl text-amber-500"></i>
        <div>
            <h3 class="font-bold text-base">Agency Profile Incomplete</h3>
            <p class="text-sm text-amber-700 mt-0.5">You do not have an active security agency profile linked. Please contact the KTM-WDC administrator to activate your security clearance.</p>
        </div>
    </div>
    @else

    <!-- Quick Action Pills -->
    <div class="flex items-center gap-2.5 overflow-x-auto pb-1 scrollbar-none">
        <a href="{{ route('security.personnel.create') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-user-plus text-blue-500"></i>
            <span>Add Security Personnel</span>
        </a>
        <a href="{{ route('security.personnel.index') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-user-shield text-emerald-500"></i>
            <span>Manage Guard Roster</span>
        </a>
        <a href="{{ route('security.goods.index') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-box-archive text-purple-500"></i>
            <span>High-Value Goods</span>
        </a>
        <a href="{{ route('security.assignments.index') }}" class="kwdc-action-pill whitespace-nowrap">
            <i class="fas fa-building-shield text-orange-500"></i>
            <span>Facility Assignments</span>
        </a>
    </div>

    <!-- KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Active Personnel</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $personnelCount ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #eff6ff; color: #2563eb;">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Licensed Guards</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Secured Goods</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $goodsCount ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #ecfdf5; color: #059669;">
                    <i class="fas fa-shield-check"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Monitored Cargo</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Facility Posts</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $activeAssignments ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #fffbeb; color: #d97706;">
                    <i class="fas fa-building-shield"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>Active Deployments</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-4">
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Incident Reports</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-gray-900">{{ $incidentsCount ?? 0 }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: #fdf2f8; color: #db2777;">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-gray-50 flex items-center justify-between text-xs text-gray-500">
                <span>{{ ($incidentsCount ?? 0) === 0 ? 'All Clear' : 'Attention Required' }}</span>
                <i class="fas fa-arrow-up-right text-gray-300"></i>
            </div>
        </div>
    </div>

    <!-- Agency Details & Operational Status Surface -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="kwdc-surface-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-id-card text-blue-500"></i>
                    <h2 class="font-bold text-gray-900 text-base">Agency Credentials & Accreditation</h2>
                </div>
                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-green-50 text-green-700 border border-green-200">
                    {{ ucfirst($agency->status ?? 'approved') }}
                </span>
            </div>
            <dl class="space-y-3 text-sm">
                <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                    <dt class="text-xs font-bold uppercase text-gray-400">Registered Name</dt>
                    <dd class="font-bold text-gray-900">{{ $agency->agency_name ?? 'Licensed Agency' }}</dd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                    <dt class="text-xs font-bold uppercase text-gray-400">Registration Date</dt>
                    <dd class="font-semibold text-gray-700">{{ optional($agency->created_at)->format('F j, Y') ?? 'N/A' }}</dd>
                </div>
                <div class="flex items-center justify-between py-1.5 border-b border-gray-50">
                    <dt class="text-xs font-bold uppercase text-gray-400">Total Deployments</dt>
                    <dd class="font-semibold text-gray-700">{{ $totalAssignments ?? 0 }} Facilities Guarded</dd>
                </div>
                <div class="flex items-center justify-between py-1.5">
                    <dt class="text-xs font-bold uppercase text-gray-400">Security Clearance</dt>
                    <dd class="font-bold text-emerald-600"><i class="fas fa-check-circle mr-1"></i> Verified Partner</dd>
                </div>
            </dl>
        </div>

        <div class="kwdc-surface-card space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-bolt text-orange-500"></i>
                    <h2 class="font-bold text-gray-900 text-base">Quick Operations</h2>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="{{ route('security.personnel.create') }}" class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100 hover:bg-blue-100/70 transition space-y-1">
                    <p class="font-bold text-blue-900 text-sm"><i class="fas fa-user-plus mr-1.5"></i> Add Guard</p>
                    <p class="text-xs text-blue-700">Register verified security staff</p>
                </a>
                <a href="{{ route('security.goods.index') }}" class="p-4 rounded-2xl bg-purple-50/70 border border-purple-100 hover:bg-purple-100/70 transition space-y-1">
                    <p class="font-bold text-purple-900 text-sm"><i class="fas fa-box-archive mr-1.5"></i> High-Value Cargo</p>
                    <p class="text-xs text-purple-700">Inspect secured warehouse goods</p>
                </a>
                <a href="{{ route('security.assignments.index') }}" class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-100 hover:bg-emerald-100/70 transition space-y-1">
                    <p class="font-bold text-emerald-900 text-sm"><i class="fas fa-building-shield mr-1.5"></i> Warehouse Posts</p>
                    <p class="text-xs text-emerald-700">Review active perimeter assignments</p>
                </a>
                <a href="{{ route('security.personnel.index') }}" class="p-4 rounded-2xl bg-amber-50/70 border border-amber-100 hover:bg-amber-100/70 transition space-y-1">
                    <p class="font-bold text-amber-900 text-sm"><i class="fas fa-list-check mr-1.5"></i> Personnel Roster</p>
                    <p class="text-xs text-amber-700">Audit on-duty security guards</p>
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
