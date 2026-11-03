<?php

namespace App\Helpers;

use App\Models\User;

class UserCodeHelper
{
    /**
     * Get user by their code.
     */
    public static function getUserByCode($userCode)
    {
        return User::where('user_code', $userCode)->first();
    }
    
    /**
     * Format user code for display.
     */
    public static function formatCode($userCode)
    {
        if (!$userCode) {
            return 'N/A';
        }
        
        $parts = explode('-', $userCode);
        if (count($parts) === 3) {
            return $parts[0] . '-' . $parts[1] . '-' . $parts[2];
        }
        
        return $userCode;
    }
    
    /**
     * Validate user code format.
     */
    public static function isValidFormat($userCode)
    {
        return preg_match('/^(CLT|DRV|EQO|USR)-\d{4}-\d{4}$/', $userCode);
    }
}