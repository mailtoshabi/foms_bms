<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeRefund extends Model
{
    protected $fillable = [
        'fee_id',
        'amount',
        'payment_method',
        'refund_date',
        'notes'
    ];

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }
}
