<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    protected $fillable = [
        'warehouse_request_id', 'provider', 'policy_number', 'premium',
        'start_date', 'end_date', 'status'
    ];

    public function warehouseRequest()
    {
        return $this->belongsTo(WarehouseRequest::class);
    }

    public function client()
    {
        return $this->hasOneThrough(
            User::class,
            WarehouseRequest::class,
            'id',
            'id',
            'warehouse_request_id',
            'client_id'
        );
    }
}