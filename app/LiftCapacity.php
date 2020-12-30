<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LiftCapacity extends Model
{
    protected $table = 'lift_capacity';

    protected $fillable = [
        'title', 'parking_cost', 'created_at', 'updated_at'
    ];
}
