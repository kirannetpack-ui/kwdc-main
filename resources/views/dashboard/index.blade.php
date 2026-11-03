@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@push('styles')
<style>
    .kwdc-kpi-val {
        font-feature-settings: "tnum";
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.025em;
    }
    .kwdc-glass-card {
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.7);
        outline: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 20px;
        box-shadow: 0 10px 32px 0 rgba(15, 23, 42, 0.04), inset 0 1px 0 0 rgba(255, 255, 255, 0.85);
        transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .kwdc-glass-card:hover {
        background: rgba(255, 255, 255, 0.94);
        outline-color: rgba(234, 88, 12, 0.22);
        box-shadow: 0 16px 40px -4px rgba(15, 23, 42, 0.08), inset 0 1px 0 0 rgba(255, 255, 255, 0.95);
        transform: translateY(-2px);
    }
    .kwdc-item-row {
        transition: all 0.15s ease;
    }
    .kwdc-item-row:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
    }
</style>
@endpush

@section('content')
@php
    $user = auth()->user();
    $role = $role ?? $user->role ?? 'client';

    $roleTitles = [
        'admin' => 'Admin',
        'client' => 'Client',
        'driver' => 'Driver',
        'property_owner' => 'Property',
        'equipment_owner' => 'Equipment',
        'security_agency' => 'Security',
    ];
    $roleTitle = $roleTitles[$role] ?? ucwords(str_replace('_', ' ', $role));

    $quickActions = [];
    if ($role === 'admin') {
        $quickActions = [
            ['label' => 'Approvals', 'icon' => 'clock', 'route' => route('admin.pending'), 'badge' => $stats['pending_requests'] ?? null, 'primary' => true],
            ['label' => 'Dispatches', 'icon' => 'truck-fast', 'route' => route('admin.dispatch'), 'badge' => null, 'primary' => false],
            ['label' => 'Drivers', 'icon' => 'id-card', 'route' => route('admin.drivers'), 'badge' => null, 'primary' => false],
            ['label' => 'Warehouses', 'icon' => 'warehouse', 'route' => route('warehouses.index'), 'badge' => null, 'primary' => false],
        ];
    } elseif ($role === 'client') {
        $quickActions = [
            ['label' => '+ Space', 'icon' => 'plus-circle', 'route' => route('my-requests.create'), 'badge' => null, 'primary' => true],
            ['label' => 'Dispatch', 'icon' => 'truck-arrow-right', 'route' => route('dispatch.direct-create'), 'badge' => null, 'primary' => false],
            ['label' => 'Pickup', 'icon' => 'boxes-packing', 'route' => route('pickup.direct-create'), 'badge' => null, 'primary' => false],
            ['label' => 'Invoices', 'icon' => 'receipt', 'route' => route('invoices.client-index'), 'badge' => $stats['pending_invoices'] ?? null, 'primary' => false],
        ];
    } elseif ($role === 'driver') {
        $quickActions = [
            ['label' => 'Available', 'icon' => 'magnifying-glass-location', 'route' => route('driver.available-jobs'), 'badge' => $stats['available_jobs'] ?? null, 'primary' => true],
            ['label' => 'Active', 'icon' => 'route', 'route' => route('driver.jobs'), 'badge' => $stats['active_jobs'] ?? null, 'primary' => false],
            ['label' => 'Pickups', 'icon' => 'boxes-stacked', 'route' => route('driver.pickups'), 'badge' => null, 'primary' => false],
            ['label' => 'Earnings', 'icon' => 'wallet', 'route' => route('driver.earnings'), 'badge' => null, 'primary' => false],
        ];
    } elseif ($role === 'equipment_owner') {
        $quickActions = [
            ['label' => '+ Equipment', 'icon' => 'plus-circle', 'route' => route('equipment.register'), 'badge' => null, 'primary' => true],
            ['label' => 'Fleet', 'icon' => 'gear', 'route' => route('equipment.list'), 'badge' => null, 'primary' => false],
            ['label' => 'Requests', 'icon' => 'clipboard-check', 'route' => route('equipment.jobs.requests'), 'badge' => $stats['job_requests'] ?? null, 'primary' => false],
            ['label' => 'Earnings', 'icon' => 'coins', 'route' => route('equipment.jobs.earnings'), 'badge' => null, 'primary' => false],
        ];
    } elseif ($role === 'property_owner') {
        $quickActions = [
            ['label' => '+ Warehouse', 'icon' => 'plus-circle', 'route' => route('warehouses.create'), 'badge' => null, 'primary' => true],
            ['label' => 'Requests', 'icon' => 'clipboard-list', 'route' => route('property.requests.index'), 'badge' => $stats['pending_requests'] ?? 1, 'primary' => false],
            ['label' => 'Analytics', 'icon' => 'chart-line', 'route' => route('property.analytics'), 'badge' => null, 'primary' => false],
            ['label' => 'Properties', 'icon' => 'building', 'route' => route('property.approved'), 'badge' => null, 'primary' => false],
        ];
    } elseif ($role === 'security_agency') {
        $quickActions = [
            ['label' => '+ Guard', 'icon' => 'user-plus', 'route' => route('security.dashboard'), 'badge' => null, 'primary' => true],
            ['label' => 'Cargo', 'icon' => 'box-archive', 'route' => route('security.dashboard'), 'badge' => null, 'primary' => false],
            ['label' => 'Posts', 'icon' => 'building-shield', 'route' => route('security.dashboard'), 'badge' => null, 'primary' => false],
        ];
    }

    $kpiList = [];
    if ($role === 'property_owner') {
        $whTotal = $stats['my_properties'] ?? 2;
        $whApproved = $stats['approved_properties'] ?? 2;
        $revenueVal = ($stats['total_revenue'] ?? 0) > 0 ? (float) $stats['total_revenue'] : 185000;

        $kpiList = [
            [
                'label' => 'Properties',
                'val' => $whTotal,
                'sub' => $whApproved . '/' . $whTotal . ' live',
                'pill' => 'Active',
                'pill_type' => 'success',
                'icon' => 'warehouse',
                'icon_bg' => 'bg-blue-50 text-blue-600',
                'meter' => 100,
            ],
            [
                'label' => 'Inquiries',
                'val' => $stats['total_requests'] ?? 3,
                'sub' => ($stats['pending_requests'] ?? 1) . ' pending',
                'pill' => 'Review',
                'pill_type' => 'warning',
                'icon' => 'clipboard-check',
                'icon_bg' => 'bg-amber-50 text-amber-600',
                'meter' => null,
            ],
            [
                'label' => 'Occupancy',
                'val' => '78%',
                'sub' => ($stats['approved_requests'] ?? 2) . ' leased',
                'pill' => '↑ 14%',
                'pill_type' => 'success',
                'icon' => 'cubes',
                'icon_bg' => 'bg-purple-50 text-purple-600',
                'meter' => 78,
            ],
            [
                'label' => 'Revenue',
                'val' => 'NPR ' . number_format($revenueVal),
                'sub' => 'Monthly lease',
                'pill' => '↑ 8.5%',
                'pill_type' => 'success',
                'icon' => 'wallet',
                'icon_bg' => 'bg-emerald-50 text-emerald-600',
                'meter' => null,
            ],
        ];
    } elseif ($role === 'admin') {
        $kpiList = [
            [
                'label' => 'Warehouses',
                'val' => $stats['approved_warehouses'] ?? 0,
                'sub' => ($stats['warehouses'] ?? 0) . ' total',
                'pill' => 'Active',
                'pill_type' => 'success',
                'icon' => 'warehouse',
                'icon_bg' => 'bg-blue-50 text-blue-600',
                'meter' => 100,
            ],
            [
                'label' => 'Dispatches',
                'val' => (($stats['total_dispatches'] ?? 0) - ($stats['completed_dispatches'] ?? 0)),
                'sub' => ($stats['completed_dispatches'] ?? 0) . ' delivered',
                'pill' => 'Transit',
                'pill_type' => 'success',
                'icon' => 'truck-fast',
                'icon_bg' => 'bg-emerald-50 text-emerald-600',
                'meter' => null,
            ],
            [
                'label' => 'Pending Queue',
                'val' => (($stats['pending_warehouses'] ?? 0) + ($stats['pending_requests'] ?? 0)),
                'sub' => 'Review queue',
                'pill' => 'Action',
                'pill_type' => 'warning',
                'icon' => 'clock',
                'icon_bg' => 'bg-amber-50 text-amber-600',
                'meter' => null,
            ],
            [
                'label' => 'Fleet Drivers',
                'val' => $stats['drivers'] ?? 0,
                'sub' => ($stats['vehicles'] ?? 0) . ' vehicles',
                'pill' => 'Ready',
                'pill_type' => 'success',
                'icon' => 'id-card',
                'icon_bg' => 'bg-purple-50 text-purple-600',
                'meter' => null,
            ],
        ];
    } elseif ($role === 'client') {
        $kpiList = [
            [
                'label' => 'Storage',
                'val' => $stats['active_requests'] ?? 0,
                'sub' => 'Active leases',
                'pill' => 'Active',
                'pill_type' => 'success',
                'icon' => 'warehouse',
                'icon_bg' => 'bg-blue-50 text-blue-600',
                'meter' => null,
            ],
            [
                'label' => 'Dispatches',
                'val' => $stats['pending_dispatches'] ?? 0,
                'sub' => ($stats['completed_dispatches'] ?? 0) . ' delivered',
                'pill' => 'Transit',
                'pill_type' => 'success',
                'icon' => 'truck-moving',
                'icon_bg' => 'bg-emerald-50 text-emerald-600',
                'meter' => null,
            ],
            [
                'label' => 'Pickups',
                'val' => $stats['pickups'] ?? 0,
                'sub' => 'Scheduled routes',
                'pill' => 'Active',
                'pill_type' => 'success',
                'icon' => 'box-open',
                'icon_bg' => 'bg-amber-50 text-amber-600',
                'meter' => null,
            ],
            [
                'label' => 'Logistics Spent',
                'val' => 'NPR ' . number_format((float) ($stats['total_spent'] ?? 0)),
                'sub' => 'Total settled',
                'pill' => 'Paid',
                'pill_type' => 'success',
                'icon' => 'wallet',
                'icon_bg' => 'bg-purple-50 text-purple-600',
                'meter' => null,
            ],
        ];
    } elseif ($role === 'driver') {
        $kpiList = [
            [
                'label' => 'Active Jobs',
                'val' => $stats['active_jobs'] ?? 0,
                'sub' => 'In progress',
                'pill' => 'Route',
                'pill_type' => 'warning',
                'icon' => 'route',
                'icon_bg' => 'bg-blue-50 text-blue-600',
                'meter' => null,
            ],
            [
                'label' => 'Completed',
                'val' => $stats['completed_jobs'] ?? 0,
                'sub' => 'Delivered',
                'pill' => 'Done',
                'pill_type' => 'success',
                'icon' => 'circle-check',
                'icon_bg' => 'bg-emerald-50 text-emerald-600',
                'meter' => null,
            ],
            [
                'label' => 'Earnings',
                'val' => 'NPR ' . number_format((float) ($stats['total_earnings'] ?? 0)),
                'sub' => 'Net payout',
                'pill' => 'Verified',
                'pill_type' => 'success',
                'icon' => 'wallet',
                'icon_bg' => 'bg-amber-50 text-amber-600',
                'meter' => null,
            ],
            [
                'label' => 'Rating',
                'val' => number_format((float) ($stats['rating'] > 0 ? $stats['rating'] : 5.0), 1) . ' ★',
                'sub' => 'Driver score',
                'pill' => 'Top Tier',
                'pill_type' => 'success',
                'icon' => 'star',
                'icon_bg' => 'bg-purple-50 text-purple-600',
                'meter' => null,
            ],
        ];
    } elseif ($role === 'equipment_owner') {
        $kpiList = [
            [
                'label' => 'Fleet',
                'val' => $stats['my_equipment'] ?? 0,
                'sub' => ($stats['available_equipment'] ?? 0) . ' ready',
                'pill' => 'Available',
                'pill_type' => 'success',
                'icon' => 'truck-ramp-box',
                'icon_bg' => 'bg-blue-50 text-blue-600',
                'meter' => null,
            ],
            [
                'label' => 'On Site',
                'val' => $stats['active_jobs'] ?? 0,
                'sub' => 'Active rental',
                'pill' => 'Active',
                'pill_type' => 'warning',
                'icon' => 'hard-hat',
                'icon_bg' => 'bg-emerald-50 text-emerald-600',
                'meter' => null,
            ],
            [
                'label' => 'Completed',
                'val' => $stats['completed_jobs'] ?? 0,
                'sub' => 'Returned',
                'pill' => 'Done',
                'pill_type' => 'success',
                'icon' => 'check-double',
                'icon_bg' => 'bg-amber-50 text-amber-600',
                'meter' => null,
            ],
            [
                'label' => 'Earnings',
                'val' => 'NPR ' . number_format((float) ($stats['total_earnings'] ?? 0)),
                'sub' => 'Net rental',
                'pill' => 'Paid',
                'pill_type' => 'success',
                'icon' => 'coins',
                'icon_bg' => 'bg-purple-50 text-purple-600',
                'meter' => null,
            ],
        ];
    } elseif ($role === 'security_agency') {
        $kpiList = [
            [
                'label' => 'Guards',
                'val' => $stats['personnel_count'] ?? 0,
                'sub' => 'Active officers',
                'pill' => 'On Duty',
                'pill_type' => 'success',
                'icon' => 'user-shield',
                'icon_bg' => 'bg-blue-50 text-blue-600',
                'meter' => null,
            ],
            [
                'label' => 'Cargo Units',
                'val' => $stats['goods_count'] ?? 0,
                'sub' => 'Secured goods',
                'pill' => 'Protected',
                'pill_type' => 'success',
                'icon' => 'shield-check',
                'icon_bg' => 'bg-emerald-50 text-emerald-600',
                'meter' => null,
            ],
            [
                'label' => 'Posts',
                'val' => $stats['assignments'] ?? 0,
                'sub' => ($stats['active_assignments'] ?? 0) . ' active',
                'pill' => 'Guarded',
                'pill_type' => 'success',
                'icon' => 'building-shield',
                'icon_bg' => 'bg-amber-50 text-amber-600',
                'meter' => null,
            ],
            [
                'label' => 'Incidents',
                'val' => $stats['incidents_reported'] ?? 0,
                'sub' => 'Zero breach',
                'pill' => 'Clear',
                'pill_type' => 'success',
                'icon' => 'triangle-exclamation',
                'icon_bg' => 'bg-purple-50 text-purple-600',
                'meter' => null,
            ],
        ];
    }
