<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TeacherLead extends Model
{
    protected $fillable = [
        'name',
        'contact_number',
        'email',
        'source',
        'status',
        'form_token',
        'form_expires_at',
        'form_opened_at',
        'form_disabled'
    ];

    protected $casts = [
        'form_expires_at' => 'datetime',
        'form_opened_at' => 'datetime',
        'form_disabled' => 'boolean',
    ];

    public function notes()
    {
        return $this->hasMany(TeacherLeadNote::class)->latest();
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function generateFormToken()
    {
        if (!$this->form_token) {
            $this->update([
                'form_token' => Str::uuid(),
                'form_expires_at' => now()->addDays(7)
            ]);
        }

        return $this->form_token;
    }

    protected static function booted()
    {
        static::creating(function ($lead) {
            $lead->form_token = Str::uuid();
            $lead->form_expires_at = now()->addDays(7);
        });
    }
}
