<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable=['name','is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function staffs()
    {
        return $this->belongsToMany(Staff::class,'role_staff');
    }
}

