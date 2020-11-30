<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BodyType extends Model
{
    protected $table = 'body_type';

    protected $fillable = [
        'title', 'created_at', 'updated_at'
    ];
}
