<?php

namespace App\Http\Controllers;

use App\Models\ContainerLog;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\DB;

class StatController extends BaseController
{
    public function getStatsForMe()
    {
        $container_logs = ContainerLog::where(['user_id' => Auth::id()])->whereNotNull('technique_id')
                //->selectRaw('DATE_FORMAT(created_at, "%d.%m.%Y") as dt, COUNT(*) as cnt')
                ->whereIn('action_type', ['put', 'pick', 'move', 'move_another_zone'])
                //->groupBy(DB::raw('Date(created_at)'))
                ->orderBy('id', 'DESC')
                //->take(30)
                ->get();
        $arr = [];
        foreach($container_logs as $container_log) {
            $r = ($container_log->action_type == 'put')  ? 1 : 0;
            $v = ($container_log->action_type == 'pick') ? 1 : 0;

            if($container_log->action_type == 'move_another_zone' && $container_log->address_to == 'BUFFER | buffer') {
                $vp = 1;
            } else {
                $vp = 0;
            }

            if($container_log->action_type == 'move' && $container_log->address_from == 'buffer') {
                $pp = 1;
            } else {
                $pp = 0;
            }

            $sum = $r + $v + $vp + $pp;

            $date = date('Y-m-d', strtotime($container_log->created_at));
            if(array_key_exists($date, $arr)) {
                $arr[$date] += $sum;
            } else {
                $arr[$date] = $sum;
            }

        }

        $new_arr = [];

        foreach($arr as $k=>$v) {
            $new_arr[] = [
                'dt' => $k,
                'cnt' => $v
            ];
        }

        return $new_arr;
    }
}
