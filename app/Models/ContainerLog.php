<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContainerLog extends Model
{
    protected $table = 'container_logs';

    protected $fillable = [
        'user_id', 'container_id', 'container_number', 'operation_type', 'technique_id', 'address_from', 'address_to', 'state', 'transaction_date'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];
}
