<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'driver_id',           // Add this - for driver relationship
        'warehouse_id',
        'warehouse_request_id',
        'job_type',
        'job_id',
        'proposed_price',
        'negotiated_price',
        'negotiation_message',
        'description',
        'valid_until',
        'status',
        'accepted_at',
        'rejected_at',
        'expired_at',
        'counter_price',       // Keep this
        'client_counter_price', // Add this for client counter offers
        'message',              // Add this for driver messages
        'client_message',       // Add this for client messages
        'notes',
        'driver_response_at',   // Add this
        'client_response_at',   // Add this
    ];

    protected $casts = [
        'proposed_price' => 'decimal:2',
        'negotiated_price' => 'decimal:2',
        'counter_price' => 'decimal:2',
        'client_counter_price' => 'decimal:2',
        'valid_until' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'expired_at' => 'datetime',
        'driver_response_at' => 'datetime',
        'client_response_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONSHIPS
    // ============================================================
    
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

    public function warehouseRequest()
    {
        return $this->belongsTo(WarehouseRequest::class);
    }

    // ============================================================
    // SCOPES
    // ============================================================
    
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeNegotiating($query)
    {
        return $query->where('status', 'negotiating');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'negotiating'])
            ->where(function($q) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>', now());
            });
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForDriver($query, $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    // ============================================================
    // ACCESSORS & MUTATORS
    // ============================================================
    
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'accepted' => 'success',
            'rejected' => 'danger',
            'negotiating' => 'info',
            'expired' => 'secondary',
            'countered' => 'primary',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'negotiating' => 'Negotiating',
            'expired' => 'Expired',
            'countered' => 'Countered',
        ];

        return $statuses[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusIconAttribute()
    {
        $icons = [
            'pending' => 'fa-clock',
            'accepted' => 'fa-check-circle',
            'rejected' => 'fa-times-circle',
            'negotiating' => 'fa-handshake',
            'expired' => 'fa-hourglass-end',
            'countered' => 'fa-exchange-alt',
        ];

        return $icons[$this->status] ?? 'fa-question-circle';
    }

    public function getFormattedPriceAttribute()
    {
        return 'रू ' . number_format($this->proposed_price, 2);
    }

    public function getFormattedCounterPriceAttribute()
    {
        if ($this->counter_price) {
            return 'रू ' . number_format($this->counter_price, 2);
        }
        return null;
    }

    // ============================================================
    // HELPER METHODS
    // ============================================================
    
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isNegotiating()
    {
        return $this->status === 'negotiating';
    }

    public function isExpired()
    {
        return $this->status === 'expired' || 
               ($this->valid_until && $this->valid_until->isPast() && in_array($this->status, ['pending', 'negotiating']));
    }

    public function markAsExpired()
    {
        if ($this->isExpired() && $this->status !== 'expired') {
            $this->update([
                'status' => 'expired',
                'expired_at' => now()
            ]);
            return true;
        }
        return false;
    }

    public function accept()
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now()
        ]);
        return $this;
    }

    public function reject()
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now()
        ]);
        return $this;
    }

    public function counter($price, $message = null)
    {
        $this->update([
            'counter_price' => $price,
            'negotiation_message' => $message,
            'status' => 'negotiating',
            'client_response_at' => now()
        ]);
        return $this;
    }

    public function respondToCounter($price, $message = null)
    {
        $this->update([
            'proposed_price' => $price,
            'counter_price' => null,
            'negotiation_message' => $message,
            'status' => 'pending',
            'driver_response_at' => now()
        ]);
        return $this;
    }

    // Check if proposal is for a specific user
    public function isForUser($userId)
    {
        return $this->client_id === $userId || $this->driver_id === $userId;
    }

    // Get the other party (not the given user)
    public function getOtherParty($userId)
    {
        if ($this->client_id === $userId) {
            return $this->driver;
        }
        if ($this->driver_id === $userId) {
            return $this->client;
        }
        return null;
    }
}