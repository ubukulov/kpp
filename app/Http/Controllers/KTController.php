<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContainerTaskResource;
use App\Models\ContainerAddress;
use App\Models\ContainerLog;
use App\Models\ContainerStock;
use App\Models\ContainerTask;
use App\Models\ImportLog;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Auth;

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
        /*$container_ids = [];
        foreach(json_decode($container_task->container_ids) as $container_id=>$container_number) {
            $container_ids[] = $container_id;
        }
        $container_stocks = ContainerStock::whereIn('container_id', $container_ids)->get();*/
        $import_logs = $container_task->import_logs;

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
            $import_log['address'] = $import_log->getContainerAddress();
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

    public function getContainerTasks($filter_id)
    {
        switch ($filter_id) {
            case 0:
                $container_tasks = ContainerTask::with('user')->where(['user_id' => Auth::id(), 'status' => 'open'])->orderBy('id', 'DESC')->get();
                break;

            case 1:
                $container_tasks = ContainerTask::with('user')->where(['status' => 'open'])->orderBy('id', 'DESC')->paginate(10);
                break;

            case 2:
                $container_tasks = ContainerTask::with('user')->where(['user_id' => Auth::id(), 'status' => 'closed'])->orderBy('id', 'DESC')->get();
                break;

            case 3:
                $container_tasks = ContainerTask::with('user')->where(['user_id' => Auth::id(), 'status' => 'failed'])->orderBy('id', 'DESC')->get();
                break;

            case 4:
                $container_tasks = ContainerTask::with('user')->where(['status' => 'closed'])->orderBy('id', 'DESC')->paginate(10);
                break;

            default:
                $container_tasks = ContainerTask::with('user')->orderBy('id', 'DESC')->get();
                break;
        }

        $tasks = [];

        foreach($container_tasks as $task) {
            $task['allow'] = $task->allowCloseThisTask();
            $task['number'] = $task->getNumber();
            $task['type'] = $task->getType();
            $task['trans'] = $task->getTransType();
            $task['stat'] = "Выполнено: " . $task->getCountCompletedItems() . ' из ' .$task->getCountItems();
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
}
