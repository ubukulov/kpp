<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContainerTaskResource;
use App\Models\Container;
use App\Models\ContainerAddress;
use App\Models\ContainerLog;
use App\Models\ContainerStock;
use App\Models\ContainerTask;
use App\Models\ImportLog;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;

class KTController extends Controller
{
    public function operator()
    {
        $container_tasks = ContainerTask::orderBy('id', 'DESC')->get();
        return view('kt.kt_operator', compact('container_tasks'));
    }

    public function createTask()
    {
        return view('kt.create_task');
    }

    public function crane()
    {
        return view('kt.kt_crane');
    }

    public function showTaskImportLogs($container_task_id)
    {
        $container_task_id = (int) $container_task_id;
        $import_logs = ImportLog::where(['container_task_id' => $container_task_id])->get();
        return view('kt.task_details', compact('import_logs'));
    }

    public function editTask($container_task_id)
    {
        $container_task = ContainerTask::findOrFail($container_task_id);
        return view('kt.edit_task', compact('container_task'));
    }

    public function showTaskContainerLogs($container_task_id)
    {
        $container_task_id = (int) $container_task_id;
        $container_task = ContainerTask::findOrFail($container_task_id);
        $import_logs = $container_task->import_logs;
        $container_stocks = $container_task->container_stocks();

        $containers_arr = [];
        foreach($container_stocks as $container_stock) {
            $containers_arr[$container_stock->container->number] = $container_stock->toArray();
        }

        foreach($import_logs as $import_log) {
            $import_log['position'] = $import_log->isPositionCancelOrEdit();

            if(array_key_exists($import_log->container_number, $containers_arr)) {
                $import_log['company']      = $containers_arr[$import_log->container_number]['company'];
                $import_log['contractor']   = $containers_arr[$import_log->container_number]['contractor'];
            } else {
                $import_log['company']      = null;
                $import_log['contractor']   = null;
            }
        }

        return view('kt.task_container_logs', compact('import_logs', 'container_task'));
    }

    public function showTaskContainerLogs2($container_task_id)
    {
        $container_task_id = (int) $container_task_id;
        $container_task = ContainerTask::findOrFail($container_task_id);
        $container_task['number'] = $container_task->getNumber();
        $container_task['type'] = $container_task->getType();
        $container_task['trans'] = $container_task->getTransType();
        return view('kt.controller_logs', compact( 'container_task'));
    }

    public function getContainerTaskLogs($container_task_id)
    {
        $container_task_id = (int) $container_task_id;
        $container_task = ContainerTask::findOrFail($container_task_id);
        $import_logs = $container_task->import_logs;
        foreach($import_logs as $import_log) {
            $import_log['address'] = $import_log->getContainerAddress($container_task_id);
            $import_log['position'] = $import_log->isPositionCancelOrEdit();
        }

        return response()->json($import_logs);
    }

    /**
     * Метод предназначен для закрытия заявку.
     * @param integer $container_task_id
     * @return RedirectResponse
     */
    public function completeTask($container_task_id)
    {
        ContainerTask::complete($container_task_id);
        return redirect()->route('kt.kt_operator');
    }

    public function getContainerTasks(Request $request, $filter_id)
    {
        switch ($filter_id) {
            case 0:
                $container_tasks = ContainerTask::with('user')
                    ->where(['user_id' => Auth::id()])
                    ->whereRaw("status != 'closed'")
//                    ->orderByRaw("CASE status
//                                            WHEN 'open' THEN 1
//                                            WHEN 'waiting' THEN 1
//                                            WHEN 'failed' THEN 2
//                                        END")
                    ->orderBy('id', 'DESC')->get();
                break;

            case 1:
                $container_tasks = ContainerTask::with('user')
                    ->where(['status' => 'open'])
                    ->orderBy('id', 'DESC');

                if($request->has('page')) {
                    $container_tasks = $container_tasks->paginate(50, ['*'], 'page', $request->get('page'));
                } else {
                    $container_tasks = $container_tasks->paginate(50);
                }
                break;

            case 2:
                $container_tasks = ContainerTask::with('user')
                    ->where(['user_id' => Auth::id(), 'status' => 'closed'])
                    ->orderBy('id', 'DESC')
                    ->paginate(50);
                break;

            case 3:
                $container_tasks = ContainerTask::with('user')
                    ->where(['user_id' => Auth::id(), 'status' => 'failed'])
                    ->orderBy('id', 'DESC')
                    ->paginate(50);
                break;

            case 4:
                $container_tasks = ContainerTask::with('user')
                    ->where(['status' => 'closed'])
                    ->orderBy('id', 'DESC');

                if($request->has('page')) {
                    $container_tasks = $container_tasks->paginate(50, ['*'], 'page', $request->get('page'));
                } else {
                    $container_tasks = $container_tasks->paginate(50);
                }
                break;

            default:
                $container_tasks = ContainerTask::with('user')
                    ->orderBy('id', 'DESC')
                    ->paginate(50);
                break;
        }

        $tasks = [];

        foreach($container_tasks as $task) {
            $task['allow'] = $task->allowCloseThisTask();
            $task['number'] = $task->getNumber();
            $task['type'] = $task->getType();
            $task['trans'] = $task->getTransType();
            $task['stat'] = $task->getCountCompletedItems() . ' из ' .$task->getCountItems();
            $task['hasAnyPositionCancelOrEdit'] = $task->checkingForCancelOrEditAnyPosition();
            $tasks[] = $task;
        }

        return ContainerTaskResource::collection($container_tasks);
    }

