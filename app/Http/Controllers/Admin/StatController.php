<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContainerLog;
use App\Models\ContainerStock;
use Illuminate\Http\Request;
use Carbon\Carbon;

class StatController extends Controller
{
    public static function getOperationCraneOperatorForToday()
    {
        $container_logs = ContainerLog::whereIn('action_type', ['put', 'pick', 'move', 'move_another_zone'])
            ->whereDate('created_at', Carbon::now())
            ->get();
        return $container_logs->count();
    }
}
