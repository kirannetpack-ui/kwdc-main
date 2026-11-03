<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehousePhoto extends Model
{
    protected $fillable = ['warehouse_id', 'photo_path'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}