    public function printTask($container_task_id)
    {
        $container_task = ContainerTask::findOrFail($container_task_id);
        $container_task->print_count++;
        $container_task->save();
        $container_stocks = $container_task->container_stocks();
        return view('kt.print_task', compact('container_task', 'container_stocks'));
    }

    public function controller()
    {
        return view('kt.controller');
    }

    // Запрос на удаление позиции из заявки
    public function taskPositionCancel(Request $request)
    {
        $data = $request->all();
        $container = Container::whereNumber($data['container_number'])->first();
        if ($container) {
            $container_task = ContainerTask::findOrFail($data['container_task_id']);
            if ($container_task->task_type == 'receive') {
                $container_stock = ContainerStock::where(['container_task_id' => $container_task->id, 'container_id' => $container->id, 'status' => 'incoming'])->first();
            } else {
                $container_stock = ContainerStock::where(['container_task_id' => $container_task->id, 'container_id' => $container->id, 'status' => 'in_order'])->first();
            }

            if ($container_stock) {
                $container_stock->status = 'cancel';
                $container_stock->note = $data['reason'];
                $container_stock->save();

                return response()->json('Заявка принято!');
            } else {
                return response()->json('Вы уже подали ранее! Заявка в обработке');
            }
        }
    }

    // Отменяет запрос на удаление позиции из заявки
    public function rejectCancelPosition(Request $request)
    {
        $data = $request->all();
        $container = Container::whereNumber($data['container_number'])->first();
        $container_task = ContainerTask::findOrFail($data['container_task_id']);

        $container_stock = ContainerStock::where(['container_task_id' => $container_task->id, 'container_id' => $container->id, 'status' => 'cancel'])->first();
        if ($container_stock) {
            if ($container_task->task_type == 'receive') {
                $container_stock->status = 'incoming';
                $container_stock->note = null;
                $container_stock->save();
            } else {
                $container_stock->status = 'in_order';
                $container_stock->note = null;
                $container_stock->save();
            }
            return response()->json('Заявка на удаление отклонен');
        }
    }

