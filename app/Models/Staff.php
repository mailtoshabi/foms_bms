<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Staff extends Authenticatable
{
    use SoftDeletes;
    protected $table = 'staffs';
    protected $fillable = [
        'name', 'email', 'phone', 'password', 'address', 'gpay_number', 'salary_amount', 'id_proof', 'photo', 'last_login_at', 'last_login_ip'
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class,'role_staff');
    }

    public function hasRole($roles)
    {
        $roles = explode('|',$roles);
        return $this->roles()->whereIn('name',$roles)->exists();
    }

    public function hasRoleId($roleId)
    {
        return $this->roles->contains('id', $roleId);
    }

    public function salaries()
    {
        return $this->hasMany(\App\Models\StaffSalary::class, 'staff_id');
    }

    public function setPhoneAttribute($value)
    {
        $this->attributes['phone'] = preg_replace('/[^0-9]/', '', $value);
    }

    public function setGpayNumberAttribute($value)
    {
        $this->attributes['gpay_number'] = preg_replace('/[^0-9]/', '', $value);
    }
}

