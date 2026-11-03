<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['dispatch_order_id', 'stock_id', 'quantity'];

    public function dispatchOrder()
    {
        return $this->belongsTo(DispatchOrder::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}