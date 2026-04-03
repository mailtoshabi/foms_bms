<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeNotification extends Model
{
    protected $fillable = [
        'fee_id',
        'type',
        'recipient_phone',
        'message',
        'status',
        'response'
    ];

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }
}
