<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentRequest extends Model
{
    use HasFactory;

    protected $table = 'equipment_requests';

    protected $fillable = [
        'client_id',
        'location',
        'special_requirements',
        'preferred_brands',
        'budget_range',
        'quoted_price',
        'assigned_equipment_id',
        'notes',
        'equipment_id',
        'owner_id',
        'title',
        'description',
        'equipment_type',
        'equipment_name',
        'quantity',
        'start_date',
        'end_date',
        'duration_days',
        'budget',
        'proposed_price',
        'agreed_price',
        'status',
        'approved_at',
        'rejected_at',
        'completed_at',
        'cancelled_at',
    ];

    protected $casts = [
        'preferred_brands' => 'array',
        'quoted_price' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'quantity' => 'decimal:2',
        'budget' => 'decimal:2',
        'proposed_price' => 'decimal:2',
        'agreed_price' => 'decimal:2',
    ];

    // Relationships
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function assignedEquipment()
    {
        return $this->belongsTo(Equipment::class, 'assigned_equipment_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'approved', 'assigned']);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    // Accessors
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'assigned' => 'info',
            'completed' => 'success',
            'cancelled' => 'secondary',
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute()
    {
        return ucfirst($this->status);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }
}
