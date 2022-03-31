<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContainerStock extends Model
{
    protected $table = 'container_stocks';

    protected $fillable = [
        'container_task_id', 'container_id', 'container_address_id', 'status', 'state', 'company', 'customs',
        'car_number_carriage', 'seal_number_document', 'seal_number_fact', 'note',
        'datetime_submission', 'datetime_arrival', 'contractor'
    ];

    public function container_address()
    {
        return $this->belongsTo(ContainerAddress::class);
    }

    public function container_task()
    {
        return $this->belongsTo(ContainerTask::class);
    }

    public function container()
    {
        return $this->belongsTo(Container::class);
    }

    public static function exists($container_id)
    {
        $container_stock = ContainerStock::where(['container_id' => $container_id])->first();
        if(isset($container_stock) && $container_stock->container_address_id == 1385) {
            return false;
        }

        return ($container_stock) ? true : false;
    }

    public static function checking_container_by_address($container_address_id)
    {
        $result = ContainerStock::where(['container_address_id' => $container_address_id])->first();
        return ($result) ? $result : false;
    }

    public static function is_shipping($container_id)
    {
        $result = ContainerStock::where(['container_id' => $container_id, 'status' => 'received'])->first();
        return ($result) ? true : false;
    }
}
