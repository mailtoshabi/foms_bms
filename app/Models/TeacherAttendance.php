<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAttendance extends Model
{
    protected $table = 'teacher_attendance';

    protected $fillable = [
        'teacher_id',
        'class_room_id',
        'attendance_date',
        'is_present',
        'google_meet_link'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'is_present' => 'boolean'
    ];
}







