<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherLeadNote extends Model
{
    protected $fillable = [
        'teacher_lead_id',
        'staff_id',
        'note',
        'status'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function lead()
    {
        return $this->belongsTo(TeacherLead::class, 'teacher_lead_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
