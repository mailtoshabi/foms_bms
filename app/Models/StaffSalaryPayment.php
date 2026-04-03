<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSalaryPayment extends Model
{
    protected $fillable = [
        'staff_salary_id',
        'paid_amount',
        'paid_date',
        'payment_method',
        'notes'
    ];

    protected $casts = [
        'paid_date' => 'date',
    ];

    public function staffSalary()
    {
        return $this->belongsTo(StaffSalary::class, 'staff_salary_id');
    }

    public function staff()
    {
        return $this->through('staffSalary')->has('staff');
    }
}
