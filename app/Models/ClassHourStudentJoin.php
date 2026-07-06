<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassHourStudentJoin extends Model
{
    protected $table = 'class_hour_student_joins';
    
    public $timestamps = false;

    protected $fillable = [
        'class_hour_id',
        'student_id',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function classHour()
    {
        return $this->belongsTo(ClassHour::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
