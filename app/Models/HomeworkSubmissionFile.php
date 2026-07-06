<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeworkSubmissionFile extends Model
{
    use HasFactory;

    protected $table = 'homework_submission_files';

    protected $fillable = [
        'homework_submission_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size'
    ];

    public function submission()
    {
        return $this->belongsTo(HomeworkSubmission::class, 'homework_submission_id');
    }

    public function getFileUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    public function getFileSizeFormattedAttribute()
    {
        if (!$this->file_size) return null;
        $size = $this->file_size;
        if ($size >= 1024 * 1024) {
            return round($size / (1024 * 1024), 2) . ' MB';
        } elseif ($size >= 1024) {
            return round($size / 1024, 2) . ' KB';
        }
        return $size . ' bytes';
    }
}
