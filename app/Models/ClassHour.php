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
        'join_teacher_at',
        'join_student_at',
        'completed_at',
        'link_updated_at',
    ];

    protected $casts = [
        'has_fee_calculated' => 'boolean',
        'has_salary_calculated' => 'boolean',
        'join_teacher_at' => 'datetime',
        'join_student_at' => 'datetime',
        'completed_at' => 'datetime',
        'link_updated_at' => 'datetime'
    ];

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class)->withTrashed();
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class)->withTrashed();
    }

    public function attendances()
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function joins()
    {
        return $this->hasMany(ClassHourStudentJoin::class, 'class_hour_id');
    }

    public function buzzers()
    {
        return $this->hasMany(ClassHourBuzzer::class, 'class_hour_id');
    }
}
