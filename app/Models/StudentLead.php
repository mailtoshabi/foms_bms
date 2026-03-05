<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentLead extends Model
{
    protected $fillable = [
    'name',
    'contact_number',
    'email',
    'source_id',
    'status'
    ];

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class)->latest();
    }

    public function student()
{
    return $this->hasOne(Student::class);
}
}

