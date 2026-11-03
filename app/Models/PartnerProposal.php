<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerProposal extends Model
{
    protected $fillable = [
        'job_type', 'job_id', 'partner_id', 'proposed_price',
        'admin_margin', 'status', 'negotiation_notes'
    ];

    public function job()
    {
        return $this->morphTo();
    }

    public function partner()
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}