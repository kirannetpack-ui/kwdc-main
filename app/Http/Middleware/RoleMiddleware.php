<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = Auth::user();

        if ($this->matchesRole($user, ['admin', 'super_admin']) || $this->matchesRole($user, $roles)) {
            return $next($request);
        }

        abort(403, 'Unauthorized access. Your account does not have access to this area.');
    }

    private function matchesRole($user, array $roles): bool
    {
        foreach ($roles as $role) {
            if (($user->role ?? null) === $role || ($user->user_type ?? null) === $role) {
                return true;
            }

            $flag = 'is_' . $role;
            if (isset($user->{$flag}) && $user->{$flag} === true) {
                return true;
            }
        }

        return false;
    }
}
