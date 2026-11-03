@extends('layouts.app')

@section('title', 'Predictive Warehouse Analytics')
@section('header', 'Predictive Warehouse Analytics')

@push('styles')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    .chart-container { position: relative; height: 100%; width: 100%; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Warehouse Capacity & Predictions</h1>
            <p class="text-gray-500 mt-1">AI-driven analytics to visualize warehouse space and forecast demand.</p>
        </div>
        <div>
            <span class="bg-blue-100 text-blue-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">
                <i class="fas fa-robot mr-1"></i> AI Generated
            </span>
        </div>
    </div>

    <!-- Global Stat Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-blue-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Capacity</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalCapacity) }} sq ft</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-orange-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Occupied Space</p>
            <p class="text-2xl font-bold text-orange-600">{{ number_format($totalOccupied) }} sq ft</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-green-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Available Space</p>
            <p class="text-2xl font-bold text-green-600">{{ number_format($totalAvailable) }} sq ft</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 border-l-4 border-purple-500">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Overall Utilization</p>
            <p class="text-2xl font-bold text-purple-600">{{ $utilizationPercent }}%</p>
        </div>
    </div>

    <!-- Visualization Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Donut Chart -->
        <div class="bg-white rounded-xl shadow-md p-6 lg:col-span-1">
            <h4 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-chart-pie text-purple-500 mr-2"></i>Overall Space Utilization</h4>
            <div class="chart-container" style="height: 250px;">
                <canvas id="utilizationChart"></canvas>
            </div>
            <div class="flex justify-center mt-3 text-xs text-gray-500">
                <span class="mr-3"><span class="inline-block w-3 h-3 bg-orange-500 rounded-full mr-1"></span> Occupied</span>
                <span><span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-1"></span> Available</span>
            </div>
        </div>

        <!-- Bar Chart -->
        <div class="bg-white rounded-xl shadow-md p-6 lg:col-span-2">
            <h4 class="text-lg font-bold text-gray-800 mb-4"><i class="fas fa-chart-bar text-blue-500 mr-2"></i>Warehouse Capacity vs. Occupied</h4>
            <div class="chart-container" style="height: 250px;">
                <canvas id="capacityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- AI Predictions Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h4 class="text-lg font-bold text-gray-800"><i class="fas fa-robot text-blue-500 mr-2"></i>AI Predictive Insights (30-Day Forecast)</h4>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                    <tr>
                        <th scope="col" class="px-6 py-3">Warehouse Name</th>
                        <th scope="col" class="px-6 py-3 text-center">Current Occupancy</th>
                        <th scope="col" class="px-6 py-3 text-center">Days Until Full</th>
                        <th scope="col" class="px-6 py-3 text-center">Status</th>
                        <th scope="col" class="px-6 py-3 w-1/3">AI Suggestion</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($predictions as $prediction)
                    @php
                        // Find the matching warehouse stat to get the current occupied %
                        $matchedStat = collect($stats)->firstWhere('name', $prediction['warehouse_name'] ?? '');
                        $currentOccupiedPercent = $matchedStat ? round(($matchedStat['occupied_sqft'] / max(1, $matchedStat['current_area_sqft'])) * 100) : 0;
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            <i class="fas fa-warehouse text-gray-400 mr-2"></i>{{ $prediction['warehouse_name'] }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center">
                                <span class="mr-2 font-bold">{{ $currentOccupiedPercent }}%</span>
                                <div class="w-24 bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $currentOccupiedPercent }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center font-bold">
                            {{ $prediction['predicted_days_until_full'] }} days
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($prediction['predicted_days_until_full'] <= 10)
                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Critical
                                </span>
                            @elseif($prediction['predicted_days_until_full'] <= 30)
                                <span class="bg-orange-100 text-orange-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                    <i class="fas fa-clock mr-1"></i> Warning
                                </span>
                            @else
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                                    <i class="fas fa-check-circle mr-1"></i> Safe
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 italic border-l-4 border-blue-300 bg-blue-50">
                            <i class="fas fa-robot text-blue-500 mr-2"></i> {{ $prediction['suggestion'] }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <i class="fas fa-database text-4xl mb-3 text-gray-300"></i>
                            <p>No active warehouse data available for AI to analyze.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-gray-50 border-t border-gray-200 text-xs text-gray-500">
            <i class="fas fa-info-circle mr-1"></i> Predictions are based on incoming request volume and estimated growth rates analyzed by the internal AI engine.
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Overall Utilization Donut Chart
    const ctxDonut = document.getElementById('utilizationChart').getContext('2d');
    const totalOccupied = {{ $totalOccupied }};
    const totalAvailable = {{ $totalAvailable }};
    
    new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
            labels: ['Occupied', 'Available'],
            datasets: [{
                data: [totalOccupied, totalAvailable],
                backgroundColor: ['#f97316', '#22c55e'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ' + context.parsed.toLocaleString() + ' sq ft';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });

    // 2. Warehouse Capacity vs Occupied Bar Chart
    const ctxBar = document.getElementById('capacityChart').getContext('2d');
    const labels = @json($chartLabels);
    const capacityData = @json($chartCapacity);
    const occupiedData = @json($chartOccupied);

    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Capacity (sq ft)',
                    data: capacityData,
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Occupied Space (sq ft)',
                    data: occupiedData,
                    backgroundColor: 'rgba(249, 115, 22, 0.7)',
                    borderColor: 'rgba(249, 115, 22, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: function(value) { return value.toLocaleString() + ' sq ft'; } }
                }
            },
            plugins: {
                legend: { position: 'top' }
            }
        }
    });
});
</script>
@endpush