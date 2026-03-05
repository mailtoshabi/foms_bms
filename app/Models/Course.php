<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'course_fee',
    ];

    public function category()
    {
        return $this->belongsTo(CourseCategory::class,'category_id');
    }
}



