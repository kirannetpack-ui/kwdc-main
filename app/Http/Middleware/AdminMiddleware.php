<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = Auth::user();
        
        // Check for admin role in multiple possible fields
        $isAdmin = false;
        
        // Check role field
        if (isset($user->role) && ($user->role == 'admin' || $user->role == 'super_admin')) {
            $isAdmin = true;
        }
        
        // Check is_admin boolean field
        if (isset($user->is_admin) && $user->is_admin == true) {
            $isAdmin = true;
        }
        
        // Check user_type field
        if (isset($user->user_type) && ($user->user_type == 'admin' || $user->user_type == 'super_admin')) {
            $isAdmin = true;
        }
        
        // Check user has admin permission via Spatie or similar
        if (method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            $isAdmin = true;
        }
        
        if (!$isAdmin) {
            abort(403, 'Unauthorized access. Admin privileges required.');
        }
        
        return $next($request);
    }
}