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

    public function getContainerAddress($container_task_id)
    {
        $import_log = ImportLog::where(['container_task_id' => $container_task_id,'container_number' => $this->container_number])->first();
        if ($import_log) {
            if ($import_log->state == 'issued') {
                return '';
            } else {
                $container = Container::whereNumber($this->container_number)->first();
                if ($container) {
                    $container_stock = ContainerStock::where(['container_task_id' => $container_task_id, 'container_id' => $container->id])->first();
                    if ($container_stock) {
                        return $container_stock->container_address->name;
                    } else {
                        return '';
                    }
                } else {
                    return '';
                }
            }
        }

        return '';
    }

    public function getZone()
    {
        $container = Container::whereNumber($this->container_number)->first();
        if($container) {
            $container_stock = ContainerStock::where(['container_task_id' => $this->container_task_id, 'container_id' => $container->id])->first();
            if($container_stock) {
                $container_address = $container_stock->container_address;
                if($container_address) {
                    return $container_address->title;
                } else {
                    return '';
                }
            } else {
                return __('words.issued');
            }
        }

        return '';
    }

    public function isPositionCancelOrEdit()
    {
        $container = Container::whereNumber($this->container_number)->first();
        if ($container) {
            $container_stockCancel  = ContainerStock::where(['container_task_id' => $this->container_task_id, 'container_id' => $container->id, 'status' => 'cancel'])->first();
            $container_stockEdit    = ContainerStock::where(['container_task_id' => $this->container_task_id, 'container_id' => $container->id, 'status' => 'edit'])->first();
            $arr = [];
            if($container_stockCancel) {
                $arr['cancel'] = true;
                $arr['reason'] = $container_stockCancel->note;
            } else {
                $arr['cancel'] = false;
                $arr['reason'] = '';
            }

            if($container_stockEdit) {
                $container_log = ContainerLog::where(['container_task_id' => $this->container_task_id, 'container_id' => $container->id, 'operation_type' => 'edit'])
                                            ->orderBy('id', 'DESC')
                                            ->first();
                if ($container_log) {
                    $arr['new_container_number'] = $container_log->address_to;
                    $arr['edit'] = true;
                } else {
                    $arr['new_container_number'] = '';
                    $arr['edit'] = false;
                }
            } else {
                $arr['edit'] = false;
                $arr['new_container_number'] = '';
            }

            return $arr;
        } else {
            $arr['cancel'] = false;
            $arr['edit'] = false;
            $arr['reason'] = '';
        }
    }
}
