@extends('layouts.app')

@section('title', 'Analytics Dashboard')
@section('header', 'Analytics Dashboard')

@push('styles')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    .chart-container { position: relative; height: 250px; width: 100%; }
</style>
@endpush

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <!-- Revenue Chart -->
    <div class="bg-white rounded-xl shadow p-4 md:col-span-3">
        <h4 class="font-bold text-gray-800 mb-4"><i class="fas fa-chart-line text-orange-500 mr-2"></i>Monthly Revenue (Last 12 Months)</h4>
        <div class="chart-container">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Dispatch Volume Chart -->
    <div class="bg-white rounded-xl shadow p-4 md:col-span-2">
        <h4 class="font-bold text-gray-800 mb-4"><i class="fas fa-chart-bar text-blue-500 mr-2"></i>Dispatch Volume</h4>
        <div class="chart-container">
            <canvas id="dispatchChart"></canvas>
        </div>
    </div>

    <!-- Status Distribution Chart -->
    <div class="bg-white rounded-xl shadow p-4 md:col-span-1">
        <h4 class="font-bold text-gray-800 mb-4"><i class="fas fa-chart-pie text-purple-500 mr-2"></i>Current Status</h4>
        <div class="chart-container">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<!-- Top Drivers Table -->
<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="px-4 py-3 border-b flex justify-between items-center">
        <h4 class="font-bold text-gray-800"><i class="fas fa-trophy text-yellow-500 mr-2"></i>Top 5 Drivers by Earnings</h4>
    </div>
    <div class="p-0">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-6 py-3">Rank</th>
                    <th class="px-6 py-3">Driver Name</th>
                    <th class="px-6 py-3 text-right">Total Earned</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($topDrivers as $index => $driver)
                <tr>
                    <td class="px-6 py-4 font-bold text-gray-700">#{{ $index + 1 }}</td>
                    <td class="px-6 py-4 font-medium text-gray-900">{{ $driver->name }}</td>
                    <td class="px-6 py-4 text-right font-medium text-orange-600">रू {{ number_format($driver->dispatch_orders_sum_driver_earning ?? 0, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">No driver data available yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Revenue Chart
    const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctxRevenue, {
        type: 'line',
        data: {
            labels: @json($months),
            datasets: [{
                label: 'Revenue (NPR)',
                data: @json($revenues),
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#f59e0b',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: function(value) { return 'रू ' + value.toLocaleString(); } }
                }
            }
        }
    });

    // 2. Dispatch Volume Chart
    const ctxDispatch = document.getElementById('dispatchChart').getContext('2d');
    new Chart(ctxDispatch, {
        type: 'bar',
        data: {
            labels: @json($months),
            datasets: [{
                label: 'Total Dispatches',
                data: @json($dispatchCounts),
                backgroundColor: 'rgba(59, 130, 246, 0.6)',
                borderColor: '#3b82f6',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    // 3. Status Distribution Chart
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    const statusLabels = @json(array_keys($statusCounts));
    const statusData = @json(array_values($statusCounts));
    const statusColors = @json(array_values($statusColors));

    new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: statusLabels.map(label => label.replace(/_/g, ' ').toUpperCase()),
            datasets: [{
                data: statusData,
                backgroundColor: statusColors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 10, usePointStyle: true, font: { size: 11 } } }
            }
        }
    });
});
</script>
@endpush