<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Student extends Authenticatable
{
    protected $fillable = [
        'name','contact_number','email','password'
    ];

    public function class_rooms()
    {
        return $this->belongsToMany(ClassRoom::class,'student_class');
    }

    public function lead()
{
    return $this->belongsTo(StudentLead::class, 'student_lead_id');
}
}
