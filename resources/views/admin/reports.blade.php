@extends('layouts.app')

@section('title', 'Analytics & Reports | Admin Hub')
@section('header', 'Analytics & Reports')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[#E8E2D8]">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-[#D96B43]/10 text-[#D96B43] border border-[#D96B43]/20">
                    Executive Intelligence
                </span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#24201D] tracking-tight" style="font-family: 'Newsreader', Georgia, serif;">
                Operational Reports & Analytics
            </h1>
            <p class="text-sm text-[#645D56] mt-0.5">High-level metrics across storage facilities, transportation capacity, and carrier engagement.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.analytics.predictive') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-[#24201D] bg-[#FFFDF9] border border-[#E8E2D8] hover:bg-[#FAF8F5] transition shadow-2xs">
                <i class="fas fa-chart-line text-xs text-[#D96B43]"></i>
                <span>Predictive Engine</span>
            </a>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-[#24201D] bg-[#FFFDF9] border border-[#E8E2D8] hover:bg-[#FAF8F5] transition shadow-2xs">
                <i class="fas fa-gauge-high text-xs text-[#645D56]"></i>
                <span>Dashboard</span>
            </a>
        </div>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold text-[#8C827A] uppercase tracking-wider">Total Warehouses</p>
                    <p class="text-2xl sm:text-3xl font-serif font-bold text-[#24201D]">{{ $totalWarehouses ?? \App\Models\Warehouse::count() }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: rgba(217, 107, 67, 0.1); color: #D96B43;">
                    <i class="fas fa-warehouse"></i>
                </div>
            </div>
            <div class="pt-3 mt-2 border-t border-[#E8E2D8]/50 flex items-center justify-between text-xs text-[#645D56]">
                <span>Registered Hubs</span>
                <i class="fas fa-arrow-up-right text-[#8C827A]"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold text-[#8C827A] uppercase tracking-wider">Approved Hubs</p>
                    <p class="text-2xl sm:text-3xl font-serif font-bold text-[#24201D]">{{ $approvedWarehouses ?? \App\Models\Warehouse::where('status', 'approved')->count() }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: rgba(46, 107, 79, 0.1); color: #2E6B4F;">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="pt-3 mt-2 border-t border-[#E8E2D8]/50 flex items-center justify-between text-xs text-[#645D56]">
                <span>Active & Lease-Ready</span>
                <i class="fas fa-arrow-up-right text-[#8C827A]"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold text-[#8C827A] uppercase tracking-wider">Fleet Vehicles</p>
                    <p class="text-2xl sm:text-3xl font-serif font-bold text-[#24201D]">{{ $totalVehicles ?? \App\Models\Vehicle::count() }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: rgba(79, 107, 148, 0.1); color: #4F6B94;">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
            <div class="pt-3 mt-2 border-t border-[#E8E2D8]/50 flex items-center justify-between text-xs text-[#645D56]">
                <span>Carrier Assets</span>
                <i class="fas fa-arrow-up-right text-[#8C827A]"></i>
            </div>
        </div>

        <div class="kwdc-kpi-card">
            <div class="flex items-start justify-between gap-3">
                <div class="space-y-1">
                    <p class="text-[11px] font-bold text-[#8C827A] uppercase tracking-wider">Portal Users</p>
                    <p class="text-2xl sm:text-3xl font-serif font-bold text-[#24201D]">{{ $totalUsers ?? \App\Models\User::count() }}</p>
                </div>
                <div class="kwdc-kpi-icon" style="background: rgba(184, 115, 42, 0.1); color: #B8732A;">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="pt-3 mt-2 border-t border-[#E8E2D8]/50 flex items-center justify-between text-xs text-[#645D56]">
                <span>Clients & Partners</span>
                <i class="fas fa-arrow-up-right text-[#8C827A]"></i>
            </div>
        </div>
    </div>

    <!-- Monthly Orders Table -->
    <div class="bg-[#FFFDF9] border border-[#E8E2D8] rounded-2xl p-6 shadow-2xs space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-base font-serif font-bold text-[#24201D]" style="font-family: 'Newsreader', Georgia, serif;">
                Monthly Dispatch Cadence
            </h3>
            <span class="text-xs text-[#8C827A]">Historical freight activity</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-[#E8E2D8] text-left text-[11px] font-bold text-[#8C827A] uppercase tracking-wider">
                        <th class="py-3 px-4">Billing Month</th>
                        <th class="py-3 px-4 text-right">Orders Processed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E2D8]/60">
                    @forelse($monthlyStats ?? [] as $stat)
                    <tr class="hover:bg-[#FAF8F5] transition">
                        <td class="py-3 px-4 font-mono font-medium text-[#24201D]">{{ $stat->month }}</td>
                        <td class="py-3 px-4 text-right font-bold text-[#D96B43]">{{ $stat->count }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="py-8 text-center text-[#8C827A]">No historical dispatch records found for this interval.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Activity Split Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Recent Warehouses -->
        <div class="bg-[#FFFDF9] border border-[#E8E2D8] rounded-2xl p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-[#E8E2D8] pb-3">
                <h3 class="text-base font-serif font-bold text-[#24201D]" style="font-family: 'Newsreader', Georgia, serif;">
                    Newly Added Warehouses
                </h3>
                <a href="{{ route('admin.all-warehouses') }}" class="text-xs text-[#D96B43] font-semibold hover:underline">
                    View All
                </a>
            </div>
            <ul class="space-y-2.5">
                @forelse($recentWarehouses ?? \App\Models\Warehouse::latest()->take(5)->get() as $warehouse)
                <li class="flex items-center justify-between p-3 bg-[#FAF8F5] rounded-xl border border-[#E8E2D8]/60 text-xs">
                    <div>
                        <strong class="text-[#24201D] block truncate max-w-xs">{{ $warehouse->name }}</strong>
                        <span class="text-[10px] text-[#8C827A]">{{ $warehouse->location ?? 'Nepal' }}</span>
                    </div>
                    <span class="text-[11px] text-[#8C827A]">{{ $warehouse->created_at->diffForHumans() }}</span>
                </li>
                @empty
                <li class="text-center py-6 text-[#8C827A] text-xs">No warehouses recorded yet.</li>
                @endforelse
            </ul>
        </div>

        <!-- Recent Orders -->
        <div class="bg-[#FFFDF9] border border-[#E8E2D8] rounded-2xl p-6 shadow-2xs space-y-4">
            <div class="flex items-center justify-between border-b border-[#E8E2D8] pb-3">
                <h3 class="text-base font-serif font-bold text-[#24201D]" style="font-family: 'Newsreader', Georgia, serif;">
                    Recent Dispatch Orders
                </h3>
                <a href="{{ route('admin.dispatch') }}" class="text-xs text-[#D96B43] font-semibold hover:underline">
                    View All
                </a>
            </div>
            <ul class="space-y-2.5">
                @forelse($recentOrders ?? \App\Models\DispatchOrder::latest()->take(5)->get() as $order)
                <li class="flex items-center justify-between p-3 bg-[#FAF8F5] rounded-xl border border-[#E8E2D8]/60 text-xs">
                    <div>
                        <strong class="font-mono text-[#24201D] block">#{{ $order->dispatch_number ?? ('DSP-' . $order->id) }}</strong>
                        <span class="text-[10px] text-[#8C827A] truncate max-w-xs block">{{ $order->pickup_address ?? 'Kathmandu' }}</span>
                    </div>
                    <span class="text-[11px] text-[#8C827A]">{{ $order->created_at->diffForHumans() }}</span>
                </li>
                @empty
                <li class="text-center py-6 text-[#8C827A] text-xs">No dispatch orders recorded yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection