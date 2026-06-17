<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherDeposit extends Model
{
    protected $table = 'teacher_deposits';

    protected $fillable = [
        'teacher_id',
        'teacher_salary_id',
        'amount',
        'paid_amount',
        'deposited_date',
        'due_date',
        'status',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'deposited_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class)->withTrashed();
    }

    public function salary()
    {
        return $this->belongsTo(TeacherSalary::class, 'teacher_salary_id');
    }
}
