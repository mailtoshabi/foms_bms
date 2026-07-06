<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassHourBuzzer extends Model
{
    protected $table = 'class_hour_buzzers';

    protected $fillable = [
        'class_hour_id',
        'student_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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
