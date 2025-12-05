<?php

namespace App\Models\Wagon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wagon extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 'length'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];
}
