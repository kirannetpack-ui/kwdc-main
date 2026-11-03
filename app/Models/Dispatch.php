<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dispatch extends Model
{
    use HasFactory;

    protected $table = 'dispatches';

    protected $fillable = [
        'request_number',
        'client_id',
        'client_code',
        'driver_id',
        'driver_code',
        'pickup_location',
        'delivery_location',
        'distance_km',
        'item_description',
        'quantity',
        'weight_kg',
        'status',
        'price',
        'driver_earning',
        'admin_commission',
        'proposed_price',
        'proposal_status',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'delivery_proof',
        'tracking_code',
        'special_instructions',
        'vehicle_id'
    ];

    protected $casts = [
        'distance_km' => 'decimal:2',
        'price' => 'decimal:2',
        'driver_earning' => 'decimal:2',
        'admin_commission' => 'decimal:2',
        'proposed_price' => 'decimal:2',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Generate unique request number
    public static function generateRequestNumber()
    {
        $year = now()->year;
        $month = now()->format('m');
        $lastDispatch = self::where('request_number', 'like', "DISP-{$year}{$month}-%")
            ->orderBy('request_number', 'desc')
            ->first();

        if ($lastDispatch) {
            $lastNumber = intval(substr($lastDispatch->request_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "DISP-{$year}{$month}-{$newNumber}";
    }

    // Relationships
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    // Status check methods
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

    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeByClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeByDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }
}