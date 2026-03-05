<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StaffMessage extends Model
{
    protected $fillable = [
        'sender_type',
        'sender_id',
        'receiver_type',
        'receiver_id',
        'subject',
        'message',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function replies()
{
    return $this->hasMany(StaffMessageReply::class, 'staff_message_id');
}

    public function sender()
    {
        if ($this->sender_type === 'admin') {
            return $this->belongsTo(Admin::class,'sender_id');
        }
        return $this->belongsTo(Staff::class,'sender_id');
    }

    public function receiver()
    {
        if ($this->receiver_type === 'admin') {
            return $this->belongsTo(Admin::class,'receiver_id');
        }
        return $this->belongsTo(Staff::class,'receiver_id');
    }
}

