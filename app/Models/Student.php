<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;

class Student extends Authenticatable
{
    use SoftDeletes;

    protected $table = 'students';

    protected $fillable = [
        'admission_no',
        'student_lead_id',

        'name',
        'dob',
        'email',
        'contact_number',
        'whatsapp_number',
        'parent_name',
        'address',

        'phone',
        'password',

        'photo',
        'id_proof',

        // class schedule
        'classes_per_week',
        'selected_days',
        'time_slot',
        'starting_date',
        'is_admission_fee_exempted',
        'admission_fee_discount',
        'is_monthly_fee_exempted',
        'monthly_fee_discount',

        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'dob' => 'date',
        'starting_date' => 'date',
        'last_login_at' => 'datetime',
        'selected_days' => 'array',
    ];

    public function class_rooms()
    {
        return $this->belongsToMany(
            ClassRoom::class,
            'student_class_room',   // pivot table
            'student_id',
            'class_room_id'
        )->withPivot('assigned_date')->withTimestamps();
    }
    public function lead()
    {
        return $this->belongsTo(StudentLead::class, 'student_lead_id');
    }

    public function fees()
    {
        return $this->hasMany(Fee::class)->latest();
    }

    public function attendances()
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function notes()
    {
        return $this->hasMany(ClassNote::class);
    }

    public function getDobFormattedAttribute()
    {
        return $this->dob?->format('d M Y');
    }


    protected static function booted()
    {
        static::creating(function ($student) {

            $now = now();
            $year = $now->format('y');  // 2-digit year  e.g. "26"
            $month = $now->format('m');  // 2-digit month e.g. "04"

            // Serial: total students admitted in the same month + 1
            $countThisMonth = Student::whereYear('created_at', $now->year)
                ->whereMonth('created_at', $now->month)
                ->count();

            $serial = str_pad($countThisMonth + 1, 2, '0', STR_PAD_LEFT);

            // Format: FA/{YY}/{MM}/{serial}
            // Example: FA/26/04/27
            $student->admission_no = 'FA/' . $year . '/' . $month . '/' . $serial;

        });
    }
}
