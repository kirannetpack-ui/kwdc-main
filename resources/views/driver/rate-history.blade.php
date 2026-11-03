@extends('layouts.app')

@section('title', 'Rate History')
@section('header', 'My Rate History')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold text-gray-800">Rate History</h3>
        <a href="{{ route('driver.rates') }}" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">
            <i class="fas fa-plus mr-2"></i> Set New Rate
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valid From</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valid Until</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">0-5 km</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">5-10 km</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">10-20 km</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">20+ km</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($rates as $rate)
                <tr>
                    <td class="px-6 py-4">{{ $rate->effective_from->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4">{{ $rate->effective_until->format('Y-m-d H:i') }}</td>
                    <td class="px-6 py-4">रु {{ number_format($rate->flat_rate_0_5) }}</td>
                    <td class="px-6 py-4">रु {{ number_format($rate->flat_rate_5_10) }}</td>
                    <td class="px-6 py-4">रु {{ number_format($rate->rate_per_km_10_20) }}/km</td>
                    <td class="px-6 py-4">रु {{ number_format($rate->rate_per_km_20_plus) }}/km</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No rate history found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $rates->links() }}
    </div>
</div>

<style>
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-active { background: #d1fae5; color: #059669; }
    .status-expired { background: #fee2e2; color: #dc2626; }
</style>
@endsection