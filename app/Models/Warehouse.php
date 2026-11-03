<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        // Owner information
        'owner_id',
        'user_id', // For compatibility
        
        // Basic information
        'name',
        'location',
        'address',
        'city',
        'state',
        'country',
        'postal_code',
        
        // Dimensions
        'area',
        'area_sqft',
        'area_sqm',
        
        // Pricing
        'price',
        'rate_per_sqft',
        'minimum_rent',
        
        // Location coordinates
        'latitude',
        'longitude',
        
        // ===== KATAHO FIELDS =====
        'kataho_code',
        'kataho_grid_id',
        'kataho_plate_id',
        'kataho_address',
        'kataho_verified',
        'kataho_verified_at',
        
        // Security features
        'cctv_count',
        'guards_count',
        'fire_extinguishers',
        'cctv_stream_urls',
        
        // Nearby amenities
        'nearby_police',
        'nearby_fire',
        'nearby_hospital',
        'nearby_bank',
        'nearby_fuel',
        'nearby_market',
        
        // Images and documents
        'front_image',
        'interior_image',
        'exterior_image',
        'ownership_document',
        'tax_document',
        'fire_safety_document',
        'building_approval_document',
        
        // Cold storage
        'cold_storage',
        'temperature_min',
        'temperature_max',
        'humidity_control',
        
        // Description and details
        'description',
        'features',
        'amenities',
        
        // Status
        'status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'cctv_stream_urls' => 'array',
        'features' => 'array',
        'amenities' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'area_sqft' => 'decimal:2',
        'area_sqm' => 'decimal:2',
        'area' => 'decimal:2',
        'price' => 'decimal:2',
        'rate_per_sqft' => 'decimal:2',
        'minimum_rent' => 'decimal:2',
        'cold_storage' => 'boolean',
        'humidity_control' => 'boolean',
        'temperature_min' => 'decimal:2',
        'temperature_max' => 'decimal:2',
        'kataho_verified' => 'boolean',
        'approved_at' => 'datetime',
        'kataho_verified_at' => 'datetime',
    'facilities' => 'array',

    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the owner (property owner) for this warehouse.
     * Uses owner_id column.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user for this warehouse (alias for owner).
     * For compatibility with other parts of the code.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the admin who approved this warehouse
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the warehouse requests for this warehouse
     */
    public function warehouseRequests()
    {
        return $this->hasMany(WarehouseRequest::class, 'warehouse_id');
    }

    /**
     * Get the proposals for this warehouse
     */
    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'warehouse_id');
    }

    /**
     * Get the Kataho location for this warehouse
     */
    public function katahoLocation()
    {
        return $this->morphOne(KatahoLocation::class, 'reference');
    }

    /**
     * Get the active warehouse requests
     */
    public function activeRequests()
    {
        return $this->warehouseRequests()->whereIn('status', ['pending', 'approved']);
    }

    /**
     * Get the approved warehouse requests
     */
    public function approvedRequests()
    {
        return $this->warehouseRequests()->where('status', 'approved');
    }

    /**
     * Get the dispatches for this warehouse
     */
    public function dispatches()
    {
        return $this->hasMany(DispatchOrder::class, 'warehouse_id');
    }

    /**
     * Get the pickups for this warehouse
     */
    public function pickups()
    {
        return $this->hasMany(PickupRequest::class, 'warehouse_id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope for approved warehouses
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for pending warehouses
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for rejected warehouses
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for warehouses with cold storage
     */
    public function scopeWithColdStorage($query)
    {
        return $query->where('cold_storage', true);
    }

    /**
     * Scope for warehouses within a price range
     */
    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Scope for warehouses with minimum area
     */
    public function scopeMinArea($query, $area)
    {
        return $query->where('area', '>=', $area)
            ->orWhere('area_sqft', '>=', $area);
    }

    /**
     * Scope for warehouses with Kataho verification
     */
    public function scopeKatahoVerified($query)
    {
        return $query->where('kataho_verified', true);
    }

    // ==================== ACCESSORS ====================

    /**
     * Check if warehouse has cold storage
     */
    public function hasColdStorage()
    {
        return (bool) $this->cold_storage;
    }

    /**
     * Get full address
     */
    public function getFullAddressAttribute()
    {
        $parts = [];
        if ($this->address) $parts[] = $this->address;
        if ($this->city) $parts[] = $this->city;
        if ($this->state) $parts[] = $this->state;
        if ($this->country) $parts[] = $this->country;
        return implode(', ', $parts) ?: 'Address not available';
    }

    /**
     * Get formatted area
     */
    public function getFormattedAreaAttribute()
    {
        if ($this->area_sqft) {
            return number_format($this->area_sqft) . ' sq ft';
        }
        if ($this->area) {
            return number_format($this->area) . ' sq ft';
        }
        return 'N/A';
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute()
    {
        if ($this->price) {
            return 'रु ' . number_format($this->price, 2);
        }
        return 'Contact for price';
    }

    /**
     * Get status badge class for Bootstrap
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'approved' => 'success',
            'pending' => 'warning',
            'rejected' => 'danger',
            'draft' => 'secondary',
            'inactive' => 'secondary',
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    /**
     * Get status badge class for Tailwind
     */
    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'approved' => 'bg-green-100 text-green-600',
            'pending' => 'bg-yellow-100 text-yellow-600',
            'rejected' => 'bg-red-100 text-red-600',
            'draft' => 'bg-gray-100 text-gray-600',
            'inactive' => 'bg-gray-100 text-gray-600',
        ];
        return $classes[$this->status] ?? 'bg-gray-100 text-gray-600';
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        return ucfirst($this->status);
    }

    /**
     * Get formatted created date
     */
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at ? $this->created_at->format('M d, Y') : 'N/A';
    }

    /**
     * Get Kataho location data
     */
    public function getKatahoLocationAttribute()
    {
        if ($this->kataho_code) {
            return [
                'code' => $this->kataho_code,
                'grid_id' => $this->kataho_grid_id,
                'plate_id' => $this->kataho_plate_id,
                'address' => $this->kataho_address,
                'verified' => $this->kataho_verified,
                'url' => "https://kataho.app/location/{$this->kataho_code}",
                'maps_url' => "https://www.google.com/maps?q={$this->latitude},{$this->longitude}",
            ];
        }
        return null;
    }

    // ==================== MUTATORS ====================

    /**
     * Set the status and handle related updates.
     */
    public function setStatus($status, $approvedBy = null)
    {
        $this->status = $status;
        
        if ($status === 'approved') {
            $this->approved_at = now();
            if ($approvedBy) {
                $this->approved_by = $approvedBy;
            }
        }
        
        $this->save();
    }

    /**
     * Verify Kataho location
     */
    public function verifyKatahoLocation($code, $gridId, $address = null, $lat = null, $lng = null)
    {
        $this->update([
            'kataho_code' => $code,
            'kataho_grid_id' => $gridId,
            'kataho_address' => $address,
            'kataho_verified' => true,
            'kataho_verified_at' => now(),
            'latitude' => $lat ?? $this->latitude,
            'longitude' => $lng ?? $this->longitude,
        ]);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if warehouse is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if warehouse is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if warehouse is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if warehouse is available
     */
    public function isAvailable()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if warehouse has Kataho location
     */
    public function hasKatahoLocation()
    {
        return !empty($this->kataho_code) && $this->kataho_verified;
    }

    /**
     * Get the total area in sq ft
     */
    public function getTotalArea()
    {
        return $this->area_sqft ?? $this->area ?? 0;
    }

    /**
     * Get the price per sq ft
     */
    public function getPricePerSqft()
    {
        if ($this->price && $this->area) {
            return $this->price / $this->area;
        }
        return 0;
    }
}