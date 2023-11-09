<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechniquePlace extends Model
{
    use HasFactory;

    protected $table = 'technique_places';

    protected $fillable = [
        'name', 'created_at', 'updated_at'
    ];
}
