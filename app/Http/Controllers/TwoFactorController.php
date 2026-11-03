<?php

namespace App\Http\Controllers;

use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class TwoFactorController extends Controller
{
    /**
     * Toggle 2FA on or off for the authenticated user.
     */
    public function toggle(Request $request)
    {
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            $user->disableTwoFactor();
            session()->forget('two_factor_verified');
            return redirect()->back()->with('success', 'Two-Factor Authentication (2FA) has been disabled.');
        }

        $user->enableTwoFactor();
        $code = $user->generateTwoFactorCode();

        // Send OTP via Nepal SMS if mobile available
        if ($user->phone) {
            try {
                app(SmsService::class)->sendOtp($user->phone, $code);
            } catch (\Throwable $e) {
                \Log::warning('2FA SMS sending failed: ' . $e->getMessage());
            }
        }

        session(['two_factor_verified' => true]);

        return redirect()->back()->with('success', "Two-Factor Authentication (2FA) is now enabled. Your verification code is {$code}.");
    }

    /**
     * Show 2FA challenge screen.
     */
    public function challenge()
    {
        $user = Auth::user();
        if (!$user || !$user->two_factor_enabled || session('two_factor_verified')) {
            return redirect()->route('dashboard');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify submitted 2FA code.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        if ($user && $user->verifyTwoFactorCode($request->code)) {
            session(['two_factor_verified' => true]);
            return redirect()->intended(route('dashboard'))
                ->with('success', 'Two-Factor verification successful.');
        }

        return redirect()->back()->withErrors([
            'code' => 'Invalid or expired 2FA code. Please check and try again.',
        ]);
    }
}
