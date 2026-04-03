<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Staff extends Authenticatable
{
    protected $table = 'staffs';
    protected $fillable = [
        'name','email','phone','password', 'address', 'gpay_number', 'salary_amount', 'id_proof', 'photo'
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
}

