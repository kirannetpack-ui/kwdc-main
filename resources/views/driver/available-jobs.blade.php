@extends('layouts.app')

@section('title', 'Available Jobs | Driver Hub')
@section('header', 'Available Freight Jobs')

@section('content')
@php
    $jobList = $jobs ?? $availableJobs ?? [];
    $totalAvailable = $jobList instanceof \Illuminate\Pagination\AbstractPaginator ? $jobList->total() : count($jobList);
@endphp

<div class="max-w-7xl mx-auto space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-3 border-b border-[#E8E2D8]">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider bg-[#D96B43]/10 text-[#D96B43] border border-[#D96B43]/20">
                    Carrier Marketplace
                </span>
                <span class="text-xs text-[#645D56]">{{ $totalAvailable }} Jobs Available</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-serif font-bold text-[#24201D] tracking-tight" style="font-family: 'Newsreader', Georgia, serif;">
                Available Delivery & Freight Jobs
            </h1>
            <p class="text-sm text-[#645D56] mt-0.5">Browse pending dispatches across Nepal corridors, accept assigned rates, or submit competitive proposals.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('driver.jobs') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-[#24201D] bg-[#FFFDF9] border border-[#E8E2D8] hover:bg-[#FAF8F5] transition shadow-2xs">
                <i class="fas fa-truck-moving text-xs text-[#D96B43]"></i>
                <span>My Active Jobs</span>
            </a>
            <a href="{{ route('driver.dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-[#24201D] bg-[#FFFDF9] border border-[#E8E2D8] hover:bg-[#FAF8F5] transition shadow-2xs">
                <i class="fas fa-gauge-high text-xs text-[#645D56]"></i>
                <span>Driver Hub</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
            <i class="fas fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl p-4 bg-red-50 border border-red-200 text-red-800 text-sm flex items-center gap-3">
            <i class="fas fa-circle-exclamation text-red-500 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Jobs Stream -->
    <div class="space-y-4">
        @forelse($jobList as $job)
        @php
            $rate = (float)($job->amount ?? $job->base_price ?? $job->price ?? 0);
            $dist = (float)($job->distance_km ?? $job->total_distance ?? 0);
            $pickup = $job->pickup_address ?? $job->pickup_location ?? 'Kathmandu Hub';
            $delivery = $job->delivery_address ?? $job->delivery_location ?? 'Multiple stops';
        @endphp
        <div class="bg-[#FFFDF9] border border-[#E8E2D8] rounded-2xl p-5 hover:border-[#D96B43]/40 transition-all shadow-2xs space-y-4">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                <div class="space-y-2 flex-1">
                    <div class="flex items-center gap-2.5">
                        <span class="font-mono text-xs font-bold px-2.5 py-1 rounded-lg bg-[#FAF8F5] border border-[#E8E2D8] text-[#24201D]">
                            #{{ $job->dispatch_number ?? ('DSP-' . $job->id) }}
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200/80">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Available to Accept
                        </span>
                        <span class="text-xs text-[#8C827A]">
                            Posted {{ $job->created_at?->diffForHumans() ?? 'recently' }}
                        </span>
                    </div>

                    <!-- Origin & Destination -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div class="bg-[#FAF8F5] p-3 rounded-xl border border-[#E8E2D8]/60 space-y-0.5">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-[#8C827A] flex items-center gap-1.5">
                                <i class="fas fa-circle-dot text-emerald-600 text-[9px]"></i>
                                <span>Pickup Location</span>
                            </div>
                            <div class="text-xs font-semibold text-[#24201D] truncate" title="{{ $pickup }}">
                                {{ $pickup }}
                            </div>
                        </div>

                        <div class="bg-[#FAF8F5] p-3 rounded-xl border border-[#E8E2D8]/60 space-y-0.5">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-[#8C827A] flex items-center gap-1.5">
                                <i class="fas fa-flag-checkered text-[#D96B43] text-[9px]"></i>
                                <span>Destination</span>
                            </div>
                            <div class="text-xs font-semibold text-[#24201D] truncate" title="{{ $delivery }}">
                                {{ $delivery }}
                            </div>
                        </div>
                    </div>

                    <!-- Metadata pills -->
                    <div class="flex flex-wrap items-center gap-3 pt-1 text-xs text-[#645D56]">
                        @if($dist > 0)
                        <span class="inline-flex items-center gap-1.5">
                            <i class="fas fa-road text-[#8C827A]"></i>
                            <span>{{ number_format($dist, 1) }} km</span>
                        </span>
                        @endif

                        @if(!empty($job->vehicle_type))
                        <span class="inline-flex items-center gap-1.5">
                            <i class="fas fa-truck text-[#8C827A]"></i>
                            <span>{{ ucfirst($job->vehicle_type) }}</span>
                        </span>
                        @endif

                        @if(!empty($job->cargo_type ?? $job->item_description))
                        <span class="inline-flex items-center gap-1.5">
                            <i class="fas fa-box text-[#8C827A]"></i>
                            <span>{{ $job->cargo_type ?? $job->item_description }}</span>
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Rate & Actions -->
                <div class="flex flex-col sm:items-end justify-between gap-3 pt-2 md:pt-0 sm:border-l sm:border-[#E8E2D8]/60 sm:pl-6">
                    <div class="sm:text-right">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#8C827A] block">Driver Payout</span>
                        <div class="text-2xl font-serif font-bold text-[#24201D] mt-0.5" style="font-family: 'Newsreader', Georgia, serif;">
                            @if($rate > 0)
                                रू {{ number_format($rate, 2) }}
                            @else
                                <span class="text-sm font-sans text-amber-700 font-semibold">Quote Required</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($rate > 0)
                        <form action="{{ route('driver.jobs.accept', $job->id) }}" method="POST" onsubmit="return confirm('Confirm accepting this dispatch route?');">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-xs transition">
                                <i class="fas fa-check text-xs"></i>
                                <span>Accept Job</span>
                            </button>
                        </form>
                        @endif

                        <button type="button" 
                                onclick="openProposalModal({{ $job->id }}, '{{ $job->dispatch_number ?? ('DSP-' . $job->id) }}', {{ $rate }})"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-[#FFFDF9] hover:bg-[#FAF8F5] text-[#24201D] text-xs font-semibold border border-[#E8E2D8] transition shadow-2xs">
                            <i class="fas fa-hand-holding-dollar text-xs text-[#D96B43]"></i>
                            <span>{{ $rate > 0 ? 'Counter-Offer' : 'Propose Rate' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-[#FFFDF9] border border-[#E8E2D8] rounded-2xl p-12 text-center space-y-4">
            <div class="w-16 h-16 mx-auto rounded-full bg-[#FAF8F5] border border-[#E8E2D8] flex items-center justify-center text-2xl text-[#8C827A]">
                <i class="fas fa-route"></i>
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-[#24201D]">No Available Jobs Right Now</h3>
                <p class="text-xs text-[#645D56] max-w-sm mx-auto">All current freight and pickup dispatches are assigned to drivers. New routes will appear as clients book orders.</p>
            </div>
            <div class="pt-2">
                <a href="{{ route('driver.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#D96B43] hover:bg-[#C35832] text-white text-xs font-semibold transition">
                    <i class="fas fa-arrow-left text-xs"></i>
                    <span>Return to Dashboard</span>
                </a>
            </div>
        </div>
        @endforelse
    </div>

    @if($jobList instanceof \Illuminate\Pagination\AbstractPaginator && $jobList->hasPages())
    <div class="pt-4">
        {{ $jobList->links() }}
    </div>
    @endif
</div>

<!-- Proposal Modal -->
<div id="driverProposalModal" class="fixed inset-0 bg-[#24201D]/60 backdrop-blur-xs hidden z-50 flex items-center justify-center p-4">
    <div class="bg-[#FFFDF9] border border-[#E8E2D8] rounded-2xl shadow-xl max-w-md w-full overflow-hidden animate-fadeIn">
        <div class="p-5 border-b border-[#E8E2D8] flex items-center justify-between bg-[#FAF8F5]">
            <div class="flex items-center gap-2">
                <i class="fas fa-hand-holding-dollar text-[#D96B43]"></i>
                <h3 class="text-sm font-bold text-[#24201D]">Propose Delivery Rate</h3>
            </div>
            <button type="button" onclick="closeProposalModal()" class="text-[#8C827A] hover:text-[#24201D]">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <form id="driverProposalForm" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <span class="text-xs text-[#645D56] block mb-1">Dispatch Order:</span>
                <span id="proposalJobRef" class="font-mono text-xs font-bold text-[#24201D]"></span>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-[#645D56] mb-1.5">Your Proposed Price (NPR) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs font-bold text-[#8C827A]">रू</span>
                    <input type="number" 
                           id="proposed_price_input" 
                           name="proposed_price" 
                           step="0.01" 
                           min="100" 
                           required
                           class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-[#E8E2D8] bg-[#FAF8F5] text-sm font-mono text-[#24201D] focus:outline-none focus:border-[#D96B43]"
                           placeholder="Enter fair price">
                </div>
                <p class="text-[11px] text-[#8C827A] mt-1.5">The client will review your proposed rate in their portal to accept or negotiate.</p>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-[#E8E2D8]">
                <button type="button" onclick="closeProposalModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-[#645D56] hover:bg-[#FAF8F5] transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 rounded-xl bg-[#D96B43] hover:bg-[#C35832] text-white text-xs font-semibold shadow-xs transition">
                    Submit Proposal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openProposalModal(jobId, jobRef, currentPrice) {
    const modal = document.getElementById('driverProposalModal');
    const form = document.getElementById('driverProposalForm');
    const refSpan = document.getElementById('proposalJobRef');
    const input = document.getElementById('proposed_price_input');

    form.action = '/driver/propose-price/' + jobId;
    refSpan.textContent = '#' + jobRef;
    if (currentPrice > 0) {
        input.value = currentPrice;
    } else {
        input.value = '';
    }

    modal.classList.remove('hidden');
    input.focus();
}

function closeProposalModal() {
    document.getElementById('driverProposalModal').classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeProposalModal();
});
</script>
@endsection