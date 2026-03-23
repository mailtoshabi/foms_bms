<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSalary extends Model
{
    protected $fillable = [
        'teacher_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes'
    ];

    protected $casts = [
        'payment_date' => 'date'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}





