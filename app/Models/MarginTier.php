<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarginTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'service_type', // dispatch, pickup, warehouse, equipment
        'margin_type',  // percentage, flat
        'margin_value',
        'min_distance',
        'max_distance',
        'is_active',
    ];

    protected $casts = [
        'min_distance' => 'decimal:2',
        'max_distance' => 'decimal:2',
        'margin_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}