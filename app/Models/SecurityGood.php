<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityGood extends Model
{
    protected $fillable = [
        'agency_id', 'item_name', 'category', 'model', 'specifications',
        'quantity_available', 'unit_price', 'is_rental', 'rental_rate_per_day',
        'description', 'image', 'status'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'rental_rate_per_day' => 'decimal:2',
        'is_rental' => 'boolean',
    ];

protected $table = 'security_goods';

    public function agency(): BelongsTo
    {
    return $this->belongsTo(SecurityAgency::class, 'agency_id');
    }
}