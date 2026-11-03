@extends('layouts.app')

@section('title', 'Rate Limit Exceeded | KTM-WDC')
@section('header', '429 - Rate Limit Exceeded')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6">
    <div class="bg-[#FFFDF9] border border-[#E8E2D8] rounded-2xl shadow-2xs p-8 sm:p-12 text-center space-y-6">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-700 text-2xl">
            <i class="fas fa-gauge-high"></i>
        </div>

        <div class="space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-700 font-mono">Rate Limit 429</span>
            <h1 class="text-3xl sm:text-4xl font-serif font-bold text-[#24201D] tracking-tight" style="font-family: 'Newsreader', Georgia, serif;">
                Pacing network requests
            </h1>
            <p class="text-sm text-[#645D56] max-w-md mx-auto leading-relaxed">
                You've initiated several requests in quick succession. To safeguard system stability and data integrity, please pause for a brief moment before retrying.
            </p>
        </div>

        <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
            <a href="javascript:location.reload();" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#FAF8F5] hover:bg-[#F0ECE4] text-[#24201D] text-xs font-semibold border border-[#E8E2D8] transition">
                <i class="fas fa-rotate-right text-xs text-[#645D56]"></i>
                <span>Retry Now</span>
            </a>
            <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#D96B43] hover:bg-[#C35832] text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-home text-xs"></i>
                <span>{{ auth()->check() ? 'Back to Dashboard' : 'Back to Home' }}</span>
            </a>
        </div>
    </div>
</div>
@endsection