<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSalary extends Model
{
    protected $fillable=[
        'staff_id','salary_month','salary_amount',
        'status','paid_amount','paid_date','remarks'
    ];
}






