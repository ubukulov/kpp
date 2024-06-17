<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoWork extends Model
{
    use HasFactory;

    protected $table = 'cargo_work_types';

    protected $fillable = [
        'name'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];
}
