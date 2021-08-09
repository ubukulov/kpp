<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    protected $fillable = [
        'number', 'container_type', 'company'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];
}
