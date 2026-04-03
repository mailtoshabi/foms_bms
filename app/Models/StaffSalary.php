<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSalary extends Model
{
    protected $fillable=[
        'staff_id','salary_month','salary_amount',
        'status','paid_date','remarks'
    ];

    protected $casts = [
        'paid_date' => 'date',
    ];

    public function getSalaryMonthFormattedAttribute()
    {
        if ($this->salary_month) {
            // Parse Y-m format (e.g., "2026-02") and create proper date
            list($year, $month) = explode('-', $this->salary_month);
            $date = \Carbon\Carbon::createFromDate((int)$year, (int)$month, 1);
            return $date->format('M Y');
        }
        return '-';
    }

    public function getPaidAmountAttribute()
    {
        // Calculate total paid amount from all payments
        return $this->payments()->sum('paid_amount') ?? 0;
    }

    public function getBalanceDueAttribute()
    {
        // Calculate remaining balance
        return $this->salary_amount - $this->paid_amount;
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function payments()
    {
        return $this->hasMany(StaffSalaryPayment::class, 'staff_salary_id');
    }
}






