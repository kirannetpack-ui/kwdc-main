@extends('layouts.app')

@section('title', 'Live Shipment Tracking | KTM-WDC')
@section('header', 'Live Tracking')

@section('content')
@php
    $totalCount = $dispatches instanceof \Illuminate\Pagination\AbstractPaginator ? $dispatches->total() : count($dispatches ?? []);
    $items = $dispatches instanceof \Illuminate\Pagination\AbstractPaginator ? $dispatches->items() : ($dispatches ?? []);
@endphp

<div class="max-w-7xl mx-auto space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-[#E8E2D8]">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-emerald-50 text-emerald-800 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live GPS Telemetry</span>
                </span>
                <span class="text-xs text-[#645D56]">{{ $totalCount }} Tracked Shipments</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#24201D] tracking-tight" style="font-family: 'Newsreader', Georgia, serif;">
                @if(Auth::user()->role === 'admin')
                    Highway Freight & Delivery Radar
                @elseif(Auth::user()->role === 'client')
                    My In-Transit Shipments
                @elseif(Auth::user()->role === 'driver')
                    My Assigned Transit Routes
                @else
                    Shipment Tracking
                @endif
            </h1>
            <p class="text-sm text-[#645D56] mt-0.5">Real-time driver location updates, waypoint progress, and arrival estimates across Nepal corridors.</p>
        </div>

        <div class="flex items-center gap-2">
            @if(Auth::user()->role === 'client')
            <a href="{{ route('dispatch.direct-create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-[#D96B43] hover:bg-[#C35832] transition shadow-xs">
                <i class="fas fa-plus text-xs"></i>
                <span>Book Dispatch</span>
            </a>
            @endif
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-[#24201D] bg-[#FFFDF9] border border-[#E8E2D8] hover:bg-[#FAF8F5] transition shadow-2xs">
                <i class="fas fa-arrow-left text-xs text-[#645D56]"></i>
                <span>Dashboard</span>
            </a>
        </div>
    </div>
    
    @if(session('success'))
        <div class="rounded-xl p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fas fa-circle-check text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <!-- Data Table Shell -->
    <div class="bg-[#FFFDF9] border border-[#E8E2D8] rounded-2xl p-4 shadow-2xs">
        <div class="w-full overflow-hidden">
            <table class="kwdc-table-fixed">
                <thead>
                    <tr>
                        <th class="w-[140px]">Tracking ID</th>
                        <th class="w-[160px]">Client</th>
                        <th class="w-[160px]">Assigned Driver</th>
                        <th class="w-[200px]">Pickup Location</th>
                        <th class="w-[110px]">Status</th>
                        <th class="w-[130px]">Last Signal</th>
                        <th class="w-[130px] text-right">Radar View</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E8E2D8]/70 text-xs">
                    @forelse($items as $dispatch)
                    @php
                        $st = strtolower($dispatch->status ?? 'pending');
                    @endphp
                    <tr class="hover:bg-[#FAF8F5]/80 transition">
                        <td>
                            <strong class="font-mono text-[#24201D] block">
                                {{ $dispatch->tracking_id ?? ('#DSP-' . $dispatch->id) }}
                            </strong>
                            <span class="text-[10px] text-[#8C827A]">
                                {{ $dispatch->dispatch_number ?? ('#' . $dispatch->id) }}
                            </span>
                        </td>
                        <td class="text-[#645D56] truncate">
                            <span class="font-medium text-[#24201D]">{{ optional($dispatch->client)->name ?? 'Customer' }}</span>
                            @if(optional($dispatch->client)->phone)
                                <span class="block text-[10px] text-[#8C827A]">{{ optional($dispatch->client)->phone }}</span>
                            @endif
                        </td>
                        <td class="text-[#645D56] truncate">
                            @if($dispatch->driver)
                                <span class="font-medium text-[#24201D] flex items-center gap-1.5">
                                    <i class="fas fa-id-badge text-emerald-600 text-[10px]"></i>
                                    {{ $dispatch->driver->name }}
                                </span>
                                @if($dispatch->driver->phone)
                                    <span class="text-[10px] text-[#8C827A] block">{{ $dispatch->driver->phone }}</span>
                                @endif
                            @else
                                <span class="text-[11px] text-amber-700 font-semibold bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                    Unassigned
                                </span>
                            @endif
                        </td>
                        <td class="text-[#645D56] truncate" title="{{ $dispatch->pickup_address }}">
                            <i class="fas fa-map-pin text-[#D96B43] mr-1 text-[10px]"></i>
                            {{ Str::limit($dispatch->pickup_address ?? 'Kathmandu Hub', 35) }}
                        </td>
                        <td>
                            @php
                                $badgeClass = match($st) {
                                    'delivered', 'completed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                    'picked_up', 'in_transit', 'on_the_way' => 'bg-blue-50 text-blue-800 border-blue-200',
                                    'assigned' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                                    'cancelled' => 'bg-rose-50 text-rose-800 border-rose-200',
                                    default => 'bg-amber-50 text-amber-800 border-amber-200'
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold border {{ $badgeClass }}">
                                {{ ucfirst(str_replace('_', ' ', $st)) }}
                            </span>
                        </td>
                        <td class="text-[#8C827A]">
                            {{ $dispatch->last_location_update ? $dispatch->last_location_update->diffForHumans() : ($dispatch->updated_at?->diffForHumans() ?? '—') }}
                        </td>
                        <td class="text-right">
                            <a href="{{ route('dispatch.show', $dispatch->id) }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#FAF8F5] hover:bg-[#F0ECE4] text-[#24201D] text-[11px] font-semibold border border-[#E8E2D8] transition shadow-2xs">
                                <i class="fas fa-map-marked-alt text-[#D96B43] text-[10px]"></i>
                                <span>Radar</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-full bg-[#FAF8F5] border border-[#E8E2D8] text-[#8C827A] flex items-center justify-center text-xl">
                                <i class="fas fa-satellite-dish"></i>
                            </div>
                            <div class="space-y-0.5">
                                <h3 class="text-sm font-bold text-[#24201D]">No Shipments Active</h3>
                                <p class="text-xs text-[#645D56]">There are currently no active deliveries transmitting GPS signals.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($dispatches instanceof \Illuminate\Pagination\AbstractPaginator && $dispatches->hasPages())
        <div class="pt-4 border-t border-[#E8E2D8]">
            {{ $dispatches->links() }}
        </div>
        @endif
    </div>
</div>
@endsection