<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeworkFile extends Model
{
    use HasFactory;

    protected $table = 'homework_files';

    protected $fillable = [
        'homework_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function homework()
    {
        return $this->belongsTo(Homework::class);
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
