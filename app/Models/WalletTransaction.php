<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'student_id',
        'fee_id',
        'amount',
        'type',
        'payment_method',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class)->withTrashed();
    }

    public function fee()
    {
        return $this->belongsTo(Fee::class);
    }
}
