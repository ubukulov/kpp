<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\ContainerStock;
use App\Models\ContainerTask;
use Illuminate\Http\Request;

class WebcontController extends Controller
{
    public function index()
    {
        $container_tasks = ContainerTask::where(['status' => 'open'])
                ->get();

        $needTasks = collect();
        foreach($container_tasks as $container_task) {
            foreach($container_task->container_stocks() as $container_stock) {
                if($container_stock->company == 'SAMSUNG') {
                    $needTasks->push($container_task);
                    break;
                }
            }
        }

        return view('cabinet.webcont.index', compact('needTasks'));
    }

    public function show($container_task_id)
    {
        $container_task = ContainerTask::findOrFail($container_task_id);

        $import_logs = collect();
        foreach ($container_task->import_logs as $import_log) {
            $container = Container::whereNumber($import_log->container_number)->first();
            if($container) {
                $container_stock = ContainerStock::where(['container_task_id' => $container_task_id, 'container_id' => $container->id])->first();
                if($container_stock && $container_stock->company == 'SAMSUNG') {
                    $import_logs->push($import_log);
                }
            }
        }

        return view('cabinet.webcont.show', compact('container_task', 'import_logs'));
    }
}
