<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContainerTaskResource;
use App\Models\Container;
use App\Models\ContainerAddress;
use App\Models\ContainerLog;
use App\Models\ContainerSchedule;
use App\Models\ContainerStock;
use App\Models\ContainerTask;
use App\Models\ImportLog;
use App\Models\SpineCode;
use App\Models\Technique;
use App\Models\TechniqueLog;
use App\Models\TechniqueStock;
use App\Models\TechniqueTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
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

    public function printTechniqueTask($technique_task_id)
    {
        $technique_task = TechniqueTask::with('company', 'agreement')->findOrFail($technique_task_id);
        $technique_stocks = TechniqueStock::where(['technique_task_id' => $technique_task->id])
            ->selectRaw('technique_stocks.*, technique_types.name as type_name, technique_places.name as place_name, companies.full_company_name')
            ->join('technique_types', 'technique_types.id', 'technique_stocks.technique_type_id')
            ->leftJoin('technique_places', 'technique_places.id', 'technique_stocks.technique_place_id')
            ->leftJoin('companies', 'companies.id', 'technique_stocks.company_id')
            ->get();
        return view('kt.print_technique_task', compact('technique_task', 'technique_stocks'));
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

    public function getContainerLogs($container_number): \Illuminate\Http\JsonResponse
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

    public function getTechniqueLogs($vincode): \Illuminate\Http\JsonResponse
    {
        $technique_logs = TechniqueLog::where('vin_code', 'like', '%'.$vincode)
            ->selectRaw('technique_logs.*, users.full_name, users.phone')
            ->join('users', 'users.id', 'technique_logs.user_id')
            ->orderBy('technique_logs.id', 'DESC')
            ->get();
        if($technique_logs) {
            return response()->json($technique_logs);
        }

        return response()->json('Не найден авто по винкоду: ' . $vincode, 404);
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
