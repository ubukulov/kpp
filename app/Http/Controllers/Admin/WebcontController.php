<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContainerLog;
use App\Models\ContainerStock;
use Illuminate\Http\Request;

class WebcontController extends Controller
{
    public function stocks()
    {
        $stocks = ContainerStock::selectRaw('containers.number,container_address.name,container_address.title,container_stocks.*')
                    ->join('containers', 'containers.id', '=', 'container_stocks.container_id')
                    ->join('container_address', 'container_address.id', '=', 'container_stocks.container_address_id')
                    ->get();

        return view('admin.webcont.stocks', compact('stocks'));
    }

    public function logs()
    {
        return view('admin.webcont.logs');
    }

    public function getLogsForAdmin()
    {
        $logs = ContainerLog::orderBy('id', 'DESC')
            ->selectRaw('container_logs.*, users.full_name, techniques.name as technique')
            ->join('users', 'users.id', '=', 'container_logs.user_id')
            ->join('techniques', 'techniques.id', '=', 'container_logs.technique_id')
            ->paginate(100);
        return response()->json($logs);
    }

    public function search(Request $request)
    {
        $number = $request->input('number');
        $date1 = $request->input('date1');
        $date2 = $request->input('date2');
        if (is_null($number)) {
            $container_logs = ContainerLog::whereRaw("(container_logs.created_at >= ? AND container_logs.created_at <= ?)", [$date1." 00:00", $date2." 23:59"])
                ->selectRaw('container_logs.*, users.full_name, techniques.name as technique')
                ->join('users', 'users.id', '=', 'container_logs.user_id')
                ->join('techniques', 'techniques.id', '=', 'container_logs.technique_id')
                ->get();
        } else {
            $container_logs = ContainerLog::where("container_logs.container_number", "LIKE", "%".$number)
                ->selectRaw('container_logs.*, users.full_name, techniques.name as technique')
                ->whereRaw("(container_logs.created_at >= ? AND container_logs.created_at <= ?)", [$date1." 00:00", $date2." 23:59"])
                ->leftJoin('users', 'users.id', '=', 'container_logs.user_id')
                ->leftJoin('techniques', 'techniques.id', '=', 'container_logs.technique_id')
                ->get();
        }

        return response()->json($container_logs);
    }
}
