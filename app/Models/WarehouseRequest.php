<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseRequest extends Model
{
    protected $table = 'warehouse_requests';
    
    protected $fillable = [
        'client_id',
        'warehouse_id',
        'assigned_warehouse_id',
        'required_area',
        'space_required',
        'duration_months',
        'purpose',
        'status',
        'preferred_start_date',
        'contact_person',
        'contact_phone',
        'invoice_path',
        'packing_list_path',
        'insurance_path',
        'agreed_price',
        'agreed_price_per_unit',
        'monthly_rent',
        'last_invoice_date',
        'goods_auctioned',
        'assigned_at',
        'completed_at',
        'cancelled_at',
        'cancellation_notes',
        'contract_end_date',
        'contract_signed_at',
        'contract_expires_at',
    ];
    
    protected $casts = [
        'preferred_start_date' => 'date',
        'contract_signed_at' => 'datetime',
        'contract_expires_at' => 'date',
        'contract_end_date' => 'date',
        'last_invoice_date' => 'date',
        'goods_auctioned' => 'boolean',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'agreed_price' => 'decimal:2',
        'agreed_price_per_unit' => 'decimal:2',
        'monthly_rent' => 'decimal:2',
        'required_area' => 'decimal:2',
        'space_required' => 'decimal:2',
    ];
    
    // ==================== RELATIONSHIPS ====================
    
    /**
     * Get the client who made this request.
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
    
    /**
     * Get the original warehouse requested.
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }
    
    /**
     * Get the assigned warehouse (if different from requested).
     */
    public function assignedWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'assigned_warehouse_id');
    }
    
    /**
     * Get the dispatch order associated with this request.
     */
    public function dispatchOrder()
    {
        return $this->hasOne(DispatchOrder::class);
    }
    
    /**
     * Get all stocks associated with this request.
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }
    
    /**
     * Get the invoice associated with this request.
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
    
    /**
     * Get the insurance associated with this request.
     */
    public function insurance()
    {
        return $this->hasOne(Insurance::class);
    }
    
    /**
     * Get the proposal associated with this request.
     */
    public function proposal()
    {
        return $this->hasOne(Proposal::class, 'warehouse_request_id');
    }
    
    // ==================== SCOPES ====================
    
    /**
     * Scope a query to only pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope a query to only approved requests.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    /**
     * Scope a query to only assigned requests.
     */
    public function scopeAssigned($query)
    {
        return $query->where('status', 'assigned');
    }
    
    /**
     * Scope a query to only completed requests.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
    
    /**
     * Scope a query to only cancelled requests.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
    
    /**
     * Scope a query to only rejected requests.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
    
    /**
     * Scope a query to requests by client.
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }
    
    /**
     * Scope a query to requests for a specific warehouse.
     */
    public function scopeForWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }
    
    /**
     * Scope a query to get active requests.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'approved', 'assigned']);
    }
    
    // ==================== ACCESSORS ====================
    
    /**
     * Get formatted required area.
     */
    public function getFormattedRequiredAreaAttribute()
    {
        return number_format($this->required_area) . ' sq ft';
    }

    public function getRequiredAreaAttribute($value)
    {
        return $value ?? $this->attributes['space_required'] ?? null;
    }

    public function getRequiredSpaceAttribute()
    {
        return $this->required_area;
    }

    public function getSpaceRequiredAttribute($value)
    {
        return $value ?? $this->attributes['required_area'] ?? null;
    }

    public function getContractEndDateAttribute($value)
    {
        return $value ?? $this->attributes['contract_expires_at'] ?? null;
    }

    public function getMonthlyRentAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }

        if ($this->agreed_price !== null) {
            return $this->agreed_price;
        }

        if ($this->warehouse && $this->required_area) {
            return $this->required_area * ($this->warehouse->price ?? 0);
        }

        return 0;
    }
    
    /**
     * Get formatted agreed price.
     */
    public function getFormattedAgreedPriceAttribute()
    {
        return 'रु ' . number_format($this->agreed_price ?? 0, 2);
    }
    
    /**
     * Get status badge class.
     */
    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'assigned' => 'bg-blue-100 text-blue-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'rejected' => 'bg-red-100 text-red-800',
        ];
        
        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800';
    }
    
    /**
     * Get status text.
     */
    public function getStatusTextAttribute()
    {
        $texts = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'assigned' => 'Warehouse Assigned',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'rejected' => 'Rejected',
        ];
        
        return $texts[$this->status] ?? ucfirst($this->status);
    }
    
    /**
     * Get status badge color for Bootstrap.
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'approved' => 'success',
            'assigned' => 'info',
            'completed' => 'success',
            'cancelled' => 'secondary',
            'rejected' => 'danger',
        ];
        
        return $badges[$this->status] ?? 'secondary';
    }
    
    // ==================== MUTATORS ====================
    
    /**
     * Set the status and handle related updates.
     */
    public function setStatus($status, $notes = null)
    {
        $this->status = $status;
        
        if ($status === 'approved') {
            $this->approved_at = now();
        } elseif ($status === 'assigned') {
            $this->assigned_at = now();
        } elseif ($status === 'completed') {
            $this->completed_at = now();
        } elseif ($status === 'cancelled') {
            $this->cancelled_at = now();
            $this->cancellation_notes = $notes;
        }
        
        $this->save();
    }
    
    // ==================== HELPER METHODS ====================
    
    /**
     * Check if request is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }
    
    /**
     * Check if request is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }
    
    /**
     * Check if request is assigned.
     */
    public function isAssigned()
    {
        return $this->status === 'assigned';
    }
    
    /**
     * Check if request is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }
    
    /**
     * Check if request is cancelled.
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }
    
    /**
     * Check if request is rejected.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }
    
    /**
     * Calculate estimated monthly rent.
     */
    public function getEstimatedMonthlyRent()
    {
        if ($this->warehouse) {
            return $this->required_area * $this->warehouse->price;
        }
        return 0;
    }
    
    /**
     * Calculate total rent for duration.
     */
    public function getTotalRent()
    {
        return $this->getEstimatedMonthlyRent() * $this->duration_months;
    }
    
    /**
     * Check if request has a proposal.
     */
    public function hasProposal()
    {
        return $this->proposal()->exists();
    }
    
    /**
     * Get the latest proposal for this request.
     */
    public function getLatestProposal()
    {
        return $this->proposal()->latest()->first();
    }
}
