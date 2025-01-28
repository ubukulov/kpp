<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoLog extends Model
{
    use HasFactory;

    protected $table = 'cargo_logs';

    protected $fillable = [
        'user_id', 'cargo_id', 'cargo_item_id', 'action_type'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];
}
