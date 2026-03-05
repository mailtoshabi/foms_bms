<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherLead extends Model
{
    protected $fillable=[
        'name','contact_number','email','source','status'
    ];
}


