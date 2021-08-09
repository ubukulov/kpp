<?php

namespace App\Http\Controllers;

use App\Models\ContainerLog;
use App\Models\ContainerStock;
use App\Models\ContainerTask;
use App\Models\ImportLog;
use Illuminate\Http\Request;
use Auth;

class KTController extends Controller
{
    public function operator()
    {
        $container_tasks = ContainerTask::where(['user_id' => Auth::id()])->orderBy('id', 'DESC')->get();
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
        $container_ids = [];
        foreach(json_decode($container_task->container_ids) as $container_id=>$container_number) {
            $container_ids[] = $container_id;
        }
        $container_stocks = ContainerStock::whereIn('container_id', $container_ids)->get();
        return view('kt.task_container_logs', compact('container_stocks', 'container_task'));
    }

    public function completeTask($container_task_id)
    {
        $container_task = ContainerTask::findOrFail($container_task_id);
        $container_ids = [];
        foreach(json_decode($container_task->container_ids) as $container_id=>$container_number) {
            $container_ids[] = $container_id;
        }
        $container_stocks = ContainerStock::whereIn('container_id', $container_ids)->get();
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
                    'operation_type' => 'completed', 'address_from' => $current_address_name, 'address_to' => 'Удален из стока', 'state' => $container_stock->state
                ]);

                // Удаляем из стока
                $container_stock->delete();
            }
        }

        return redirect()->route('kt.kt_operator');
    }
}
