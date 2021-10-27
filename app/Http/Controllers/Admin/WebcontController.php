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
        $logs = ContainerLog::orderBy('id', 'DESC')
                    ->selectRaw('container_logs.*, users.full_name, techniques.name as technique')
                    ->join('users', 'users.id', '=', 'container_logs.user_id')
                    ->join('techniques', 'techniques.id', '=', 'container_logs.technique_id')
                    ->paginate(50);
        return view('admin.webcont.logs', compact('logs'));
    }
}
