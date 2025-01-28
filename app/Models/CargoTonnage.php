<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoTonnage extends Model
{
    use HasFactory;

    protected $table = 'cargo_tonnage_types';

    protected $fillable = [
        'tonnage', 'name'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];
}
