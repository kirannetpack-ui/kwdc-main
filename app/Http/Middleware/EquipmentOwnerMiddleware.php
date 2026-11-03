<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EquipmentOwnerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        $user = Auth::user();
        
        // Check if user is equipment_owner or admin
        $isEquipmentOwner = false;
        
        if (isset($user->role) && ($user->role == 'equipment_owner' || $user->role == 'admin')) {
            $isEquipmentOwner = true;
        }
        
        if (isset($user->user_type) && ($user->user_type == 'equipment_owner' || $user->user_type == 'admin')) {
            $isEquipmentOwner = true;
        }
        
        if (!$isEquipmentOwner) {
            abort(403, 'Unauthorized access. Equipment owner access required.');
        }
        
        return $next($request);
    }
}