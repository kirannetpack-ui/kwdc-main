<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryStop extends Model
{
    protected $fillable = [
        'dispatch_order_id',
        'stop_number',
        'recipient_name',
        'recipient_phone',
        'address',
        'latitude',
        'longitude',
        'boxes_count',
        'notes',
        'status',
        'delivered_at',
        'invoice_document',
        'invoice_number',
        'received_by',
        'received_at',
        'signature'
    ];
    
    protected $casts = [
        'delivered_at' => 'datetime',
        'received_at' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];
    
    public function dispatchOrder()
    {
        return $this->belongsTo(DispatchOrder::class);
    }

    public function getStopOrderAttribute()
    {
        return $this->stop_number;
    }
}
