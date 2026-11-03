<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoaderJobLog extends Model
{
    protected $fillable = [
        'loader_id', 'assignment_id', 'check_in_time', 'check_out_time',
        'hours_worked', 'amount_earned', 'notes'
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'hours_worked' => 'decimal:2',
        'amount_earned' => 'decimal:2',
    ];

    public function loader(): BelongsTo
    {
        return $this->belongsTo(Loader::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(LoaderAssignment::class);
    }
}