    // Удаляет конкретную позицию из заявки по подтверждение куратора
    public function confirmCancelPosition(Request $request)
    {
        $data = $request->all();
        DB::beginTransaction();
        try {
            $container = Container::whereNumber($data['container_number'])->first();
            $container_task = ContainerTask::findOrFail($data['container_task_id']);
            $container_stock = ContainerStock::where(['container_task_id' => $container_task->id, 'container_id' => $container->id, 'status' => 'cancel'])->first();
            if ($container_stock) {
                if($container_task->task_type == 'receive') {
                    $cs = $container_stock->attributesToArray();
                    $cs['user_id'] = Auth::id();
                    $cs['container_number'] = $container->number;
                    $cs['operation_type'] = 'canceled';
                    $cs['address_from'] = $container_stock->container_address->name;
                    $cs['address_to'] = 'Удалено по заявке';
                    $cs['action_type'] = 'canceled';
                    // Зафиксируем в лог
                    ContainerLog::create($cs);
                    ImportLog::destroy($data['import_log_id']);

                    $container_stock->delete();
                } else {
                    $container_stock->status = 'received';
                    $container_stock->container_task_id = null;
                    $container_stock->note = null;
                    $container_stock->save();
                    $cs = $container_stock->attributesToArray();
                    $cs['user_id'] = Auth::id();
                    $cs['container_task_id'] = 0;
                    $cs['container_number'] = $container->number;
                    $cs['operation_type'] = 'canceled';
                    $cs['address_from'] = $container_stock->container_address->name;
                    $cs['address_to'] = $container_stock->container_address->name;
                    $cs['action_type'] = 'canceled';
                    // Зафиксируем в лог
                    ContainerLog::create($cs);
                    ImportLog::destroy($data['import_log_id']);
                }

                if ($container_task->allowCloseThisTask() && $container_task->trans_type == 'auto') {
                    $container_task->status = 'closed';
                    $container_task->save();
                }

                DB::commit();

                return response()->json('Заявка на удаление выполнено');
            } else {
                return response()->json('Ранее удален либо не найдено', 403);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json($exception, 500);
        }
    }

    // Запрос на редактирование (номер контейнера)
    public function taskPositionEdit(Request $request)
    {
        $data = $request->all();
        $container = Container::whereNumber($data['edit_container_number'])->first();
        if ($container) {
            $container_task = ContainerTask::findOrFail($data['edit_container_task_id']);
            $container_stock = ContainerStock::where(['container_task_id' => $container_task->id, 'container_id' => $container->id, 'status' => 'incoming'])->first();
            if ($container_stock) {
                $container_stock->status = 'edit';
                $container_stock->save();

                $cs = $container_stock->attributesToArray();
                $cs['user_id'] = Auth::id();
                $cs['container_number'] = $container->number;
                $cs['operation_type'] = 'edit';
                $cs['address_from'] = $container->number;
                $cs['address_to'] = $data['new_container_number'];
                $cs['action_type'] = 'edit';
                // Зафиксируем в лог
                ContainerLog::create($cs);

                return response()->json('Заявка принято!');
            } else {
                return response()->json('Вы уже подали ранее! Заявка в обработке');
            }
        } else {
            return response()->json('Не найден контейнер', 403);
        }
    }

    // Отменяет изменение номер контейнера
    public function rejectEditPosition(Request $request)
    {
        $data = $request->all();
        $container = Container::whereNumber($data['container_number'])->first();
        $container_task = ContainerTask::findOrFail($data['container_task_id']);

        $container_stock = ContainerStock::where(['container_task_id' => $container_task->id, 'container_id' => $container->id, 'status' => 'edit'])->first();
        if ($container_stock) {
            $container_stock->status = 'incoming';
            $container_stock->save();
            return response()->json('Заявка на редактирование отклонен');
        }
    }

    // Изменяет номер контейнера по подтверждение куратора
    public function confirmEditPosition(Request $request)
    {
        $data = $request->all();
        DB::beginTransaction();
        try {
            $container = Container::whereNumber($data['container_number'])->first();
            $container_task = ContainerTask::findOrFail($data['container_task_id']);
            $container_stock = ContainerStock::where(['container_task_id' => $container_task->id, 'container_id' => $container->id, 'status' => 'edit'])->first();
            if ($container_stock) {
                // Изменяем в справочнике
                $container->number = $data['new_container_number'];
                $container->save();

                // Меняем статус в стоке
                $container_stock->status = 'incoming';
                $container_stock->save();

                // Изменяем в import_log
                $import_log = ImportLog::findOrFail($data['import_log_id']);
                $import_log->container_number = $data['new_container_number'];
                $import_log->save();

                $cs = $container_stock->attributesToArray();
                $cs['user_id'] = Auth::id();
                $cs['container_number'] = $container->number;
                $cs['operation_type'] = 'edit_completed';
                $cs['address_from'] = $data['container_number'];
                $cs['address_to'] = $data['new_container_number'];
                $cs['action_type'] = 'edit';
                // Зафиксируем в лог
                ContainerLog::create($cs);
                DB::commit();

                return response()->json('Заявка на редактирование выполнено');
            } else {
                return response()->json('Ранее удален либо не найдено', 403);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json($exception, 500);
        }
    }

    public function getContainerLogs($container_number)
    {
        $container = Container::where('number', 'like', '%'.$container_number)->first();
        if ($container) {
            $container_logs = $container->logs;
            foreach($container_logs as $container_log){
                $container_log['user'] = $container_log->user;
            }
            return response()->json($container_logs);
        } else {
            return response()->json('Не найден контейнер', 404);
        }
    }
}
