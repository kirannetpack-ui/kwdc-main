@extends('layouts.app')

@section('title', 'My Stock')
@section('header', 'My Stock Inventory')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-6">My Stock Items</h3>
    
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Warehouse</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Added</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks as $stock)
                <tr class="border-b">
                    <td class="px-6 py-4">{{ $stock->product_name }}</td>
                    <td class="px-6 py-4">{{ $stock->quantity }}</td>
                    <td class="px-6 py-4">{{ $stock->warehouse->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">
                        <span class="status-badge status-{{ $stock->status }}">{{ ucfirst($stock->status) }}</span>
                    </td>
                    <td class="px-6 py-4">{{ $stock->created_at->format('Y-m-d') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No stock items found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $stocks->links() }}
    </div>
</div>
@endsection