@extends('layouts.app')

@section('title', 'Access Restricted | KTM-WDC')
@section('header', '403 - Forbidden')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6">
    <div class="bg-[#FFFDF9] border border-[#E8E2D8] rounded-2xl shadow-2xs p-8 sm:p-12 text-center space-y-6">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-700 text-2xl">
            <i class="fas fa-lock"></i>
        </div>

        <div class="space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-700 font-mono">Security Notice 403</span>
            <h1 class="text-3xl sm:text-4xl font-serif font-bold text-[#24201D] tracking-tight" style="font-family: 'Newsreader', Georgia, serif;">
                Access credentials required
            </h1>
            <p class="text-sm text-[#645D56] max-w-md mx-auto leading-relaxed">
                You do not currently hold authorization to view this logistics record or tenant resource. If you believe this is a permission error, please reach out to your operations lead.
            </p>
        </div>

        <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#FAF8F5] hover:bg-[#F0ECE4] text-[#24201D] text-xs font-semibold border border-[#E8E2D8] transition">
                <i class="fas fa-arrow-left text-xs text-[#645D56]"></i>
                <span>Return to Previous Page</span>
            </a>
            <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#D96B43] hover:bg-[#C35832] text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-home text-xs"></i>
                <span>{{ auth()->check() ? 'Back to Dashboard' : 'Back to Home' }}</span>
            </a>
        </div>
    </div>
</div>
@endsection