<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auction extends Model
{
    protected $fillable = [
        'warehouse_request_id', 'scheduled_date', 'status', 'notes'
    ];

    public function warehouseRequest()
    {
        return $this->belongsTo(WarehouseRequest::class);
    }
}