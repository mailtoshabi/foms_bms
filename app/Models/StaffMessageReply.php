<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StaffMessageReply extends Model
{
    protected $fillable = [
        'staff_message_id',
        'sender_id',
        'sender_type',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function message()
    {
        return $this->belongsTo(StaffMessage::class, 'staff_message_id');
    }

    public function sender()
    {
        if ($this->sender_type === 'admin') {
            return $this->belongsTo(Admin::class,'sender_id');
        }
        return $this->belongsTo(Staff::class,'sender_id');
    }

    public function senderName()
    {
        if ($this->sender_type == 'admin') {
            return \App\Models\Admin::find($this->sender_id)?->name ?? 'Admin';
        }

        if ($this->sender_type == 'staff') {
            return \App\Models\Staff::find($this->sender_id)?->name ?? 'Staff';
        }

        return 'User';
    }
}

