<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispatchItem extends Model
{
    protected $fillable = [
        'dispatch_order_id',
        'stock_id',
        'quantity',
        'unit_price',
        'total_price'
    ];
    
    public function dispatchOrder()
    {
        return $this->belongsTo(DispatchOrder::class);
    }
    
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}