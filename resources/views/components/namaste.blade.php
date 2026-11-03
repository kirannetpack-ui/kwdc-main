@props(['role' => '', 'message' => ''])

<div class="bg-gradient-to-r from-orange-50 to-amber-50 rounded-lg p-4 mb-6 border-l-4 border-orange-500">
    <div class="flex items-center">
        <div class="text-4xl mr-4 animate-wave" style="animation: wave 0.5s ease-in-out;">
            🙏
        </div>
        <div>
            <p class="text-gray-800 text-lg">
                <span class="font-bold">Namaste</span>
                @if($role)
                    <span class="text-orange-600 font-bold ml-1">{{ $role }}</span>
                @endif
                <span class="text-orange-600 font-bold ml-1">{{ Auth::user()->name }}</span>
            </p>
            <p class="text-sm text-gray-500 mt-1">
                {{ $message ?: 'Welcome back to KTM-WDC Dashboard' }}
            </p>
        </div>
    </div>
</div>

<style>
    @keyframes wave {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(10deg); }
        75% { transform: rotate(-10deg); }
    }
    .animate-wave {
        animation: wave 0.5s ease-in-out;
    }
</style>