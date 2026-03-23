<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StudentLead extends Model
{
    protected $fillable = [
        'name',
        'contact_number',
        'email',
        'source_id',
        'status',
        'form_token',
        'form_expires_at',
        'form_opened_at',
        'form_disabled'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'form_expires_at' => 'datetime',
        'form_opened_at' => 'datetime',
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

    public function hasStudent()
    {
        return $this->student()->exists();
    }

    public function generateFormToken()
    {
        if (!$this->form_token) {

            $this->update([
                'form_token' => Str::uuid(),
                'form_expires_at' => now()->addDays(7),
                'form_disabled' => false
            ]);

            $this->refresh();
        }

        return $this->form_token;
    }

    protected static function booted()
    {
        static::creating(function ($lead) {
            if (!$lead->form_token) {
                $lead->form_expires_at = now()->addDays(7);
                $lead->form_disabled = false;
            }
        });
    }
}
