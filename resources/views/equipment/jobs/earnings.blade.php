@extends('layouts.app')

@section('title', 'My Earnings')
@section('header', 'Earnings Overview')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl shadow-md p-6 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm opacity-90">Total Earnings</p>
                    <p class="text-2xl font-bold">रू {{ number_format($stats['total_earnings'] ?? 0, 2) }}</p>
                </div>
                <i class="fas fa-rupee-sign text-3xl opacity-50"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-md p-6 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm opacity-90">This Month</p>
                    <p class="text-2xl font-bold">रू {{ number_format($stats['monthly_earnings'] ?? 0, 2) }}</p>
                </div>
                <i class="fas fa-calendar-alt text-3xl opacity-50"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl shadow-md p-6 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm opacity-90">This Week</p>
                    <p class="text-2xl font-bold">रू {{ number_format($stats['weekly_earnings'] ?? 0, 2) }}</p>
                </div>
                <i class="fas fa-chart-line text-3xl opacity-50"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl shadow-md p-6 text-white">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm opacity-90">Total Jobs</p>
                    <p class="text-2xl font-bold">{{ $stats['total_jobs'] ?? 0 }}</p>
                </div>
                <i class="fas fa-tasks text-3xl opacity-50"></i>
            </div>
        </div>
    </div>
    
    <!-- Chart Section -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-lg font-bold mb-4">Earnings Overview</h3>
        <canvas id="earningsChart" height="100"></canvas>
    </div>
    
    <!-- Recent Earnings Table -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Recent Earnings</h3>
            <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-sm">
                <i class="fas fa-history mr-1"></i> Last 10 Transactions
            </span>
        </div>
        
        @if(isset($recentEarnings) && $recentEarnings->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr class="border-b">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job ID</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Equipment</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($recentEarnings as $earning)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-sm font-medium">#{{ $earning->id }}</td>
                            <td class="px-4 py-3 text-sm">
                                {{ $earning->equipment->name ?? 'N/A' }}
                                <div class="text-xs text-gray-500">{{ $earning->equipment->type ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $earning->client->name ?? 'N/A' }}
                                <div class="text-xs text-gray-500">{{ $earning->client->phone ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ \Carbon\Carbon::parse($earning->start_date)->format('M d') }} - 
                                {{ \Carbon\Carbon::parse($earning->end_date)->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="font-semibold text-green-600">
                                    रू {{ number_format($earning->price ?? 0, 2) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                {{ $earning->completed_at ? \Carbon\Carbon::parse($earning->completed_at)->format('M d, Y') : 'N/A' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-chart-line text-gray-300 text-5xl mb-4"></i>
                <p class="text-gray-500 text-lg">No earnings recorded yet</p>
                <p class="text-gray-400 text-sm mt-2">When you complete jobs, earnings will appear here</p>
            </div>
        @endif
    </div>
    
    <!-- Monthly Breakdown -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h3 class="text-lg font-bold mb-4">Monthly Breakdown</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="border-b">
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jobs Completed</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Earnings</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Average per Job</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php
                        $monthlyData = [];
                        if(isset($recentEarnings) && $recentEarnings->count() > 0) {
                            foreach($recentEarnings as $earning) {
                                $month = \Carbon\Carbon::parse($earning->completed_at)->format('F Y');
                                if(!isset($monthlyData[$month])) {
                                    $monthlyData[$month] = ['count' => 0, 'total' => 0];
                                }
                                $monthlyData[$month]['count']++;
                                $monthlyData[$month]['total'] += ($earning->price ?? 0);
                            }
                        }
                    @endphp
                    
                    @forelse($monthlyData as $month => $data)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">{{ $month }}</td>
                        <td class="px-4 py-3 text-sm">{{ $data['count'] }}</td>
                        <td class="px-4 py-3 text-sm font-semibold text-green-600">रू {{ number_format($data['total'], 2) }}</td>
                        <td class="px-4 py-3 text-sm">रू {{ number_format($data['total'] / $data['count'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-8 text-gray-500">No monthly data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sample chart data - replace with actual data from controller
        const ctx = document.getElementById('earningsChart').getContext('2d');
        
        @php
            $chartLabels = [];
            $chartData = [];
            if(isset($recentEarnings) && $recentEarnings->count() > 0) {
                $last7Jobs = $recentEarnings->take(7);
                foreach($last7Jobs as $job) {
                    $chartLabels[] = '#' . $job->id;
                    $chartData[] = $job->price ?? 0;
                }
            } else {
                $chartLabels = ['No Data'];
                $chartData = [0];
            }
        @endphp
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Earnings (रू)',
                    data: {!! json_encode($chartData) !!},
                    backgroundColor: 'rgba(245, 158, 11, 0.5)',
                    borderColor: 'rgba(245, 158, 11, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'रू ' + context.raw.toLocaleString('en-IN');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'रू ' + value.toLocaleString('en-IN');
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection