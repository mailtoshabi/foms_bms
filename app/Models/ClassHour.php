<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassHour extends Model
{
    protected $fillable = [
        'class_room_id',
        'teacher_id',
        'duration',
        'google_meet_link',
        'status'
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
