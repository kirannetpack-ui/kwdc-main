@extends('layouts.app')

@section('title', 'Partner Earnings')
@section('header', 'Partner Earnings')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    
    <!-- Earnings Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="p-4 border rounded-lg bg-green-50 border-green-200">
            <h4 class="text-sm font-medium text-green-800">Total Paid Earnings</h4>
            <p class="text-2xl font-bold text-green-600 mt-1">रू {{ number_format($totalEarnings, 2) }}</p>
        </div>
        <div class="p-4 border rounded-lg bg-yellow-50 border-yellow-200">
            <h4 class="text-sm font-medium text-yellow-800">Pending Earnings</h4>
            <p class="text-2xl font-bold text-yellow-600 mt-1">रू {{ number_format($pendingEarnings, 2) }}</p>
        </div>
        <div class="p-4 border rounded-lg bg-blue-50 border-blue-200">
            <h4 class="text-sm font-medium text-blue-800">Total Orders</h4>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ $earnings->count() }}</p>
        </div>
    </div>

    <!-- Dispatch Orders Table -->
    <div class="mb-8">
        <h4 class="font-bold text-lg mb-4 text-gray-800">Dispatch Orders</h4>
        <div class="overflow-x-auto border rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Partner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Earned At</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dispatchOrders as $earning)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-800">#{{ $earning->order_id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-800">{{ $earning->partner->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-orange-600">रू {{ number_format($earning->amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $earning->status == 'paid' ? 'bg-green-100 text-green-800' : 
                                   ($earning->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($earning->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $earning->created_at ? $earning->created_at->format('M d, Y') : 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">No dispatch earnings found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pickup Orders Table -->
    <div class="mb-8">
        <h4 class="font-bold text-lg mb-4 text-gray-800">Pickup Orders</h4>
        <div class="overflow-x-auto border rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Partner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Earned At</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pickupOrders as $earning)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-800">#{{ $earning->order_id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-800">{{ $earning->partner->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-orange-600">रू {{ number_format($earning->amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $earning->status == 'paid' ? 'bg-green-100 text-green-800' : 
                                   ($earning->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($earning->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $earning->created_at ? $earning->created_at->format('M d, Y') : 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">No pickup earnings found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Equipment Orders Table -->
    <div>
        <h4 class="font-bold text-lg mb-4 text-gray-800">Equipment Jobs</h4>
        <div class="overflow-x-auto border rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Partner</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Earned At</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($equipmentOrders as $earning)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-800">#{{ $earning->order_id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-800">{{ $earning->partner->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-orange-600">रू {{ number_format($earning->amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $earning->status == 'paid' ? 'bg-green-100 text-green-800' : 
                                   ($earning->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ ucfirst($earning->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $earning->created_at ? $earning->created_at->format('M d, Y') : 'N/A' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500 text-sm">No equipment earnings found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection