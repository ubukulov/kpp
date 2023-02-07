<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bosch extends Model
{
    use HasFactory;

    protected $table = 'boschs';

    protected $fillable = [
        'article', 'description_ru', 'description_kz', 'count', 'invoice', 'cert'
    ];
}
