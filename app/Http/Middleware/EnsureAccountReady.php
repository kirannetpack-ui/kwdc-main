<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountReady
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        if ($user->isBanned()) {
            abort(403, 'Account Suspended: ' . ($user->ban_reason ?: 'This account has been banned due to security compliance review.'));
        }

        if (! $user->is_active) {
            abort(403, 'This account is disabled. Please contact your administrator.');
        }

        if (! $user->hasVerifiedEmail()) {
            if ($request->expectsJson()) {
                abort(403, 'Verify your email before continuing.');
            }

            return redirect()->route('activation.notice', ['email' => $user->email]);
        }

        return $next($request);
    }
}
