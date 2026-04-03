<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    protected $table='class_rooms';

    protected $fillable = [
        'course_id',
        'class_type_id',   // 🔥 MUST EXIST
        'name',
        'classes_per_week',
        // 'days',
        'selected_days',
        'time_slot',
        'slot_duration',
        'admission_fee',
        'monthly_fee',
        'starting_date',
        'is_completed'
    ];

    protected $casts = [
        'selected_days' => 'array',
        'starting_date' => 'date',
        'is_completed'  => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function classType()
    {
        return $this->belongsTo(ClassType::class);
    }

    public function notes()
    {
        return $this->hasMany(ClassNote::class);
    }

public function students()
{
    return $this->belongsToMany(
        Student::class,
        'student_class_room',
        'class_room_id',
        'student_id'
    )->withPivot('assigned_date')->withTimestamps();
}

public function teachers()
{
    return $this->belongsToMany(
        Teacher::class,
        'teacher_class_room'
    )->withTimestamps()
     ->withPivot('hourly_wage','assigned_at');
}


public function classHours()
{
    return $this->hasMany(ClassHour::class, 'class_room_id')
        ->latest();
}

}


