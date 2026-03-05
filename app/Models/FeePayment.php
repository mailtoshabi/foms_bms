<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    protected $fillable=[
        'fee_id','paid_amount','paid_date'
    ];
}




