<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentRemovalLog extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'date',
        'removed_by',
        'auth_type',
        'reason',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function classroom()
    {
        return $this->belongsTo(ClassRoom::class, 'class_id')->withTrashed();
    }

    public function getRemoverNameAttribute()
    {
        if (!$this->removed_by) {
            return 'System';
        }
        if ($this->auth_type === 'admin') {
            $admin = \App\Models\Admin::find($this->removed_by);
            if ($admin) {
                return $admin->name . ' (Admin)';
            }
        } elseif ($this->auth_type === 'staff') {
            $staff = \App\Models\Staff::find($this->removed_by);
            if ($staff) {
                return $staff->name . ' (Staff)';
            }
        } else {
            $admin = \App\Models\Admin::find($this->removed_by);
            if ($admin) {
                return $admin->name . ' (Admin)';
            }
            $staff = \App\Models\Staff::find($this->removed_by);
            if ($staff) {
                return $staff->name . ' (Staff)';
            }
        }
        return 'ID: ' . $this->removed_by;
    }
}
