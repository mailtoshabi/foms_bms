<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Utility extends Model
{
    protected $fillable = ['key', 'value'];

    protected $casts = [
        'is_visible' => 'boolean',
    ];
}
