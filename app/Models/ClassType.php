<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassType extends Model
{
    public function classes()
    {
        return $this->hasMany(ClassRoom::class,'class_type_id');
    }
}
