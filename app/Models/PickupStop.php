<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'pickup_request_id',
        'stop_number',
        'address',
        'contact_name',
        'contact_phone',
        'items_description',
        'estimated_weight',
        'status',
        'completed_at',
        // ===== KATAHO FIELDS =====
        'kataho_code',
        'kataho_grid_id',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'estimated_weight' => 'decimal:2',
        'completed_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function pickupRequest()
    {
        return $this->belongsTo(PickupRequest::class);
    }

    public function katahoLocation()
    {
        return $this->morphOne(KatahoLocation::class, 'reference');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function hasKatahoLocation()
    {
        return !empty($this->kataho_code);
    }
}