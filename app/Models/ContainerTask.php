<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Illuminate\Support\Facades\DB;

class ContainerTask extends Model
{
    protected $table = 'container_tasks';

    protected $fillable = [
        'user_id', 'title', 'task_type', 'trans_type', 'status', 'document_base', 'upload_file', 'container_ids', 'print_count',
        'kind', 'child_id'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function import_logs()
    {
        return $this->hasMany(ImportLog::class);
    }

    // Проверяет успешно ли импортировался файл
    public function isSuccessImport()
    {
        $import_logs = $this->import_logs;
        $count = 0;
        foreach($import_logs as $import_log) {
            if ($import_log->status == 'not') {
                $count++;
            }
        }
        return ($count==0) ? true : false;
    }

    // Метод удаляет все истории заявки об импорте
    public function deleteImportLogs()
    {
        $this->import_logs()->delete();
    }

    // Проверяет, можно ли закрывать заявку
    public function allowCloseThisTask()
    {
        $count = 0;
        foreach ($this->container_stocks() as $container_stock) {
            if ($this->task_type == 'receive') {
                if ($container_stock->status != 'received') {
                    $count++;
                }
            }

            if ($this->task_type == 'ship') {
                if ($container_stock->status != 'shipped') {
                    $count++;
                }
            }
        }
        return ($count == 0) ? true : false;
    }

    // Метод возвращает количество выполненных позиции
    public function getCountCompletedItems()
    {
        $count = 0;
        if ($this->status == 'open' && $this->trans_type == 'train') {
            $container_stocks = $this->container_stocks();
            foreach ($container_stocks as $container_stock) {
                if ($this->task_type == 'receive') {
                    if ($container_stock->status == 'received') {
                        $count++;
                    }
                }

                if ($this->task_type == 'ship') {
                    if ($container_stock->status == 'shipped') {
                        $count++;
                    }
                }
            }
        }

        if ($this->trans_type == 'auto') {
            foreach ($this->import_logs as $import_log) {
                if ($this->task_type == 'receive') {
                    if ($import_log->state == 'posted') {
                        $count++;
                    }
                }

                if ($this->task_type == 'ship') {
                    if ($import_log->state == 'issued') {
                        $count++;
                    }
                }
            }
        }

        return $count;
    }

    // Метод возвращает список контейнеров в стоке
    public function container_stocks()
    {
        $container_ids = [];
        foreach(json_decode($this->container_ids) as $container_id=>$container_number) {
            $container_ids[] = $container_id;
        }
        return ContainerStock::where(['container_task_id' => $this->id])->whereIn('container_id', $container_ids)->orderBy('container_address_id')->get();
    }

    public function getCountItems()
    {
        return count($this->container_stocks());
    }

    // Метод формирует номер заявку
    public function getNumber()
    {
        return ($this->task_type == 'receive') ? 'IN_'.$this->id : 'OUT_'.$this->id;
    }

    // Метод возвращает тип транс заявку
    public function getTransType()
    {
        return ($this->trans_type == 'auto') ? __('words.auto') : __('words.train');
    }

    // Метод возвращает тип заявку
    public function getType()
    {
        return ($this->task_type == 'receive') ? __('words.receive') : __('words.ship');
    }

    public function isOpen()
    {
        return ($this->status == 'open') ? true : false;
    }

    // Метод закрывает заявку
    public static function complete($container_task_id)
    {
        $container_task = ContainerTask::findOrFail($container_task_id);
        $container_ids = [];
        foreach(json_decode($container_task->container_ids) as $container_id=>$container_number) {
            $container_ids[] = $container_id;
        }
        $container_stocks = ContainerStock::where(['container_task_id' => $container_task_id])->whereIn('container_id', $container_ids)->get();
        if ($container_task->task_type == 'receive') {
            $errors = 0;
            foreach($container_stocks as $container_stock) {
                if ($container_stock->status == 'incoming') {
                    $errors++;
                }
            }
            if ($errors == 0) {
                $container_task->status = 'closed';
                $container_task->save();
            }
        } else {
            $errors = 0;
            foreach($container_stocks as $container_stock) {
                if ($container_stock->status == 'in_order') {
                    $errors++;
                }
            }
            if ($errors == 0) {
                $container_task->status = 'closed';
                $container_task->save();
            }
        }

        if ($container_task->task_type == 'ship' && $container_task->status == 'closed') {
            foreach($container_stocks as $container_stock) {
                $container = $container_stock->container;
                $current_address_name = $container_stock->container_address->name;
                // Зафиксируем в лог
                ContainerLog::create([
                    'user_id' => Auth::id(), 'container_id' => $container->id, 'container_number' => $container->number,
                    'operation_type' => 'completed', 'address_from' => $current_address_name, 'address_to' => 'Удален из стока',
                    'state' => $container_stock->state, 'action_type' => 'deleted'
                ]);

                // Удаляем из стока
                $container_stock->delete();
            }
        }
    }

    // Метод частично закрывает заявку по позициям
    public static function complete_part($container_task_id, $container_id)
    {
        $container_task = ContainerTask::findOrFail($container_task_id);
        if ($container_task->task_type == 'ship' && $container_task->trans_type == 'auto') {
            $container = Container::findOrFail($container_id);
            $container_stock = ContainerStock::where(['container_task_id' => $container_task_id, 'container_id' => $container_id])->first();
            if ($container_stock) {
                DB::beginTransaction();
                try {
                    $current_address_name = $container_stock->container_address->name;
                    // Зафиксируем в лог
                    ContainerLog::create([
                        'user_id' => Auth::id(), 'container_id' => $container->id, 'container_number' => $container->number,
                        'operation_type' => 'completed', 'action_type' => 'deleted', 'address_from' => $current_address_name, 'address_to' => 'Удален из стока', 'state' => $container_stock->state
                    ]);

                    $import_log = ImportLog::where(['container_task_id' => $container_task_id, 'container_number' => $container->number])->first();
                    if ($import_log) {
                        $import_log->state = 'issued';
                        $import_log->save();
                    }

                    if ($container_task->allowCloseThisTask()) {
                        $container_task->status = 'closed';
                        $container_task->save();
                    }

                    // Удаляем из стока
                    $container_stock->delete();

                    if ($container_task->child_id != 0) {
                        $container_task_auto = ContainerTask::findOrFail($container_task->child_id);
                        $container_task_auto->status = 'open';
                        $container_task_auto->save();

                        $container_address_dmu_in = ContainerAddress::whereName('damu_in')->first();
                        // добавляем в остатки
                        $container_stock = ContainerStock::create([
                            'container_task_id' => $container_task_auto->id, 'container_id' => $container_id, 'container_address_id' => $container_address_dmu_in->id,
                            'status' => 'incoming',
                        ]);

                        $cs = $container_stock->attributesToArray();
                        $cs['user_id'] = Auth::id();
                        $cs['container_number'] = $container->number;
                        $cs['operation_type'] = 'incoming';
                        $cs['address_from'] = 'automatic';
                        $cs['address_to'] = $container_address_dmu_in->name;
                        $cs['action_type'] = 'reception';
                        // Зафиксируем в лог
                        ContainerLog::create($cs);
                    }

                    DB::commit();
                } catch (\Exception $exception) {
                    abort(500, "Ошибка с сервером: $exception");
                    DB::rollBack();
                }
            } else {
                abort(404, "Контейнер с ИД - $container_id не найден в стоке");
            }
        }
    }

    // Проверяет, есть ли в заявке хоть один позиция на отмены
    public function checkingForCancelOrEditAnyPosition()
    {
        foreach($this->container_stocks() as $container_stock) {
            if($container_stock->status == 'cancel') {
                return true;
            }
            if($container_stock->status == 'edit') {
                return true;
            }
        }

        return false;
    }
}
