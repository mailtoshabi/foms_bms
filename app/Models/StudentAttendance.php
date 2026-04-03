<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    protected $table='student_attendance';
    protected $fillable = [
        'class_hour_id',
        'student_id',
        'is_present'
    ];
    protected $casts = [
        'is_present' => 'boolean',
    ];

    public function classHour()
    {
        return $this->belongsTo(\App\Models\ClassHour::class);
    }
}








