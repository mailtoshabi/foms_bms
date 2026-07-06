<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeworkSubmission extends Model
{
    use HasFactory;

    protected $table = 'homework_submissions';

    protected $fillable = [
        'homework_id',
        'student_id',
        'submitted_text',
        'total_mark',
        'mark_obtained',
        'teacher_comments',
        'graded_by',
        'graded_at'
    ];

    protected $casts = [
        'graded_at' => 'datetime',
        'total_mark' => 'float',
        'mark_obtained' => 'float'
    ];

    public function homework()
    {
        return $this->belongsTo(Homework::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function files()
    {
        return $this->hasMany(HomeworkSubmissionFile::class);
    }

    public function grader()
    {
        return $this->belongsTo(Teacher::class, 'graded_by')->withTrashed();
    }
}
