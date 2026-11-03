@extends('layouts.app')

@section('title', 'Session Security Refresh | KTM-WDC')
@section('header', '419 - Session Expired')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6">
    <div class="bg-[#FFFDF9] border border-[#E8E2D8] rounded-2xl shadow-2xs p-8 sm:p-12 text-center space-y-6">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-700 text-2xl">
            <i class="fas fa-shield-halved"></i>
        </div>

        <div class="space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-amber-700 font-mono">Session Check 419</span>
            <h1 class="text-3xl sm:text-4xl font-serif font-bold text-[#24201D] tracking-tight" style="font-family: 'Newsreader', Georgia, serif;">
                Your security token has expired
            </h1>
            <p class="text-sm text-[#645D56] max-w-md mx-auto leading-relaxed">
                To protect against cross-site request forgery, sessions automatically cycle after periods of inactivity. Refreshing will issue a fresh cryptographic token.
            </p>
        </div>

        <div class="pt-4 flex flex-wrap items-center justify-center gap-3">
            <a href="javascript:location.reload();" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#FAF8F5] hover:bg-[#F0ECE4] text-[#24201D] text-xs font-semibold border border-[#E8E2D8] transition">
                <i class="fas fa-rotate-right text-xs text-[#645D56]"></i>
                <span>Refresh Session</span>
            </a>
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#D96B43] hover:bg-[#C35832] text-white text-xs font-semibold shadow-xs transition">
                <i class="fas fa-sign-in-alt text-xs"></i>
                <span>Sign In Again</span>
            </a>
        </div>
    </div>
</div>
@endsection
