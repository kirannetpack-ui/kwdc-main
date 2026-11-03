<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ClientMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = Auth::user();
        
        // Check if user is client or admin (admin has access to everything)
        $isClient = false;
        
        if (isset($user->role) && ($user->role == 'client' || $user->role == 'admin')) {
            $isClient = true;
        }
        
        if (isset($user->user_type) && ($user->user_type == 'client' || $user->user_type == 'admin')) {
            $isClient = true;
        }
        
        if (!$isClient) {
            abort(403, 'Unauthorized access. Client access required.');
        }
        
        return $next($request);
    }
}