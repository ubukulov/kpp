<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContainerLog extends Model
{
    protected $table = 'container_logs';

    protected $fillable = [
        'user_id', 'container_task_id', 'container_id', 'container_number', 'operation_type', 'technique_id', 'address_from', 'address_to', 'state',
        'start_date', 'transaction_date', 'action_type', 'company', 'customs', 'car_number_carriage', 'seal_number_document',
        'seal_number_fact', 'note', 'datetime_submission', 'datetime_arrival', 'contractor'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function container()
    {
        return $this->belongsTo(Container::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
