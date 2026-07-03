<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $table = 'holidays';

    protected $fillable = [
        'title',
        'description',
        'date',
        'target_type',
        'class_target_type',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    public function teachers()
    {
        return $this->morphedByMany(Teacher::class, 'targetable', 'holiday_targets');
    }

    public function students()
    {
        return $this->morphedByMany(Student::class, 'targetable', 'holiday_targets');
    }

    public function classRooms()
    {
        return $this->morphedByMany(ClassRoom::class, 'targetable', 'holiday_targets');
    }
}
