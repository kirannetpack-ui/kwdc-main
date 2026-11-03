<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'driver_id',
        'warehouse_id',
        'tracking_id',
        'invoice_no',
        'pickup_address',
        'destination_address',
        'pickup_latitude',
        'pickup_longitude',
        'destination_latitude',
        'destination_longitude',
        'items_description',
        'weight',
        'scheduled_date',
        'scheduled_time',
        'driver_earning',
        'admin_margin',
        'tax_amount',
        'grand_total',
        'payment_status',
        'payment_due_date',
        'notes',
        'picked_up_at',
        'delivered_at',
        'cancelled_at',
        'total_distance',
        'total_price',
        'status',
        'bill_type',
        'pan_number',
        'assigned_at',
        'started_at',
        'completed_at',
        // ===== KATAHO FIELDS =====
        'destination_kataho_code',
        'destination_kataho_grid_id',
        'destination_latitude',
        'destination_longitude',
        'kataho_code',
        'kataho_grid_id',
    ];

    protected $casts = [
        'total_distance' => 'decimal:2',
        'total_price' => 'decimal:2',
        'driver_earning' => 'decimal:2',
        'admin_margin' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'weight' => 'decimal:2',
        'scheduled_date' => 'date',
        'assigned_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'payment_due_date' => 'date',
        'pickup_latitude' => 'decimal:8',
        'pickup_longitude' => 'decimal:8',
        'destination_latitude' => 'decimal:8',
        'destination_longitude' => 'decimal:8',
    ];

    // ==================== RELATIONSHIPS ====================

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function stops()
    {
        return $this->hasMany(PickupStop::class);
    }

    public function pickupStops()
    {
        return $this->stops();
    }

    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'driver_id', 'driver_id');
    }

    public function katahoLocations()
    {
        return $this->morphMany(KatahoLocation::class, 'reference');
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    // ==================== ACCESSORS ====================

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'assigned' => 'info',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getFormattedTotalPriceAttribute()
    {
        return 'रु ' . number_format($this->total_price, 2);
    }

    public function getFormattedTotalDistanceAttribute()
    {
        return number_format($this->total_distance, 2) . ' km';
    }

    public function getKatahoNavigationLinkAttribute()
    {
        if ($this->destination_kataho_code) {
            return "https://kataho.app/navigate/{$this->destination_kataho_code}";
        }
        return null;
    }

    public function getGoogleMapsLinkAttribute()
    {
        if ($this->destination_latitude && $this->destination_longitude) {
            return "https://www.google.com/maps?q={$this->destination_latitude},{$this->destination_longitude}";
        }
        return null;
    }

    // ==================== HELPER METHODS ====================

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isAssigned()
    {
        return $this->status === 'assigned';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function hasKatahoLocation()
    {
        return !empty($this->destination_kataho_code);
    }

    public function hasCoordinates()
    {
        return !empty($this->destination_latitude) && !empty($this->destination_longitude);
    }

    public function getRouteUrl()
    {
        if ($this->hasKatahoLocation()) {
            return $this->kataho_navigation_link;
        }
        return $this->google_maps_link;
    }
}
