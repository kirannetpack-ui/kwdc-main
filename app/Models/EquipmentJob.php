<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EquipmentJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'owner_id',
        'client_id',
        'equipment_owner_id',
        'job_type',
        'pickup_location',
        'delivery_location',
        'start_date',
        'end_date',
        'description',
        'location',
        'price',
        'proposed_price',
        'amount',
        'paid_amount',
        'client_counter_price',
        'proposal_message',
        'client_message',
        'status',
        'accepted_by_client_status',
        'accepted_by_owner_status',
        'accepted_at',
        'rejected_at',
        'proposed_at',
        'started_at',
        'completed_at',
        'request_date',
        'completion_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
        'proposed_price' => 'decimal:2',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'client_counter_price' => 'decimal:2',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'proposed_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'request_date' => 'datetime',
        'completion_date' => 'datetime',
    ];

    /**
     * Get the equipment for this job
     */
    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    /**
     * Get the owner of the equipment
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the client who requested the job
     */
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    /**
     * Get the equipment owner
     */
    public function equipmentOwner()
    {
        return $this->belongsTo(User::class, 'equipment_owner_id');
    }

    /**
     * Scope for pending jobs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for accepted jobs
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope for in-progress jobs
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope for completed jobs
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for rejected jobs
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for a specific owner
     */
    public function scopeForOwner($query, $ownerId)
    {
        return $query->where('owner_id', $ownerId);
    }

    /**
     * Scope for a specific client
     */
    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-yellow-100 text-yellow-600',
            'accepted' => 'bg-blue-100 text-blue-600',
            'in_progress' => 'bg-purple-100 text-purple-600',
            'completed' => 'bg-green-100 text-green-600',
            'rejected' => 'bg-red-100 text-red-600',
            'cancelled' => 'bg-gray-100 text-gray-600',
            'price_proposed' => 'bg-indigo-100 text-indigo-600',
        ];

        return $badges[$this->status] ?? 'bg-gray-100 text-gray-600';
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    /**
     * Check if job is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if job is accepted
     */
    public function isAccepted()
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if job is completed
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if job is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Accept the job
     */
    public function accept()
    {
        $this->status = 'accepted';
        $this->accepted_at = now();
        $this->save();
        return $this;
    }

    /**
     * Reject the job
     */
    public function reject()
    {
        $this->status = 'rejected';
        $this->rejected_at = now();
        $this->save();
        return $this;
    }

    /**
     * Start the job
     */
    public function start()
    {
        $this->status = 'in_progress';
        $this->started_at = now();
        $this->save();
        return $this;
    }

    /**
     * Complete the job
     */
    public function complete()
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->completion_date = now();
        $this->save();
        return $this;
    }

    /**
     * Propose a price
     */
    public function proposePrice($price, $message = null)
    {
        $this->proposed_price = $price;
        $this->proposal_message = $message;
        $this->status = 'price_proposed';
        $this->proposed_at = now();
        $this->save();
        return $this;
    }

    /**
     * Accept client's counter offer
     */
    public function acceptCounter($price)
    {
        $this->price = $price;
        $this->status = 'accepted';
        $this->accepted_at = now();
        $this->save();
        return $this;
    }
}