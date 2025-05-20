<?php

namespace App\Models\Cargo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoService extends Model
{
    use HasFactory;

    protected $table = 'cargo_services';

    protected $fillable = [
        'name'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];
}
