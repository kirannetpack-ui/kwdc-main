<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KatahoLocation extends Model
{
    use HasFactory;

    protected $table = 'kataho_locations';

    protected $fillable = [
        'kataho_code',
        'grid_id',
        'plate_id',
        'name',
        'address',
        'latitude',
        'longitude',
        'location_type',
        'reference_id',
        'reference_type',
        'is_verified',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_verified' => 'boolean',
    ];

    // Polymorphic relationship
    public function reference()
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('location_type', $type);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('kataho_code', $code);
    }

    // Helper methods
    public function getFullAddressAttribute()
    {
        return $this->address ?? $this->name ?? 'Location ' . $this->kataho_code;
    }

    public function getGoogleMapsUrlAttribute()
    {
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    public function getKatahoAppUrlAttribute()
    {
        return "https://kataho.app/location/{$this->kataho_code}";
    }
}