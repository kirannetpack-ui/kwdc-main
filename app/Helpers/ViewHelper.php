<?php

namespace App\Helpers;

use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ViewHelper
{
    /**
     * Get assigned warehouses for the current client
     */
    public static function getAssignedWarehouses()
    {
        $user = Auth::user();
        
        if (!$user || $user->role != 'client') {
            return collect();
        }
        
        $warehouses = Warehouse::whereHas('warehouseRequests', function($query) use ($user) {
            $query->where('client_id', $user->id)
                  ->where('status', 'approved');
        })->with('user')->get();
        
        if ($warehouses->isEmpty()) {
            $warehouses = Warehouse::where('status', 'approved')
                ->limit(10)
                ->with('user')
                ->get();
        }
        
        return $warehouses;
    }
    
    /**
     * Check if assigned warehouses exist
     */
    public static function hasAssignedWarehouses()
    {
        return self::getAssignedWarehouses()->isNotEmpty();
    }
    
    /**
     * Get warehouse count for client
     */
    public static function getAssignedWarehousesCount()
    {
        return self::getAssignedWarehouses()->count();
    }
}