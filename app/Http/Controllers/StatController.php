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
        return ContainerLog::where(['user_id' => Auth::id()])
                ->selectRaw('DATE_FORMAT(created_at, "%d.%m.%Y") as dt, COUNT(*) as cnt')
                ->groupBy(DB::raw('Date(created_at)'))
                ->orderBy('id', 'DESC')
                ->take(15)
                ->get();
    }
}
