<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerEarning extends Model
{
    protected $fillable = [
        'partner_id',
        'order_type',
        'order_id',
        'amount',
        'status',
        'earned_at',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'earned_at' => 'datetime',
    ];

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }

    // Optional: Helper to mark as paid
    public function markAsPaid()
    {
        $this->status = 'paid';
        $this->save();
        return $this;
    }
}