<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BT extends Model
{
    protected $table = 'bt';

    protected $fillable = [
        'title', 'created_at', 'updated_at'
    ];
}
