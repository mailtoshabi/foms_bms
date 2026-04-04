<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassHour extends Model
{
    protected $fillable = [
        'class_room_id',
        'teacher_id',
        'hourly_wage',
        'duration',
        'google_meet_link',
        'status',
        'has_fee_calculated',
        'has_salary_calculated',
        'class_started_at'
    ];

    protected $casts = [
        'has_fee_calculated'  => 'boolean',
        'has_salary_calculated'  => 'boolean',
        'class_started_at'  => 'date'
        ];

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function attendances()
    {
        return $this->hasMany(StudentAttendance::class);
    }
}
