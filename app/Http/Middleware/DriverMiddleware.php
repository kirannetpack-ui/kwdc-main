<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DriverMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = Auth::user();
        
        // Check if user is driver or admin
        $isDriver = false;
        
        if (isset($user->role) && ($user->role == 'driver' || $user->role == 'admin')) {
            $isDriver = true;
        }
        
        if (isset($user->user_type) && ($user->user_type == 'driver' || $user->user_type == 'admin')) {
            $isDriver = true;
        }
        
        if (!$isDriver) {
            abort(403, 'Unauthorized access. Driver access required.');
        }
        
        return $next($request);
    }
}