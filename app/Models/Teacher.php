<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;

class Teacher extends Authenticatable
{
    use SoftDeletes;

    protected $table = 'teachers';

    protected $fillable = [
        'admission_no',
        'teacher_lead_id',

        'name',
        'dob',
        'email',
        'contact_number',
        'whatsapp_number',
        'upi_number',
        'address',

        'qualification',
        'experience',

        'phone',
        'password',

        'photo',
        'id_proof',

        'status',

        'salary_cycle_day',
        'salary_amount'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'dob' => 'date',
        'last_login_at' => 'datetime',
        'salary_cycle_day' => 'integer',
    ];

    public function salaries()
    {
        return $this->hasMany(TeacherSalary::class)
            ->latest('payment_date');
    }

    // public function attendances()
    // {
    //     return $this->hasMany(TeacherAttendance::class);
    // }


    public function getDobFormattedAttribute()
    {
        return $this->dob?->format('d M Y');
    }

    protected static function booted()
    {
        static::creating(function ($teacher) {

            $lastTeacher = Teacher::latest('id')->first();

            $nextNumber = $lastTeacher ? $lastTeacher->id + 1 : 1;

            $year = now()->year;

            $teacher->admission_no = 'FOMS-T-'.$year.'-' . str_pad($nextNumber,4,'0',STR_PAD_LEFT);

        });
    }

    public function notes()
{
    return $this->hasMany(ClassNote::class);
}

    public function classRooms()
    {
        return $this->belongsToMany(
            ClassRoom::class,
            'teacher_class_room'
        )
        ->withPivot('hourly_wage','assigned_at')
        ->withTimestamps();
    }

    public function completedClassRooms()
    {
        return $this->belongsToMany(
            ClassRoom::class,
            'teacher_class_room'
        )
        ->withPivot('hourly_wage','assigned_at')
        ->withTimestamps();
    }

public function lead()
{
    return $this->belongsTo(TeacherLead::class,'teacher_lead_id');
}

public function getSalaryCreditDayAttribute()
{
    if (!$this->salary_cycle_day) {
        return null;
    }

    $date = Carbon::create(
        now()->year,
        now()->month,
        min($this->salary_cycle_day, now()->daysInMonth)
    );

    return $date->addDays(10)->day;
}

public function getSalaryCreditDateAttribute()
{
    if (!$this->salary_cycle_day) {
        return null;
    }

    $date = Carbon::create(
        now()->year,
        now()->month,
        min($this->salary_cycle_day, now()->daysInMonth)
    );

    return $date->addDays(10)->format('d M Y');
}
}
