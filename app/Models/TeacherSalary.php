<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSalary extends Model
{
    protected $fillable=[
        'teacher_id','salary_date','amount',
        'deposit_amount','deposit_return_date',
        'status','paid_amount','paid_date'
    ];
}





