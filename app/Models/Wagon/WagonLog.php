<?php

namespace App\Models\Wagon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WagonLog extends Model
{
    use HasFactory;

    protected $table = 'wagon_logs';

    protected $fillable = [
        'user_id',
        'wagon_id',
        'container_id',
        'wagon_track_id',
        'wagon_task_id',
        'container_number',
        'user_ids',
        'address_from',
        'address_to',
        'reception_datetime',
        'wagon_number',
        'wagon_task_number',
        'ordinal_number',
        'country_code',
        'owner_name',
        'cargo_name',
        'state',
        'shipper_name',
        'status',
        'operation_type',
        'directions',
        'destination_station',
        'consignee_name'
    ];

    protected $casts = [
        'reception_datetime' => 'datetime',
    ];
}
