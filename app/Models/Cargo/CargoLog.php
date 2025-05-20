<?php

namespace App\Models\Cargo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoLog extends Model
{
    use HasFactory;

    protected $table = 'cargo_logs';

    protected $fillable = [
        'user_id', 'cargo_id', 'cargo_task_id', 'user_name', 'cargo_name', 'vin_code', 'car_number', 'quantity', 'weight', 'address_from',
        'address_to', 'action_type', 'ramp', 'technique_ids', 'user_ids', 'square', 'count_operations'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];
}