@endphp

<div class="max-w-7xl mx-auto space-y-6">
    <!-- Unified Executive Command Hero & AI Logistics Brief -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
        <!-- LEFT: Executive Mission Control & Capacity Overview (5 cols) -->
        <div class="lg:col-span-5 kwdc-hero-welcome space-y-4">
            <!-- Greeting Header -->
            <div>
                <div class="flex items-center justify-between gap-3 mb-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-[#FBF2ED] text-[#D96B43] border border-[#F3D5C7]">
                            <i class="fas fa-layer-group"></i> {{ __($roleTitle) }}
                        </span>
                        <span class="text-[11px] font-semibold text-[#8A8379] uppercase tracking-wider">{{ __('Executive Overview') }}</span>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#EFF6F1] text-[#3D7357] border border-[#D4E7DC]">
                        <span class="w-2 h-2 rounded-full bg-[#3D7357] animate-pulse"></span>
                        <span>{{ __('Active Session') }}</span>
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-serif text-[#24201D] tracking-tight leading-tight">
                    {{ __('Namaste') }}, {{ $user->name }}
                </h1>
                <p class="text-xs text-[#8A8379] font-medium mt-1">
                    {{ now()->format('l, F j, Y') }} &bull; {{ now()->format('h:i A') }}
                </p>
            </div>

            <!-- Visual Capacity Ring & Utilization Gauge -->
            <div class="bg-gradient-to-br from-[#FFFDF9] to-[#F7F3EE] border border-[#E8E2D8] rounded-2xl p-3.5 shadow-2xs">
                <div class="flex items-center gap-3.5">
                    <div class="relative w-14 h-14 flex-shrink-0">
                        <svg class="w-14 h-14 transform -rotate-90" viewBox="0 0 36 36">
                            <path class="text-[#EAE3D9]" stroke-width="3.5" stroke="currentColor" fill="none"
                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-[#D96B43] transition-all duration-1000 ease-out" stroke-dasharray="{{ $stats['occupancyRate'] ?? 70 }}, 100" stroke-width="3.5" stroke-linecap="round" stroke="currentColor" fill="none"
                                  d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                            <span class="text-xs font-serif font-bold text-[#24201D] leading-none">{{ $stats['occupancyRate'] ?? 70 }}%</span>
                            <span class="text-[7px] font-bold text-[#8A8379] uppercase tracking-tighter mt-0.5">{{ __('Occupancy') }}</span>
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-[#24201D] truncate">{{ __('Hub Storage Occupancy') }}</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-[#EFF6F1] text-[#3D7357] border border-[#D4E7DC] flex-shrink-0">
                                +3.2% ↑
                            </span>
                        </div>
                        <p class="text-[11px] text-[#57524C] mt-0.5 leading-snug">
                            {{ $stats['approved_warehouses'] ?? 4 }} {{ __('All Warehouses') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Live Telemetry Row: 3 High-Density Tiles -->
            <div class="grid grid-cols-3 gap-2.5 pt-1">
                <div class="bg-white border border-[#E8E2D8] rounded-xl p-2.5 shadow-2xs hover:border-[#D6CCC0] transition text-left">
                    <span class="text-[10px] font-bold text-[#8A8379] uppercase tracking-wider block truncate">{{ __('Dispatches') }}</span>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-base font-serif font-bold text-[#24201D] kwdc-kpi-val">{{ $stats['total_dispatches'] ?? 18 }}</span>
                        <span class="text-[10px] font-bold text-[#D96B43] truncate">{{ $stats['pending_dispatches'] ?? 6 }} {{ __('queue') }}</span>
                    </div>
                </div>
                <div class="bg-white border border-[#E8E2D8] rounded-xl p-2.5 shadow-2xs hover:border-[#D6CCC0] transition text-left">
                    <span class="text-[10px] font-bold text-[#8A8379] uppercase tracking-wider block truncate">{{ __('Fleet Units') }}</span>
                    <div class="flex items-baseline gap-1 mt-1">
                        <span class="text-base font-serif font-bold text-[#24201D] kwdc-kpi-val">{{ $stats['active_drivers'] ?? ($stats['drivers'] ?? 6) }}</span>
                        <span class="text-[10px] font-bold text-[#8A8379] truncate">/ {{ $stats['vehicles'] ?? 10 }}</span>
                    </div>
                </div>
                <div class="bg-white border border-[#E8E2D8] rounded-xl p-2.5 shadow-2xs hover:border-[#D6CCC0] transition text-left">
                    <span class="text-[10px] font-bold text-[#8A8379] uppercase tracking-wider block truncate">{{ __('Network') }}</span>
                    <div class="flex items-center gap-1.5 mt-1">
                        <span class="w-2 h-2 rounded-full bg-[#3D7357] animate-pulse"></span>
                        <span class="text-xs font-bold text-[#24201D]">{{ __('Optimal') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: AI Logistics Brief Card (7 cols) -->
        <div class="lg:col-span-7 kwdc-brief-card flex flex-col justify-between" id="aiDashboardBriefWidget">
            <div>
                <div class="flex items-center justify-between gap-4 pb-3 border-b border-[#F0EAE1]">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#D96B43] to-[#B85832] text-white flex items-center justify-center text-xs shadow-xs flex-shrink-0">
                            <i class="fas fa-wand-magic-sparkles"></i>
                        </div>
                        <div>
                            <h2 class="text-sm font-serif font-bold text-[#24201D] tracking-tight mb-0">{{ __('AI Logistics Brief') }}</h2>
                            <span class="text-[11px] text-[#8A8379] font-medium">{{ __('Powered by Gemini • Real-time Operations') }}</span>
                        </div>
                    </div>
                    <button type="button" id="btnRefreshAiBrief" onclick="refreshDashboardAiBrief(true)" class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-lg bg-[#FAF8F5] hover:bg-[#F3EFEA] text-[#57524C] font-semibold transition border border-[#E8E2D8] shadow-2xs">
                        <i class="fas fa-rotate text-[10px] text-[#D96B43]" id="aiBriefRefreshIcon"></i>
                        <span>{{ __('Refresh') }}</span>
                    </button>
                </div>

                <div id="aiBriefLoading" class="hidden text-center py-6 text-xs text-slate-500">
                    <div class="spinner-border spinner-border-sm text-orange-500 mb-2" role="status"></div>
                    <p class="font-medium">{{ __('Synthesizing logistics intelligence...') }}</p>
                </div>

                <div id="aiBriefContent" class="pt-3 space-y-2.5">
                    <p id="aiBriefSummary" class="text-xs text-slate-600 leading-relaxed font-medium mb-0">
                        Network operations are managing 18 total dispatches with a healthy revenue baseline of 111,300, though 6 dispatches remain pending execution.
                    </p>

                    <div id="aiBriefBullets" class="space-y-2">
                        <div class="kwdc-insight-row">
                            <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded border bg-blue-50 text-blue-700 border-blue-200/80 flex-shrink-0 mt-0.5">
                                <i class="fas fa-truck-fast text-[9px]"></i> DISPATCH
                            </span>
                            <span class="text-xs text-slate-700 font-medium leading-relaxed flex-1">Prioritize clearing the 6 pending dispatches to improve the 4 completed dispatch ratio.</span>
                        </div>
                        <div class="kwdc-insight-row">
                            <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded border bg-emerald-50 text-emerald-700 border-emerald-200/80 flex-shrink-0 mt-0.5">
                                <i class="fas fa-warehouse text-[9px]"></i> CAPACITY
                            </span>
                            <span class="text-xs text-slate-700 font-medium leading-relaxed flex-1">Review and process the 2 pending warehouse approvals to expand operational capacity from the current 4 active facilities.</span>
                        </div>
                        <div class="kwdc-insight-row">
                            <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded border bg-amber-50 text-amber-700 border-amber-200/80 flex-shrink-0 mt-0.5">
                                <i class="fas fa-id-card text-[9px]"></i> FLEET
                            </span>
                            <span class="text-xs text-slate-700 font-medium leading-relaxed flex-1">Coordinate with available vehicle assets (10 registered) and drivers (6 active) to handle the 2 pending system requests.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-slate-100">
                <div class="kwdc-priority-banner flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-start gap-2.5 min-w-0">
                        <span class="w-6 h-6 rounded-lg bg-amber-500/20 text-amber-800 flex items-center justify-center text-xs flex-shrink-0 mt-0.5">
                            <i class="fas fa-bolt"></i>
                        </span>
                        <div class="text-xs text-amber-900 leading-relaxed min-w-0">
                            <strong class="font-extrabold uppercase tracking-wider text-[10px] text-amber-900 block mb-0.5">{{ __('Priority Directive:') }}</strong>
                            <span id="aiBriefAction">Immediately allocate available driver and vehicle resources to clear the 6 pending dispatches and review the 2 pending warehouse applications.</span>
                        </div>
                    </div>
                    <a href="{{ $role === 'admin' ? route('admin.dispatch') : ($role === 'driver' ? route('driver.jobs') : route('dispatch.direct-create')) }}" class="px-3.5 py-1.5 rounded-full bg-[#D96B43] hover:bg-[#C35832] text-white text-xs font-semibold transition flex items-center gap-1.5 flex-shrink-0 shadow-2xs self-end sm:self-auto">
                        <span>{{ __('Take Action') }}</span>
                        <i class="fas fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Bar -->
    @if(!empty($quickActions))
    <div class="kwdc-action-bar">
        @foreach($quickActions as $action)
            @if($action['primary'])
            <a href="{{ $action['route'] }}" class="kwdc-action-btn primary">
                <i class="fas fa-{{ $action['icon'] }}"></i>
                <span>{{ __($action['label']) }}</span>
            </a>
            @else
            <a href="{{ $action['route'] }}" class="kwdc-action-btn">
                <i class="fas fa-{{ $action['icon'] }}" style="color: #D96B43;"></i>
                <span>{{ __($action['label']) }}</span>
                @if(!empty($action['badge']) && $action['badge'] > 0)
                    <span class="kwdc-action-badge">
                        {{ $action['badge'] }}
                    </span>
                @endif
            </a>
            @endif
        @endforeach
    </div>
    @endif

    <!-- 4 High-Impact KPI Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        @foreach($kpiList as $kpi)
        <div class="kwdc-glass-card p-5 sm:p-6 flex flex-col justify-between space-y-4">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ __($kpi['label']) }}</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 kwdc-kpi-val">{{ $kpi['val'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-base flex-shrink-0 {{ $kpi['icon_bg'] }}">
                    <i class="fas fa-{{ $kpi['icon'] }}"></i>
                </div>
            </div>

            @if(isset($kpi['meter']) && $kpi['meter'] !== null)
            <div class="space-y-1.5 pt-1">
                <div class="w-full h-1.5 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-full rounded-full bg-orange-500" style="width: {{ $kpi['meter'] }}%;"></div>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-500 font-medium">
                    <span>{{ __($kpi['sub']) }}</span>
                    <span class="font-bold text-slate-700">{{ $kpi['meter'] }}%</span>
                </div>
            </div>
            @else
            <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium truncate">{{ __($kpi['sub']) }}</span>
                @if($kpi['pill_type'] === 'success')
                <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ __($kpi['pill']) }}
                </span>
                @elseif($kpi['pill_type'] === 'warning')
                <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                    {{ __($kpi['pill']) }}
                </span>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <!-- Analytics Visualizers Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Main Line/Bar Chart -->
        <div class="lg:col-span-2 kwdc-glass-card p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ __('Revenue') }}</h2>
                    <p class="text-[11px] text-slate-400" id="primaryChartSubtitle">{{ __('Last 7 days') }}</p>
                </div>
                <div class="flex items-center gap-1">
                    <button type="button" onclick="setChartRange('7d')" id="chartBtn7d" class="px-2.5 py-1 rounded-lg bg-slate-900 text-white text-xs font-bold transition shadow-2xs">7D</button>
                    <button type="button" onclick="setChartRange('30d')" id="chartBtn30d" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium transition">30D</button>
                    <button type="button" onclick="setChartRange('90d')" id="chartBtn90d" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium transition">90D</button>
                </div>
            </div>
            <div class="h-60 sm:h-64 w-full relative">
                <canvas id="dashboardPrimaryChart"></canvas>
            </div>
        </div>

        <!-- Workload Distribution Doughnut Chart -->
        <div class="kwdc-glass-card p-5 sm:p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">{{ __('Occupancy') }}</h2>
                    <p class="text-[11px] text-slate-400">{{ __('Capacity breakdown') }}</p>
                </div>
                <div class="w-7 h-7 rounded-lg bg-slate-50 flex items-center justify-center text-slate-400">
                    <i class="fas fa-chart-pie text-xs"></i>
                </div>
            </div>
            <div class="h-60 sm:h-64 w-full relative flex items-center justify-center">
                <canvas id="dashboardActivityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Role-Specific Feeds -->
    @if($role === 'property_owner')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Warehouses -->
        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-warehouse text-orange-500 text-sm"></i>
                    <h2 class="font-bold text-slate-900 text-sm">{{ __('Warehouses') }}</h2>
                </div>
                <a href="{{ route('warehouses.create') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">+ {{ __('Add') }}</a>
            </div>
            <div class="space-y-2">
                @forelse($myWarehouses ?? [] as $wh)
                <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100 kwdc-item-row cursor-pointer flex items-center justify-between gap-3" data-record-url="{{ route('warehouses.show', $wh->id) }}">
                    <div>
                        <h3 class="font-bold text-slate-900 text-xs">{{ $wh->name }}</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">
                            <i class="fas fa-location-dot text-orange-500 mr-1"></i>{{ $wh->city ?? 'Kathmandu' }} &bull; {{ number_format((float) ($wh->capacity ?? 0)) }} m³
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                            {{ $wh->status === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                            {{ ucfirst($wh->status) }}
                        </span>
                        <span class="text-xs text-orange-600 font-bold">&rarr;</span>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">{{ __('No warehouses listed.') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Inbound Requests -->
        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-clipboard-list text-blue-500 text-sm"></i>
                    <h2 class="font-bold text-slate-900 text-sm">{{ __('Requests') }}</h2>
                </div>
                <a href="{{ route('property.requests.index') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">{{ __('All') }}</a>
            </div>
            <div class="space-y-2">
                @forelse($recentRequests ?? [] as $req)
                <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100 kwdc-item-row cursor-pointer flex items-center justify-between gap-3" data-record-url="{{ route('warehouse-requests.show', $req->id) }}">
                    <div>
                        <p class="font-bold text-slate-900 text-xs">Request #{{ $req->id }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ optional($req->client)->name ?? 'Client' }} &bull; {{ $req->space_sqft ?? 0 }} sq.ft</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                            {{ ($req->status ?? 'pending') === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                            {{ ucfirst($req->status ?? 'pending') }}
                        </span>
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">{{ __('No requests.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
    @elseif($role === 'admin')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Warehouses -->
        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-warehouse text-orange-500 text-sm"></i>
                    <h2 class="font-bold text-slate-900 text-sm">{{ __('Warehouses') }}</h2>
                </div>
                <a href="{{ route('warehouses.index') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">{{ __('All') }}</a>
            </div>
            <div class="space-y-2">
                @forelse($recentWarehouses ?? [] as $wh)
                <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100 kwdc-item-row cursor-pointer flex items-center justify-between gap-3" data-record-url="{{ route('warehouses.show', $wh->id) }}">
                    <div>
                        <h3 class="font-bold text-slate-900 text-xs">{{ $wh->name }}</h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $wh->city ?? 'Kathmandu' }}</p>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                        {{ $wh->status === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        {{ ucfirst($wh->status) }}
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">{{ __('No warehouses listed.') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Dispatches -->
        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-truck text-emerald-500 text-sm"></i>
                    <h2 class="font-bold text-slate-900 text-sm">{{ __('Dispatches') }}</h2>
                </div>
                <a href="{{ route('dispatch.index') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">{{ __('All') }}</a>
            </div>
            <div class="space-y-2">
                @forelse($recentDispatches ?? [] as $disp)
                <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100 kwdc-item-row cursor-pointer flex items-center justify-between gap-3" data-record-url="{{ route('dispatch.show', $disp->id) }}">
                    <div>
                        <p class="font-bold text-slate-900 text-xs">#{{ $disp->tracking_id ?? $disp->id }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ Str::limit($disp->destination_address ?? 'Kathmandu', 25) }}</p>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                        {{ ucfirst(str_replace('_', ' ', $disp->status ?? 'pending')) }}
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">{{ __('No dispatches.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
    @elseif($role === 'client')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Shipments -->
        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-truck text-orange-500 text-sm"></i>
                    <h2 class="font-bold text-slate-900 text-sm">{{ __('Shipments') }}</h2>
                </div>
                <a href="{{ route('dispatch.index') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">{{ __('All') }}</a>
            </div>
            <div class="space-y-2">
                @forelse($recentDispatches ?? [] as $disp)
                <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100 kwdc-item-row cursor-pointer flex items-center justify-between gap-3" data-record-url="{{ route('dispatch.show', $disp->id) }}">
                    <div>
                        <p class="font-bold text-slate-900 text-xs">#{{ $disp->tracking_id ?? $disp->id }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ Str::limit($disp->destination_address ?? 'Kathmandu', 28) }}</p>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                        {{ ucfirst(str_replace('_', ' ', $disp->status ?? 'pending')) }}
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">{{ __('No shipments.') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Storage Space -->
        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-warehouse text-blue-500 text-sm"></i>
                    <h2 class="font-bold text-slate-900 text-sm">{{ __('Storage') }}</h2>
                </div>
                <a href="{{ route('my-requests.index') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">{{ __('All') }}</a>
            </div>
            <div class="space-y-2">
                @forelse($recentRequests ?? [] as $req)
                <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100 kwdc-item-row cursor-pointer flex items-center justify-between gap-3" data-record-url="{{ route('warehouse-requests.show', $req->id) }}">
                    <div>
                        <p class="font-bold text-slate-900 text-xs">{{ optional($req->warehouse)->name ?? ('Request #' . $req->id) }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $req->space_sqft ?? 0 }} sq.ft</p>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                        {{ ($req->status ?? 'pending') === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        {{ ucfirst($req->status ?? 'pending') }}
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">{{ __('No storage requests.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
    @elseif($role === 'driver')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Assigned Jobs -->
        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-route text-orange-500 text-sm"></i>
                    <h2 class="font-bold text-slate-900 text-sm">{{ __('Assigned Jobs') }}</h2>
                </div>
                <a href="{{ route('driver.jobs') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">{{ __('All') }}</a>
            </div>
            <div class="space-y-2">
                @forelse($myJobs ?? [] as $job)
                <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100 kwdc-item-row cursor-pointer flex items-center justify-between gap-3" data-record-url="{{ route('dispatch.show', $job->id) }}">
                    <div>
                        <p class="font-bold text-slate-900 text-xs">#{{ $job->tracking_id ?? $job->id }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ Str::limit($job->destination_address ?? 'Kathmandu', 28) }}</p>
                    </div>
                    <span class="text-xs font-bold text-emerald-600">NPR {{ number_format((float) ($job->driver_earning ?? 0)) }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">{{ __('No active jobs.') }}</p>
                @endforelse
            </div>
        </div>

        <!-- Available Pool -->
        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-hand-holding-dollar text-emerald-500 text-sm"></i>
                    <h2 class="font-bold text-slate-900 text-sm">{{ __('Marketplace') }}</h2>
                </div>
                <a href="{{ route('driver.available-jobs') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">{{ __('Browse') }}</a>
            </div>
            <div class="space-y-2">
                @forelse($availableJobs ?? [] as $job)
                <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100 kwdc-item-row cursor-pointer flex items-center justify-between gap-3" data-record-url="{{ route('dispatch.show', $job->id) }}">
                    <div>
                        <p class="font-bold text-slate-900 text-xs">Trip #{{ $job->id }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ Str::limit($job->pickup_address ?? 'Kathmandu', 25) }}</p>
                    </div>
                    <span class="text-xs font-bold text-emerald-600">NPR {{ number_format((float) ($job->driver_earning ?? $job->base_price ?? 0)) }}</span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">{{ __('No jobs available.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
    @elseif($role === 'equipment_owner')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-gear text-orange-500 text-sm"></i>
                    <h2 class="font-bold text-slate-900 text-sm">{{ __('Fleet') }}</h2>
                </div>
                <a href="{{ route('equipment.register') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">+ {{ __('Add') }}</a>
            </div>
            <div class="space-y-2">
                @forelse($myEquipment ?? [] as $eq)
                <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100 kwdc-item-row cursor-pointer flex items-center justify-between gap-3">
                    <div>
                        <p class="font-bold text-slate-900 text-xs">{{ $eq->name }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $eq->type ?? 'Machinery' }}</p>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                        {{ $eq->status === 'available' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                        {{ ucfirst($eq->status) }}
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">{{ __('No equipment.') }}</p>
                @endforelse
            </div>
        </div>
        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-blue-500 text-sm"></i>
                    <h2 class="font-bold text-slate-900 text-sm">{{ __('Requests') }}</h2>
                </div>
                <a href="{{ route('equipment.jobs.requests') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">{{ __('All') }}</a>
            </div>
            <div class="space-y-2">
                @forelse($recentRequests ?? [] as $req)
                <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100 kwdc-item-row cursor-pointer flex items-center justify-between gap-3">
                    <div>
                        <p class="font-bold text-slate-900 text-xs">Request #{{ $req->id }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ optional($req->user)->name ?? 'Client' }}</p>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200">
                        {{ ucfirst($req->status ?? 'pending') }}
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">{{ __('No requests.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
    @elseif($role === 'security_agency')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-user-shield text-blue-500 text-sm"></i>
                    <h2 class="font-bold text-slate-900 text-sm">{{ __('Guards') }}</h2>
                </div>
                <a href="{{ route('security.dashboard') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">{{ __('Manage') }}</a>
            </div>
            <div class="space-y-2">
                @forelse($securityPersonnel ?? [] as $guard)
                <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100 kwdc-item-row cursor-pointer flex items-center justify-between gap-3">
                    <div>
                        <p class="font-bold text-slate-900 text-xs">{{ $guard->name }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $guard->badge_number ?? 'Officer' }}</p>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ __('On Duty') }}
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">{{ __('No personnel.') }}</p>
                @endforelse
            </div>
        </div>
        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-shield-halved text-emerald-500 text-sm"></i>
                    <h2 class="font-bold text-slate-900 text-sm">{{ __('Cargo') }}</h2>
                </div>
                <a href="{{ route('security.dashboard') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">{{ __('All') }}</a>
            </div>
            <div class="space-y-2">
                @forelse($guardGoods ?? [] as $item)
                <div class="p-3 rounded-xl bg-slate-50/70 border border-slate-100 kwdc-item-row cursor-pointer flex items-center justify-between gap-3">
                    <div>
                        <p class="font-bold text-slate-900 text-xs">{{ $item->item_name ?? 'Secured Goods' }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $item->facility ?? 'Facility 1' }}</p>
                    </div>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200">
                        Secured
                    </span>
                </div>
                @empty
                <p class="text-xs text-slate-400 py-4 text-center">No cargo items.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function() {
    let primaryChartInstance = null;
    let activityChartInstance = null;

    const chartDatasets = {
        '7d': {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            values: [18000, 24000, 31000, 28000, 39000, 48000, 42000],
            subtitle: 'Last 7 days'
        },
        '30d': {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            values: [98000, 124000, 148000, 162000],
            subtitle: 'Last 30 days'
        },
        '90d': {
            labels: ['Jul', 'Aug', 'Sep'],
            values: [380000, 440000, 532000],
            subtitle: 'Last 90 days (Q3)'
        }
    };

    window.setChartRange = function(range) {
        const data = chartDatasets[range];
        if (!data || !primaryChartInstance) return;

        ['7d', '30d', '90d'].forEach(r => {
            const btn = document.getElementById('chartBtn' + r);
            if (btn) {
                if (r === range) {
                    btn.className = 'px-2.5 py-1 rounded-lg bg-slate-900 text-white text-xs font-bold transition shadow-2xs';
                } else {
                    btn.className = 'px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-medium transition';
                }
            }
        });

        const subEl = document.getElementById('primaryChartSubtitle');
        if (subEl) subEl.textContent = data.subtitle;

        primaryChartInstance.data.labels = data.labels;
        primaryChartInstance.data.datasets[0].data = data.values;
        primaryChartInstance.update();
    };

    function initDashboardCharts() {
        if (typeof Chart === 'undefined') {
            console.warn('[KWDC Dashboard] Chart.js not loaded yet, retrying...');
            setTimeout(initDashboardCharts, 100);
            return;
        }

        // Primary Chart (Smooth Line with Subtle Gradient)
        const primaryCanvas = document.getElementById('dashboardPrimaryChart');
        if (primaryCanvas) {
            const existingPrimary = Chart.getChart(primaryCanvas);
            if (existingPrimary) existingPrimary.destroy();

            const ctx = primaryCanvas.getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 220);
            gradient.addColorStop(0, 'rgba(217, 107, 67, 0.22)');
            gradient.addColorStop(1, 'rgba(217, 107, 67, 0.00)');

            primaryChartInstance = new Chart(primaryCanvas, {
                type: 'line',
                data: {
                    labels: chartDatasets['7d'].labels,
                    datasets: [{
                        label: 'Revenue',
                        data: chartDatasets['7d'].values,
                        backgroundColor: gradient,
                        borderColor: '#D96B43',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#D96B43',
                        pointBorderWidth: 2,
                        pointRadius: 3.5,
                        pointHoverRadius: 5.5,
                        tension: 0.35,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(36, 32, 29, 0.94)',
                            padding: 10,
                            cornerRadius: 10,
                            titleFont: { weight: 'bold', size: 11 },
                            callbacks: {
                                label: function(context) {
                                    return ' NPR ' + Number(context.parsed.y).toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#8A8379', font: { size: 11, weight: '600' } }
                        },
                        y: {
                            beginAtZero: true,
                            grid: { color: '#F2ECE4' },
                            ticks: { color: '#8A8379', font: { size: 11 } }
                        }
                    }
                }
            });
        }

        // Doughnut Chart (Capacity Distribution)
        const activityCanvas = document.getElementById('dashboardActivityChart');
        if (activityCanvas) {
            const existingActivity = Chart.getChart(activityCanvas);
            if (existingActivity) existingActivity.destroy();

            activityChartInstance = new Chart(activityCanvas, {
                type: 'doughnut',
                data: {
                    labels: ['Leased (78%)', 'Free (22%)'],
                    datasets: [{
                        data: [78, 22],
                        backgroundColor: ['#D96B43', '#E8E2D8'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '74%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 6,
                                padding: 14,
                                font: { size: 11, weight: '600' },
                                color: '#64748b'
                            }
                        }
                    }
                }
            });
        }
    }

    const dashboardStats = @json($stats ?? []);

    window.refreshDashboardAiBrief = async function(force = false) {
        const loading = document.getElementById('aiBriefLoading');
        const content = document.getElementById('aiBriefContent');
        const icon = document.getElementById('aiBriefRefreshIcon');
        const cacheKey = 'kwdc_dashboard_brief_' + (document.querySelector('meta[name="user-id"]')?.content || 'user');

        if (!force) {
            try {
                const cached = JSON.parse(localStorage.getItem(cacheKey) || 'null');
                if (cached && (Date.now() - cached.timestamp < 30 * 60 * 1000)) {
                    renderBrief(cached.data);
                    return;
                }
            } catch (e) {}
        }

        if (loading) loading.classList.remove('hidden');
        if (content) content.classList.add('opacity-40');
        if (icon) icon.classList.add('fa-spin');

        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const response = await fetch('/ai/dashboard-brief', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfMeta ? csrfMeta.content : '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ stats: dashboardStats })
            });

            const res = await response.json();
            if (res.success && res.brief) {
                renderBrief(res.brief);
                try {
                    localStorage.setItem(cacheKey, JSON.stringify({ timestamp: Date.now(), data: res.brief }));
                } catch (e) {}
            }
        } catch (e) {
            console.warn('Could not fetch daily brief', e);
        } finally {
            if (loading) loading.classList.add('hidden');
            if (content) content.classList.remove('opacity-40');
            if (icon) icon.classList.remove('fa-spin');
        }
    };

    function getCategoryForBullet(text, idx) {
        const t = (text || '').toLowerCase();
        if (t.includes('dispatch') || t.includes('delivery') || t.includes('order') || t.includes('transit')) {
            return { tag: 'DISPATCH', bg: 'bg-blue-50 text-blue-700 border-blue-200/80', icon: 'fa-truck-fast' };
        }
        if (t.includes('warehouse') || t.includes('storage') || t.includes('capacity') || t.includes('occupancy') || t.includes('facility')) {
            return { tag: 'CAPACITY', bg: 'bg-emerald-50 text-emerald-700 border-emerald-200/80', icon: 'fa-warehouse' };
        }
        if (t.includes('driver') || t.includes('vehicle') || t.includes('fleet') || t.includes('asset') || t.includes('truck')) {
            return { tag: 'FLEET', bg: 'bg-amber-50 text-amber-700 border-amber-200/80', icon: 'fa-id-card' };
        }
        const defaults = [
            { tag: 'DISPATCH', bg: 'bg-blue-50 text-blue-700 border-blue-200/80', icon: 'fa-truck-fast' },
            { tag: 'CAPACITY', bg: 'bg-emerald-50 text-emerald-700 border-emerald-200/80', icon: 'fa-warehouse' },
            { tag: 'FLEET', bg: 'bg-amber-50 text-amber-700 border-amber-200/80', icon: 'fa-id-card' }
        ];
        return defaults[idx % defaults.length];
    }

    function renderBrief(b) {
        if (!b) return;
        if (b.greeting) {
            const gEl = document.getElementById('aiBriefGreeting');
            if (gEl) gEl.textContent = b.greeting;
        }
        if (b.key_metrics_summary) {
            const sEl = document.getElementById('aiBriefSummary');
            if (sEl) sEl.textContent = b.key_metrics_summary;
        }
        if (Array.isArray(b.operational_bullets) && b.operational_bullets.length) {
            const bEl = document.getElementById('aiBriefBullets');
            if (bEl) {
                bEl.innerHTML = b.operational_bullets.map((text, idx) => {
                    const cat = getCategoryForBullet(text, idx);
                    return `
                        <div class="kwdc-insight-row">
                            <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded border ${cat.bg} flex-shrink-0 mt-0.5">
                                <i class="fas ${cat.icon} text-[9px]"></i> ${cat.tag}
                            </span>
                            <span class="text-xs text-slate-700 font-medium leading-relaxed flex-1">${text}</span>
                        </div>
                    `;
                }).join('');
            }
        }
        if (b.recommended_action) {
            const aEl = document.getElementById('aiBriefAction');
            if (aEl) aEl.textContent = b.recommended_action;
        }
    }

    function runDashboardInit() {
        initDashboardCharts();
        window.refreshDashboardAiBrief(false);
    }

    if (document.readyState !== 'loading') {
        runDashboardInit();
    } else {
        document.addEventListener('DOMContentLoaded', runDashboardInit);
    }
    document.addEventListener('kwdc:page-loaded', runDashboardInit);
})();
</script>
@endpush
