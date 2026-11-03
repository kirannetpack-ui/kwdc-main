@extends('layouts.app')

@section('title', 'My Rates')

@section('header', 'My Driver Rates')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border-l-4 border-red-500 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Current Active Rate Info -->
    @if(isset($currentRate) && $currentRate)
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded mb-6">
        <div class="flex justify-between items-start">
            <div>
                <p class="text-green-800 font-semibold">
                    <i class="fas fa-check-circle mr-2"></i> Your Current Active Rate
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div>
                        <p class="text-sm text-gray-600">0-5 km (Flat)</p>
                        <p class="text-lg font-bold text-green-700">रु {{ number_format($currentRate->rate_0_5, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">6-10 km (Flat)</p>
                        <p class="text-lg font-bold text-green-700">रु {{ number_format($currentRate->rate_6_10, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">11-15 km (Flat)</p>
                        <p class="text-lg font-bold text-green-700">रु {{ number_format($currentRate->rate_11_15, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">16-20 km (Flat)</p>
                        <p class="text-lg font-bold text-green-700">रु {{ number_format($currentRate->rate_16_20, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">21+ km (Per km)</p>
                        <p class="text-lg font-bold text-green-700">रु {{ number_format($currentRate->rate_per_km, 2) }}/km</p>
                    </div>
                </div>
                @if($currentRate->valid_until)
                <p class="text-sm text-green-600 mt-3">
                    Valid until: <strong>{{ \Carbon\Carbon::parse($currentRate->valid_until)->format('F j, Y') }}</strong>
                    ({{ \Carbon\Carbon::parse($currentRate->valid_until)->diffForHumans() }})
                </p>
                @else
                <p class="text-sm text-green-600 mt-3">Valid: Permanent</p>
                @endif
            </div>
            <div>
                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-sm">Active</span>
            </div>
        </div>
    </div>
    @else
    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded mb-6">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-yellow-800 font-semibold">
                    <i class="fas fa-exclamation-triangle mr-2"></i> No Active Rate
                </p>
                <p class="text-sm text-yellow-700 mt-1">
                    You don't have an active rate. Please add your rate below to start receiving job offers.
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Add New Rate Form -->
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-plus-circle text-red-500 mr-2"></i> Set Your Rates
        </h3>
        <form method="POST" action="{{ route('driver.rates.store') }}">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">
                        0-5 km (Flat Rate) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">रु</span>
                        <input type="number" name="rate_0_5" step="0.01" min="0" required
                            class="w-full pl-8 pr-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                            placeholder="0.00"
                            value="{{ old('rate_0_5', $currentRate->rate_0_5 ?? '') }}">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Fixed price for any trip up to 5 km</p>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">
                        6-10 km (Flat Rate) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">रु</span>
                        <input type="number" name="rate_6_10" step="0.01" min="0" required
                            class="w-full pl-8 pr-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                            placeholder="0.00"
                            value="{{ old('rate_6_10', $currentRate->rate_6_10 ?? '') }}">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Fixed price for any trip between 6-10 km</p>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">
                        11-15 km (Flat Rate) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">रु</span>
                        <input type="number" name="rate_11_15" step="0.01" min="0" required
                            class="w-full pl-8 pr-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                            placeholder="0.00"
                            value="{{ old('rate_11_15', $currentRate->rate_11_15 ?? '') }}">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Fixed price for any trip between 11-15 km</p>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">
                        16-20 km (Flat Rate) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">रु</span>
                        <input type="number" name="rate_16_20" step="0.01" min="0" required
                            class="w-full pl-8 pr-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                            placeholder="0.00"
                            value="{{ old('rate_16_20', $currentRate->rate_16_20 ?? '') }}">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Fixed price for any trip between 16-20 km</p>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">
                        21+ km (Per km Rate) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">रु</span>
                        <input type="number" name="rate_per_km" step="0.01" min="0" required
                            class="w-full pl-8 pr-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                            placeholder="0.00"
                            value="{{ old('rate_per_km', $currentRate->rate_per_km ?? '') }}">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Rate per km for distances above 20 km</p>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">Valid Until (Optional)</label>
                    <input type="date" name="valid_until" 
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500"
                        value="{{ old('valid_until') }}">
                    <p class="text-xs text-gray-500 mt-1">Leave empty for permanent rate</p>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-save mr-2"></i> Save Rate
                </button>
            </div>
        </form>
    </div>

    <!-- Extend Current Rate Form -->
    @if(isset($currentRate) && $currentRate)
    <div class="bg-blue-50 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-calendar-plus text-blue-500 mr-2"></i> Extend Current Rate
        </h3>
        <form method="POST" action="{{ route('driver.rates.extend') }}">
            @csrf
            <input type="hidden" name="rate_id" value="{{ $currentRate->id }}">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2">New Valid Until Date</label>
                    <input type="date" name="valid_until" required
                        class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-red-500">
                </div>
                <div>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-calendar-plus mr-2"></i> Extend Rate
                    </button>
                </div>
            </div>
        </form>
    </div>
    @endif

    <!-- Rate History Table -->
    <div class="mt-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-history text-gray-500 mr-2"></i> Rate History
        </h3>
        
        @if(isset($rates) && $rates->count() > 0)
        <div class="w-full overflow-hidden pt-1">
            <table class="kwdc-table-fixed">
                <thead>
                    <tr>
                        <th class="w-[100px]">0-5 km</th>
                        <th class="w-[100px]">6-10 km</th>
                        <th class="w-[100px]">11-15 km</th>
                        <th class="w-[100px]">16-20 km</th>
                        <th class="w-[110px]">21+ km</th>
                        <th class="w-[100px] text-center">Status</th>
                        <th class="w-[120px] text-right">Valid Until</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/90 text-xs">
                    @foreach($rates as $rate)
                    @php
                        $isActive = $rate->is_active && (!$rate->valid_until || \Carbon\Carbon::parse($rate->valid_until)->isFuture());
                    @endphp
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="font-bold text-slate-800">रु {{ number_format($rate->rate_0_5, 2) }}</td>
                        <td class="font-bold text-slate-800">रु {{ number_format($rate->rate_6_10, 2) }}</td>
                        <td class="font-bold text-slate-800">रु {{ number_format($rate->rate_11_15, 2) }}</td>
                        <td class="font-bold text-slate-800">रु {{ number_format($rate->rate_16_20, 2) }}</td>
                        <td class="font-bold text-orange-600">रु {{ number_format($rate->rate_per_km, 2) }}/km</td>
                        <td class="text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $isActive ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                {{ $isActive ? 'Active' : 'Expired' }}
                            </span>
                        </td>
                        <td class="text-slate-500 text-[11px] text-right">
                            @if($rate->valid_until)
                                {{ \Carbon\Carbon::parse($rate->valid_until)->format('M d, Y') }}
                            @else
                                Permanent
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $rates->links() }}
        </div>
        @else
        <div class="text-center py-8 bg-gray-50 rounded-lg">
            <i class="fas fa-chart-line text-gray-400 text-4xl mb-3"></i>
            <p class="text-gray-500">No rate history found</p>
            <p class="text-gray-400 text-sm mt-1">Add your first rate using the form above</p>
        </div>
        @endif
    </div>
</div>
@endsection