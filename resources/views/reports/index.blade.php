@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('header', 'Reports & Analytics')

@push('styles')
<style>
    .stat-card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 12px;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
    .activity-timeline {
        position: relative;
        padding-left: 30px;
    }
    .activity-timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }
    .activity-item {
        position: relative;
        padding-bottom: 20px;
    }
    .activity-item::before {
        content: '';
        position: absolute;
        left: -26px;
        top: 4px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #f59e0b;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .activity-item .time {
        font-size: 12px;
        color: #6b7280;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-0">
    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-gradient-to-r from-blue-500 to-blue-600 text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 opacity-75">Total Revenue</h6>
                        <h2 class="mb-0">रू {{ number_format($summary['total_revenue'], 0) }}</h2>
                    </div>
                    <div class="stat-icon bg-white bg-opacity-25 text-white">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-gradient-to-r from-green-500 to-green-600 text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 opacity-75">This Month</h6>
                        <h2 class="mb-0">रू {{ number_format($summary['this_month_revenue'], 0) }}</h2>
                    </div>
                    <div class="stat-icon bg-white bg-opacity-25 text-white">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-gradient-to-r from-purple-500 to-purple-600 text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 opacity-75">Total Dispatches</h6>
                        <h2 class="mb-0">{{ $summary['total_dispatches'] }}</h2>
                    </div>
                    <div class="stat-icon bg-white bg-opacity-25 text-white">
                        <i class="fas fa-truck"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-gradient-to-r from-orange-500 to-orange-600 text-white">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1 opacity-75">Completed</h6>
                        <h2 class="mb-0">{{ $summary['completed_jobs'] }}</h2>
                    </div>
                    <div class="stat-icon bg-white bg-opacity-25 text-white">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-line me-2"></i>Revenue Trend (Last 12 Months)
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 260px; width: 100%;"><canvas id="revenueChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-2"></i>Dispatch Status
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 260px; width: 100%;"><canvas id="statusChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Lists -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-warehouse me-2"></i>Top Warehouses
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Warehouse</th>
                                    <th>Location</th>
                                    <th>Requests</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($top_warehouses as $warehouse)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $warehouse->name }}</td>
                                    <td>{{ $warehouse->location }}</td>
                                    <td><span class="badge bg-primary">{{ $warehouse->warehouse_requests_count }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-truck me-2"></i>Top Drivers
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Driver</th>
                                    <th>Email</th>
                                    <th>Jobs</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($top_drivers as $driver)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $driver->name }}</td>
                                    <td>{{ $driver->email }}</td>
                                    <td><span class="badge bg-success">{{ $driver->dispatch_orders_count }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card mt-4">
        <div class="card-header">
            <i class="fas fa-clock me-2"></i>Recent Activity
        </div>
        <div class="card-body">
            <div class="activity-timeline">
                @foreach($recent_activity as $activity)
                <div class="activity-item">
                    <div class="d-flex justify-content-between">
                        <div>
                            <i class="fas {{ $activity['icon'] }} text-{{ $activity['color'] }} me-2"></i>
                            <strong>{{ $activity['title'] }}</strong>
                            <span class="text-muted ms-2">{{ $activity['description'] }}</span>
                        </div>
                        <span class="time">{{ $activity['time'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        const revenueData = @json($revenue_chart);
        
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: revenueData.map(item => item.month),
                datasets: [{
                    label: 'Revenue (NPR)',
                    data: revenueData.map(item => item.revenue),
                    backgroundColor: 'rgba(245, 158, 11, 0.2)',
                    borderColor: 'rgba(245, 158, 11, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(245, 158, 11, 1)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'रू ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusData = @json($status_distribution['dispatches']);
        
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusData.map(item => item.status),
                datasets: [{
                    data: statusData.map(item => item.count),
                    backgroundColor: [
                        '#f59e0b', // pending
                        '#3b82f6', // assigned
                        '#8b5cf6', // picked_up
                        '#ec4899', // on_the_way
                        '#22c55e', // delivered
                        '#ef4444', // cancelled
                    ],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection