<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    protected $table='classes';

    protected $fillable = [
        'course_id',
        'class_type_id',   // 🔥 MUST EXIST
        'name',
        'slots_per_week',
        'days',
        'slot_time',
        'slot_duration',
        'admission_fee',
        'monthly_fee',
        'start_date',
        'is_completed'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function classType()
    {
        return $this->belongsTo(ClassType::class);
    }
}


