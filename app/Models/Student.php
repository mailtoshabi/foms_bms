<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;

class Student extends Authenticatable
{
    use SoftDeletes;

    protected $table = 'students';

    protected $fillable = [
        'admission_no',
        'country_id',
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
        'admission_fee_discount',
        'is_monthly_fee_exempted',
        'monthly_fee_discount',

        'status',
        'is_whatsapp_different',
        'wallet_balance',
        'is_wallet_autopay_enabled',
        'is_blocked'
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
        'wallet_balance' => 'decimal:2',
        'is_wallet_autopay_enabled' => 'boolean',
        'is_blocked' => 'boolean',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function relatedStudents()
    {
        return $this->belongsToMany(
            Student::class,
            'student_relations',
            'student_id',
            'related_student_id'
        );
    }

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
        return $this->hasMany(Fee::class)->latest();
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    public function attendances()
    {
        return $this->hasMany(StudentAttendance::class);
    }

    public function homeworkSubmissions()
    {
        return $this->hasMany(HomeworkSubmission::class);
    }

    public function notes()
    {
        return $this->hasMany(ClassNote::class);
    }

    public function getDobFormattedAttribute()
    {
        return $this->dob?->format('d M Y');
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

    public function getFormattedPhoneAttribute()
    {
        $code = $this->country?->code ?? '';
        return $code ? $code . ' ' . $this->phone : $this->phone;
    }

    public function setContactNumberAttribute($value)
    {
        $this->attributes['contact_number'] = preg_replace('/[^0-9]/', '', $value);
    }

    public function setWhatsappNumberAttribute($value)
    {
        $this->attributes['whatsapp_number'] = preg_replace('/[^0-9]/', '', $value);
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = preg_replace('/[^0-9]/', '', $value);
    }

    public function classHourJoins()
    {
        return $this->hasMany(ClassHourStudentJoin::class, 'student_id');
    }

    public function buzzers()
    {
        return $this->hasMany(ClassHourBuzzer::class, 'student_id');
    }
}
