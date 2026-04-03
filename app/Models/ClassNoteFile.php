<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ClassNoteFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_note_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function note()
    {
        return $this->belongsTo(ClassNote::class, 'class_note_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors (Optional Helpers)
    |--------------------------------------------------------------------------
    */

    // Get full file URL
    // public function getFileUrlAttribute()
    // {
    //     return asset('storage/' . $this->file_path);
    // }

    // Human readable file size
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

    public function getFileUrlAttribute()
    {
        return Storage::url($this->file_path);
    }
}
