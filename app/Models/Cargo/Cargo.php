<?php

namespace App\Models\Cargo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    use HasFactory;

    protected $table = 'cargo';

    protected $fillable = [
        'name', 'type'
    ];

    /*
     * @type примечание
     * 1 - деталь
     * 2 - полноценная техника который заводится и может передвигаться
     * */

    protected $dates = [
        'created_at', 'updated_at'
    ];
}
