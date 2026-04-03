<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    protected $fillable = [
        'fee_id',
        'paid_amount',
        'paid_date',
        'payment_method',
        'notes'
    ];

    // ✅ Each payment belongs to a fee
    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }
}
