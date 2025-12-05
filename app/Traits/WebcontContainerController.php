<?php

namespace App\Traits;

use App\Http\Resources\ContainerTaskResource;
use App\Models\Container;
use App\Models\ContainerLog;
use App\Models\ContainerSchedule;
use App\Models\ContainerStock;
use App\Models\ContainerTask;
use App\Models\ImportLog;
use App\Models\Technique;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;

trait WebcontContainerController
{
    public function controller()
    {
        return view('kt.controller');
    }

    public function getContainerTasks(Request $request, $filter_id)
    {
        switch ($filter_id) {
            case 0:
                $container_tasks = ContainerTask::with('user')
                    ->where(['user_id' => Auth::id()])
//                    ->whereNotIn("status", ['closed', 'ignore', 'failed'])
                    ->whereIn("status", ['open', 'failed', 'waiting'])
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
                    ->paginate(20);
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
    public function confirmEditPosition(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->all();
        DB::beginTransaction();
        try {
            $container = Container::whereNumber($data['container_number'])->first();
            $container_task = ContainerTask::findOrFail($data['container_task_id']);
            $container_stock = ContainerStock::where(['container_task_id' => $container_task->id, 'container_id' => $container->id, 'status' => 'edit'])->first();
            if ($container_stock) {
                // Изменяем в справочнике
                $new_container = Container::whereNumber($data['new_container_number'])->first();
                if(!$new_container) {
                    $new_container = Container::create([
                        'number' => $data['new_container_number'], 'container_type' => $container->container_type, 'company' => $container->company,
                    ]);
                    //return response()->json($data['new_container_number'] . " нет в справочнике", 404, [], JSON_UNESCAPED_UNICODE);
                }

                // Меняем статус в стоке
                $container_stock->container_id = $new_container->id;
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

                return response()->json('Заявка на редактирование выполнено', 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json('Ранее удален либо не найдено', 403,[], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json($exception, 500);
        }
    }

    public function getSchedule(): \Illuminate\Http\JsonResponse
    {
        $schedules = ContainerSchedule::join('users', 'users.id', 'container_schedule.crane_id')
            ->selectRaw('container_schedule.*, users.full_name as crane_name')
            ->with('user', 'technique')
            ->get();

        foreach($schedules as $schedule){
            if(is_null($schedule->ids)) continue;
            $ids = explode(',', $schedule->ids);
            $slingers = [];
            if(count($ids) == 2) {
                $result = User::whereIn('id', $ids)->get();
                foreach($result as $item) {
                    $slingers[] = [
                        'id' => $item->id,
                        'full_name' => $item->full_name,
                    ];
                }
            } else {
                $result = User::findOrFail($schedule->ids);
                $slingers[] = [
                    'id' => $result->id,
                    'full_name' => $result->full_name,
                ];

            }
            $schedule['slinger'] = $slingers;
        }

        return response()->json($schedules);
    }

    public function getCraneUsers(): \Illuminate\Http\JsonResponse
    {
        $crane_users = User::where(['roles.slug' => 'kt-crane'])
            ->selectRaw('users.id, users.full_name')
            ->join('users_roles', 'users_roles.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'users_roles.role_id')
            ->leftJoin('container_schedule', 'container_schedule.crane_id', 'users.id')
            ->whereNull('container_schedule.id')
            ->orderBy('users.full_name', 'ASC')
            ->get();
        return response()->json($crane_users);
    }

    public function getSlingerUsers(): \Illuminate\Http\JsonResponse
    {
        $slinger_users = User::whereIn('users.position_id', [91,92,93,153,174])
            ->whereNotIn('users.id', ContainerSchedule::ids())
            ->selectRaw('users.id, users.full_name')
            ->selectRaw('(SELECT users_histories.status FROM users_histories WHERE users_histories.user_id=users.id ORDER BY users_histories.id DESC LIMIT 1) as status')
            ->orderBy('users.full_name', 'ASC')
            ->havingRaw('status = "works"')
            ->get();

        return response()->json($slinger_users);
    }

    public function getTechniques(): \Illuminate\Http\JsonResponse
    {
        $techniques = Technique::leftJoin('container_schedule', 'container_schedule.technique_id', 'techniques.id')
            ->selectRaw('techniques.id, techniques.name')
            ->whereNull('container_schedule.id')
            ->get();
        return response()->json($techniques);
    }
}
