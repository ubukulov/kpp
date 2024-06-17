<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\ContainerLog;
use App\Models\ContainerStock;
use App\Models\ContainerTask;
use App\Models\User;
use App\Traits\WebcontReports;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class WebcontController extends Controller
{
    use WebcontReports;

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

    public function aftosWebcont()
    {
        return view('cabinet.webcont.aftos');
    }

    public function aftosWebcontSearch(Request $request)
    {
        $container_number = $request->input('container_number');
        $container = Container::where('number', 'like', '%'.$container_number)->first();
        if($container){
            $container_stock = ContainerStock::where(['container_id' => $container->id])->first();
            if ($container_stock) {
                $container_address = $container_stock->container_address;
                return response([
                    'text' => "Контейнер: <span style='color: white;'>".$container->number."<br> (Владелец: $container->company, $container->container_type футовый)</span> <br>Адрес: $container_address->name ($container_address->title)",
                ], 200);
            } else {
                return response([
                    'text' => "Контейнер №".$container->number." отсутствует в остатке. <br> (Владелец: $container->company, $container->container_type футовый)</span>",
                ], 200);
            }
        } else {
            return response([
                'text' => "Контейнер №".$container_number." не найден"
            ], 404);
        }
    }

    public function reports()
    {
        $users = User::whereIn('users.position_id', [91,92,93,153])
            ->selectRaw('users.*')
            //->join('users_roles', 'users_roles.user_id', 'users.id')
            ->orderBy('users.full_name')
            ->get();
        return view('cabinet.webcont.reports', compact('users'));
    }
}
