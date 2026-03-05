<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadNote extends Model
{
    protected $fillable = [
        'student_lead_id',
        'staff_id',
        'note',
        'status'
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function lead()
    {
        return $this->belongsTo(StudentLead::class, 'student_lead_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
