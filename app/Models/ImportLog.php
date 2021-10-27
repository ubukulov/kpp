<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $table = 'import_logs';

    protected $fillable = [
        'container_task_id', 'user_id', 'container_number', 'comments', 'status', 'import_date', 'ip', 'state'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function container_task()
    {
        return $this->belongsTo(ContainerTask::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getContainerAddress()
    {
        $container = Container::whereNumber($this->container_number)->first();
        if ($container) {
            $container_stock = $container->container_stock;
            if($container_stock) {
                return $container_stock->container_address->name;
            } else {
                return "Не найден адрес";
            }
        }

        return 'Не найдено контейнер';
    }
}
