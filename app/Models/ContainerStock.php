<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContainerStock extends Model
{
    protected $table = 'container_stocks';

    protected $fillable = [
        'container_id', 'container_address_id', 'status', 'state'
    ];

    public function container_address()
    {
        return $this->belongsTo(ContainerAddress::class);
    }

    public function container()
    {
        return $this->belongsTo(Container::class);
    }

    public static function exists($container_id)
    {
        $result = ContainerStock::where(['container_id' => $container_id])->first();
        return ($result) ? true : false;
    }

    public static function checking_container_by_address($container_address_id)
    {
        $result = ContainerStock::where(['container_address_id' => $container_address_id])->first();
        return ($result) ? $result : false;
    }

    public static function is_shipping($container_id)
    {
        $result = ContainerStock::where(['container_id' => $container_id, 'status' => 'received'])->where('container_address_id', '>', 2)->first();
        return ($result) ? true : false;
    }
}
