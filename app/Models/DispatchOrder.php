<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispatchOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'driver_id',
        'warehouse_id',
        'warehouse_request_id',
        'tracking_id',
        'invoice_no',
        'pickup_address',
        'delivery_address',
        'pickup_contact_person',
        'pickup_contact_phone',
        'pickup_latitude',
        'pickup_longitude',
        'delivery_latitude',
        'delivery_longitude',
        'total_distance',
        'base_price',
        'tax_amount',
        'grand_total',
        'driver_earning',
        'commission',
        'admin_margin',
        'bill_type',
        'pan_number',
        'status',
        'payment_status',
        'payment_method',
        'notes',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'cancelled_at',
        'proof_image',
        'client_rating',
        'client_review',
        // ===== KATAHO FIELDS =====
        'pickup_kataho_code',
        'pickup_kataho_grid_id',
        'delivery_kataho_code',
        'delivery_kataho_grid_id',
    ];

    protected $casts = [
        'total_distance' => 'decimal:2',
        'base_price' => 'decimal:2',
        'driver_earning' => 'decimal:2',
        'commission' => 'decimal:2',
        'client_rating' => 'decimal:1',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'pickup_latitude' => 'decimal:8',
        'pickup_longitude' => 'decimal:8',
        'delivery_latitude' => 'decimal:8',
        'delivery_longitude' => 'decimal:8',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Get the client who created this dispatch order
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the driver assigned to this dispatch order
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Get the primary vehicle for the assigned driver.
     */
    public function vehicle()
    {
        return $this->hasOne(Vehicle::class, 'driver_id', 'driver_id');
    }

    /**
     * Get the warehouse associated with this dispatch order
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Get the warehouse request associated with this dispatch order
     */
    public function warehouseRequest()
    {
        return $this->belongsTo(WarehouseRequest::class, 'warehouse_request_id');
    }

    /**
     * Get the inventory items for this dispatch order
     */
    public function items()
    {
        return $this->hasMany(DispatchItem::class);
    }

    /**
     * Get the delivery stops for this dispatch order
     */
    public function stops()
    {
        return $this->hasMany(DeliveryStop::class);
    }

    public function deliveryStops()
    {
        return $this->stops();
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

    public function scopePickedUp($query)
    {
        return $query->where('status', 'picked_up');
    }

    public function scopeOnTheWay($query)
    {
        return $query->where('status', 'on_the_way');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
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
            'picked_up' => 'primary',
            'on_the_way' => 'info',
            'delivered' => 'success',
            'cancelled' => 'danger',
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getFormattedBasePriceAttribute()
    {
        return 'रु ' . number_format($this->base_price, 2);
    }

    public function getFormattedDriverEarningAttribute()
    {
        return 'रु ' . number_format($this->driver_earning, 2);
    }

    public function getFormattedTotalDistanceAttribute()
    {
        return number_format($this->total_distance, 2) . ' km';
    }

    public function getKatahoPickupLinkAttribute()
    {
        if ($this->pickup_kataho_code) {
            return "https://kataho.app/navigate/{$this->pickup_kataho_code}";
        }
        return null;
    }

    public function getKatahoDeliveryLinkAttribute()
    {
        if ($this->delivery_kataho_code) {
            return "https://kataho.app/navigate/{$this->delivery_kataho_code}";
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

    public function isPickedUp()
    {
        return $this->status === 'picked_up';
    }

    public function isOnTheWay()
    {
        return $this->status === 'on_the_way';
    }

    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function hasKatahoLocation()
    {
        return !empty($this->pickup_kataho_code) || !empty($this->delivery_kataho_code);
    }

    public function assignDriver($driverId)
    {
        $this->update([
            'driver_id' => $driverId,
            'status' => 'assigned',
            'assigned_at' => now(),
        ]);
    }

    public function markPickedUp()
    {
        $this->update([
            'status' => 'picked_up',
            'picked_up_at' => now(),
        ]);
    }

    public function markDelivered()
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);
    }

    public function markCancelled()
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
