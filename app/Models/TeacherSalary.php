<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSalary extends Model
{
    protected $fillable = [
        'teacher_id',
        'cycle_start',
        'cycle_end',
        'total_hours',
        'total_amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
        'status'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'cycle_start' => 'date',
        'cycle_end' => 'date'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class)->withTrashed();
    }
}





