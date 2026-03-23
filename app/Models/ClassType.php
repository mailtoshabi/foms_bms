<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassType extends Model
{
    public function class_rooms()
    {
        return $this->hasMany(ClassRoom::class,'class_type_id');
    }
}
