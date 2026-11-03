<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'user_id',
        'vehicle_type',
        'flat_rate_0_5',
        'flat_rate_6_10',
        'flat_rate_11_20',
        'rate_per_km_21_plus',
        'base_fare',
        'minimum_fare',
        'rate_per_km',
        'rate_per_hour',
        'valid_until',
        'date',
        'effective_from',
        'effective_until',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'date' => 'date',
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
        'valid_until' => 'datetime',
        'flat_rate_0_5' => 'decimal:2',
        'flat_rate_6_10' => 'decimal:2',
        'flat_rate_11_20' => 'decimal:2',
        'rate_per_km_21_plus' => 'decimal:2',
        'rate_per_km' => 'decimal:2',
        'rate_per_hour' => 'decimal:2',
        'base_fare' => 'decimal:2',
        'minimum_fare' => 'decimal:2',
    ];

    // Relationships
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId)
            ->orWhere('user_id', $driverId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeValid($query)
    {
        return $query->where(function($q) {
            $q->whereNull('valid_until')
              ->orWhere('valid_until', '>', now());
        });
    }

    public function scopeEffective($query)
    {
        return $query->where(function($q) {
            $q->whereNull('effective_from')
              ->orWhere('effective_from', '<=', now());
        })->where(function($q) {
            $q->whereNull('effective_until')
              ->orWhere('effective_until', '>=', now());
        });
    }

    // Helper methods
    public function getRateForDistance($distance)
    {
        if ($distance <= 5) {
            return $this->flat_rate_0_5 ?? 0;
        } elseif ($distance <= 10) {
            return $this->flat_rate_6_10 ?? 0;
        } elseif ($distance <= 20) {
            return $this->flat_rate_11_20 ?? 0;
        } else {
            return ($this->rate_per_km_21_plus ?? $this->rate_per_km ?? 0) * $distance;
        }
    }

    public function getFormattedRateAttribute()
    {
        return 'रु ' . number_format($this->flat_rate_0_5 ?? 0, 2);
    }

    public function isEffective()
    {
        $fromValid = is_null($this->effective_from) || $this->effective_from <= now();
        $untilValid = is_null($this->effective_until) || $this->effective_until >= now();
        return $fromValid && $untilValid && $this->is_active;
    }
}