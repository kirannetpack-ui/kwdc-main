<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasUserCode
{
    /**
     * Boot the trait and add model event listeners.
     */
    protected static function bootHasUserCode()
    {
        static::creating(function ($model) {
            if (empty($model->user_code)) {
                $model->user_code = $model->generateUserCode();
            }
        });
    }

    /**
     * Generate a unique user code in format: PREFIX-YEAR-0000
     */
    public function generateUserCode()
    {
        // Determine prefix based on role
        $prefix = match(true) {
            $this->is_client || $this->role === 'client' => 'CLT',
            $this->is_driver || $this->role === 'driver' => 'DRV',
            $this->is_equipment_owner || $this->role === 'equipment_owner' => 'EQO',
            default => 'USR',
        };
        
        $year = now()->year;
        
        // Get the highest sequence number for this prefix and year
        $lastCode = DB::table($this->getTable())
            ->where('user_code', 'like', "{$prefix}-{$year}-%")
            ->orderBy('user_code', 'desc')
            ->value('user_code');
        
        if ($lastCode) {
            // Extract the 4-digit number from the end
            $lastSeq = (int) substr($lastCode, -4);
            $newSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newSeq = '0001';
        }
        
        return "{$prefix}-{$year}-{$newSeq}";
    }
    
    /**
     * Find a user by their unique code.
     */
    public static function findByCode($userCode)
    {
        return static::where('user_code', $userCode)->first();
    }
    
    /**
     * Get the displayable user ID.
     */
    public function getDisplayIdAttribute()
    {
        return $this->user_code ?? 'Not Assigned';
    }
}