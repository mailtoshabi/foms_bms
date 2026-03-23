<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;

class Student extends Authenticatable
{

    protected $table = 'students';

    protected $fillable = [
        'admission_no',
        'student_lead_id',

        'name',
        'dob',
        'email',
        'contact_number',
        'whatsapp_number',
        'parent_name',
        'address',

        'phone',
        'password',

        'photo',
        'id_proof',

        // class schedule
        'classes_per_week',
        'selected_days',
        'time_slot',
        'starting_date',
        'is_admission_fee_exempted',
        'is_monthly_fee_exempted',

        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'dob' => 'date',
        'starting_date' => 'date',
        'last_login_at' => 'datetime',
        'selected_days' => 'array',
    ];

    public function class_rooms()
{
    return $this->belongsToMany(
        ClassRoom::class,
        'student_class_room',   // pivot table
        'student_id',
        'class_room_id'
    )->withPivot('assigned_date')->withTimestamps();
}
    public function lead()
    {
        return $this->belongsTo(StudentLead::class, 'student_lead_id');
    }

    public function fees()
    {
        return $this->hasMany(Fee::class);
    }

    public function attendances()
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function notes()
    {
        return $this->hasMany(ClassNote::class);
    }

    public function getDobFormattedAttribute()
    {
        return $this->dob?->format('d M Y');
    }

    protected static function booted()
    {
        static::creating(function ($student) {

            $lastStudent = Student::latest('id')->first();

            $nextNumber = $lastStudent ? $lastStudent->id + 1 : 1;

            $year = now()->year;

            $student->admission_no = 'FOMS-'.$year.'-' . str_pad($nextNumber,4,'0',STR_PAD_LEFT);

        });
    }
}
