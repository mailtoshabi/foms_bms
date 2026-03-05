<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name','phone','email','photo','password',
        'last_login_at','last_login_ip'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    // 🔥 Tell Laravel login uses phone
    public function getAuthIdentifierName()
    {
        return 'phone';
    }
}
