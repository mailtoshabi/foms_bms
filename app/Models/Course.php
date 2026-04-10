<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'course_fee',
    ];

    public function category()
    {
        return $this->belongsTo(CourseCategory::class,'category_id');
    }

    public function classRooms()
    {
        return $this->hasMany(ClassRoom::class);
    }
}



