<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Homework extends Model
{
    use HasFactory;

    protected $table = 'homeworks';

    protected $fillable = [
        'class_room_id',
        'teacher_id',
        'title',
        'content'
    ];

    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class)->withTrashed();
    }

    public function files()
    {
        return $this->hasMany(HomeworkFile::class);
    }

    public function submissions()
    {
        return $this->hasMany(HomeworkSubmission::class);
    }
}
