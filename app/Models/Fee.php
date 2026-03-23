<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fee extends Model
{
    protected $fillable=[
        'student_id','class_room_id','type','amount','due_date','status'
    ];
}



