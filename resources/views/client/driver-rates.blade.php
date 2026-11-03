@extends('layouts.app')

@section('title', 'Driver Rates')
@section('header', 'Available Driver Rates')

@section('content')
<div class="bg-white rounded-xl shadow-md p-6">
    <div class="mb-6">
        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <p class="text-blue-700"><i class="fas fa-info-circle mr-2"></i> These are the current rates offered by drivers. Prices are calculated based on distance and valid for 24 hours.</p>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Driver Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">0-5 km</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">5-10 km</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">10-20 km</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">20+ km</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valid Until</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($driverRates ?? [] as $rate)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-semibold">{{ $rate->driver->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4">रु {{ number_format($rate->flat_rate_0_5) }}</td>
                    <td class="px-6 py-4">रु {{ number_format($rate->flat_rate_5_10) }}</td>
                    <td class="px-6 py-4">रु {{ number_format($rate->rate_per_km_10_20) }}/km</td>
                    <td class="px-6 py-4">रु {{ number_format($rate->rate_per_km_20_plus) }}/km</td>
                    <td class="px-6 py-4 text-sm">{{ $rate->effective_until->format('Y-m-d H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No active driver rates available</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $driverRates->links() }}
    </div>
</div>
@endsection