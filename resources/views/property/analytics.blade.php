@extends('layouts.app')

@section('title', 'Property Analytics')
@section('header', 'Property Analytics')

@push('styles')
<style>
    .kwdc-kpi-val {
        font-feature-settings: "tnum";
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.025em;
    }
    .kwdc-glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.03);
        transition: all 0.2s ease;
    }
    .kwdc-glass-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 8px 25px -4px rgba(15, 23, 42, 0.06);
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header Banner -->
    <div class="kwdc-glass-card p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-orange-50 text-orange-600 border border-orange-200/80 mb-2">
                <i class="fas fa-chart-pie"></i> Analytics
            </span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Property Performance
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Occupancy, revenue yield, and tenant inquiries.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('property.approved') }}" class="px-4 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-xs font-bold text-slate-700 transition">
                Properties
            </a>
            <a href="{{ route('property.requests.index') }}" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold shadow-sm transition">
                Requests
            </a>
        </div>
    </div>

    <!-- 4 High-Impact KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Properties</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 kwdc-kpi-val">{{ $stats['total_properties'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base flex-shrink-0">
                    <i class="fas fa-warehouse"></i>
                </div>
            </div>
            <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>{{ $stats['approved_properties'] ?? 0 }} active</span>
                <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Live</span>
            </div>
        </div>

        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Approved</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 kwdc-kpi-val">{{ $stats['approved_properties'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base flex-shrink-0">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Verified facilities</span>
                <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">100%</span>
            </div>
        </div>

        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Inquiries</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 kwdc-kpi-val">{{ $stats['total_requests'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base flex-shrink-0">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
            <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>{{ $stats['pending_requests'] ?? 0 }} pending</span>
                <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Queue</span>
            </div>
        </div>

        <div class="kwdc-glass-card p-5 space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Revenue Yield</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-slate-900 kwdc-kpi-val">रू {{ number_format((float) ($stats['total_revenue'] ?? 0), 0) }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-base flex-shrink-0">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
            <div class="pt-2.5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span>Commercial yield</span>
                <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">↑ 8.5%</span>
            </div>
        </div>
    </div>

    <!-- Chart & Requests Overview Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Revenue Trend Chart (Fixed Container - Prevents infinite Chart.js loop) -->
        <div class="lg:col-span-2 kwdc-glass-card p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Revenue Velocity</h2>
                    <p class="text-[11px] text-slate-400">Monthly commercial lease yield</p>
                </div>
                <span class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-bold">Monthly</span>
            </div>

            <!-- Constrained Chart Container with fixed height -->
            <div style="position: relative; height: 260px; width: 100%;">
                @if(isset($monthlyRevenue) && count($monthlyRevenue) > 0)
                    <canvas id="revenueChart"></canvas>
                @else
                    <div class="h-full flex items-center justify-center text-xs text-slate-400">
                        No revenue telemetry recorded yet.
                    </div>
                @endif
            </div>
        </div>

        <!-- Space Inquiries Summary -->
        <div class="kwdc-glass-card p-6 space-y-4">
            <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-900">Inquiry Breakdown</h2>
                <span class="text-xs font-bold text-orange-600">{{ $stats['total_requests'] ?? 0 }} total</span>
            </div>

            <div class="space-y-3">
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-600 font-medium">Approved Contracts</span>
                    <span class="font-extrabold text-emerald-600">{{ $stats['approved_requests'] ?? 0 }}</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-600 font-medium">Pending Review</span>
                    <span class="font-extrabold text-amber-600">{{ $stats['pending_requests'] ?? 0 }}</span>
                </div>
                <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-600 font-medium">Total Volume</span>
                    <span class="font-extrabold text-slate-900">{{ number_format($stats['total_properties'] ?? 0) }} Hubs</span>
                </div>
            </div>

            <a href="{{ route('property.requests.index') }}" class="w-full py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs flex items-center justify-center transition">
                Manage Inquiries &rarr;
            </a>
        </div>
    </div>

    <!-- Recent Warehouse Requests Table Card -->
    <div class="kwdc-glass-card p-6 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <i class="fas fa-clipboard-list text-orange-500 text-sm"></i>
                <h2 class="font-bold text-slate-900 text-sm">Recent Tenant Requests</h2>
            </div>
            <a href="{{ route('property.requests.index') }}" class="text-xs font-bold text-orange-600 hover:text-orange-700">All Requests</a>
        </div>

        @if(isset($recentRequests) && $recentRequests->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 uppercase text-[10px] tracking-wider">
                            <th class="pb-2.5 font-bold">Client</th>
                            <th class="pb-2.5 font-bold">Warehouse</th>
                            <th class="pb-2.5 font-bold">Date</th>
                            <th class="pb-2.5 font-bold">Status</th>
                            <th class="pb-2.5 font-bold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recentRequests as $request)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 font-bold text-slate-900">{{ $request->client->name ?? 'Client' }}</td>
                            <td class="py-3 text-slate-600">{{ $request->warehouse->name ?? 'Hub' }}</td>
                            <td class="py-3 text-slate-500">{{ $request->created_at->format('M d, Y') }}</td>
                            <td class="py-3">
                                @if($request->status == 'pending')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Pending</span>
                                @elseif($request->status == 'approved')
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Approved</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">Rejected</span>
                                @endif
                            </td>
                            <td class="py-3 text-right">
                                <a href="{{ route('property.requests.index') }}" class="text-orange-600 hover:text-orange-700 font-bold">View &rarr;</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="py-8 text-center text-slate-400 text-xs">
                No recent warehouse requests.
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
@if(isset($monthlyRevenue) && count($monthlyRevenue) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('revenueChart');
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    const monthlyData = @json($monthlyRevenue);
    
    const gradient = ctx.createLinearGradient(0, 0, 0, 240);
    gradient.addColorStop(0, 'rgba(249, 115, 22, 0.22)');
    gradient.addColorStop(1, 'rgba(249, 115, 22, 0.00)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthlyData.map(item => item.month),
            datasets: [{
                label: 'Revenue (NPR)',
                data: monthlyData.map(item => item.revenue),
                backgroundColor: gradient,
                borderColor: '#f97316',
                borderWidth: 2.5,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#f97316',
                pointBorderWidth: 2,
                pointRadius: 4,
                tension: 0.35,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15, 23, 42, 0.90)',
                    padding: 10,
                    cornerRadius: 10,
                    callbacks: {
                        label: function(context) {
                            return ' रू ' + Number(context.parsed.y).toLocaleString();
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 11, weight: '600' } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11 },
                        callback: function(value) {
                            return 'रू ' + Number(value).toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endif
@endpush
