<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Source;

class TeacherLead extends Model
{
    protected $fillable = [
        'country_id',
        'name',
        'contact_number',
        'email',
        'source_id',
        'status',
        'form_token',
        'form_expires_at',
        'form_opened_at',
        'form_disabled',
        'whatsapp_number',
        'is_whatsapp_different'
    ];

    protected $casts = [
        'form_expires_at' => 'datetime',
        'form_opened_at' => 'datetime',
        'form_disabled' => 'boolean',
    ];

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function notes()
    {
        return $this->hasMany(TeacherLeadNote::class)->latest();
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function getFormattedContactNumberAttribute()
    {
        $code = $this->country?->code ?? '';
        return $code ? $code . ' ' . $this->contact_number : $this->contact_number;
    }

    public function getFormattedWhatsappNumberAttribute()
    {
        return $this->whatsapp_number ? '+' . $this->whatsapp_number : null;
    }

    public function regenerateFormToken()
    {
        $this->update([
            'form_token' => Str::uuid(),
            'form_expires_at' => now()->addDays(7),
            'form_disabled' => false
        ]);

        $this->refresh();

        return $this->form_token;
    }

    public function generateFormToken()
    {
        if (!$this->form_token) {
            return $this->regenerateFormToken();
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
