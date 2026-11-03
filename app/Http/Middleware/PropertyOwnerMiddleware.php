<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PropertyOwnerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = Auth::user();
        
        // Check if user is property_owner or admin
        $isPropertyOwner = false;
        
        if (isset($user->role) && ($user->role == 'property_owner' || $user->role == 'admin')) {
            $isPropertyOwner = true;
        }
        
        if (isset($user->user_type) && ($user->user_type == 'property_owner' || $user->user_type == 'admin')) {
            $isPropertyOwner = true;
        }
        
        if (!$isPropertyOwner) {
            abort(403, 'Unauthorized access. Property owner access required.');
        }
        
        return $next($request);
    }
}