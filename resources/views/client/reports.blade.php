@extends('layouts.app')

@section('title', 'My Reports')
@section('header', 'My Activity Reports')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <p class="text-gray-600">Total Requests</p>
            <p class="text-3xl font-bold text-blue-600">{{ $totalRequests ?? 0 }}</p>
        </div>
        <div class="bg-green-50 rounded-lg p-4 text-center">
            <p class="text-gray-600">Completed Orders</p>
            <p class="text-3xl font-bold text-green-600">{{ $completedOrders ?? 0 }}</p>
        </div>
        <div class="bg-orange-50 rounded-lg p-4 text-center">
            <p class="text-gray-600">Total Spent</p>
            <p class="text-3xl font-bold text-orange-600">रु {{ number_format($totalSpent ?? 0) }}</p>
        </div>
    </div>
    
    <h3 class="text-lg font-bold text-gray-800 mb-4">Recent Orders</h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($recentOrders ?? [] as $order)
                <tr>
                    <td class="px-6 py-4">#{{ $order->id }}</td>
                    <td class="px-6 py-4">{{ ucfirst($order->type ?? 'Dispatch') }}</td>
                    <td class="px-6 py-4">रु {{ number_format($order->amount ?? 0) }}</td>
                    <td class="px-6 py-4">
                        <span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status ?? 'Pending') }}</span>
                    </td>
                    <td class="px-6 py-4">{{ $order->created_at->format('Y-m-d') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No orders found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection