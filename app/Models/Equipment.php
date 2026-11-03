<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Equipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',           // Add this - for compatibility with dashboard queries
        'owner_id', 
        'name', 
        'type', 
        'model', 
        'year', 
        'description',
        'weight', 
        'engine_power', 
        'bucket_capacity', 
        'max_reach',
        'daily_rate', 
        'weekly_rate', 
        'monthly_rate', 
        'security_deposit',
        'location', 
        'status', 
        'front_photo', 
        'side_photo', 
        'working_photo',
        'registration_doc', 
        'insurance_doc'
    ];

    protected $casts = [
        'daily_rate' => 'decimal:2',
        'weekly_rate' => 'decimal:2',
        'monthly_rate' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'weight' => 'decimal:2',
        'engine_power' => 'decimal:2',
        'bucket_capacity' => 'decimal:2',
        'max_reach' => 'decimal:2',
        'year' => 'integer',
    ];

    /**
     * Get the owner of the equipment (using owner_id)
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the user (owner) - alias for compatibility with dashboard
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get equipment jobs
     */
    public function jobs()
    {
        return $this->hasMany(EquipmentJob::class, 'equipment_id');
    }

    /**
     * Alias for equipment jobs (for admin queries and views)
     */
    public function equipmentJobs()
    {
        return $this->hasMany(EquipmentJob::class, 'equipment_id');
    }

    /**
     * Scope for active/available equipment
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope for equipment belonging to a specific owner
     */
    public function scopeForOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId)
            ->orWhere('user_id', $ownerId);
    }

    /**
     * Check if equipment is available
     */
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    /**
     * Get formatted daily rate
     */
    public function getFormattedDailyRateAttribute()
    {
        return 'रु ' . number_format($this->daily_rate, 2);
    }

    /**
     * Get formatted weekly rate
     */
    public function getFormattedWeeklyRateAttribute()
    {
        return 'रु ' . number_format($this->weekly_rate, 2);
    }

    /**
     * Get formatted monthly rate
     */
    public function getFormattedMonthlyRateAttribute()
    {
        return 'रु ' . number_format($this->monthly_rate, 2);
    }

    /**
     * Get equipment display name
     */
    public function getDisplayNameAttribute()
    {
        return $this->name . ($this->model ? ' (' . $this->model . ')' : '');
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        switch ($this->status) {
            case 'available':
                return 'bg-green-100 text-green-600';
            case 'rented':
            case 'in_use':
                return 'bg-yellow-100 text-yellow-600';
            case 'maintenance':
                return 'bg-orange-100 text-orange-600';
            default:
                return 'bg-gray-100 text-gray-600';
        }
    }
}