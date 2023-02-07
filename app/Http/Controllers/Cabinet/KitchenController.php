<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\BaseController;
use App\Models\AshanaLog;
use App\Traits\KitchenTraits;
use Illuminate\Http\Request;
use Auth;

class KitchenController extends BaseController
{

    public function ashana()
    {
        $user = Auth::user();
        $logs = $user->ashana;
        return view('cabinet.ashana.index', compact('logs'));
    }

    public function talon()
    {
        $user = Auth::user();
        return view('cabinet.ashana.talon', compact('user'));
    }

    public function reports()
    {
        return view('cabinet.ashana.reports');
    }

    public function getLogs(Request $request)
    {
        $company_id = $request->input('company_id');
        $cashier_id = $request->input('cashier_id') ?? 0;
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $krt_id = $request->input('krt_id');

        if($krt_id == 1) {
            $logs = AshanaLog::whereDate('ashana_logs.date', '>=', $from_date)->whereDate('ashana_logs.date', '<=', $to_date)
                ->selectRaw('users.full_name,companies.short_ru_name as company_name,positions.title as p_name, SUM(IF(ashana_logs.cashier_id = 1097, 1, 0)) as abk,SUM(IF(ashana_logs.cashier_id = 1099, 1, 0)) as kpp3')
                ->join('users', 'users.id', 'ashana_logs.user_id')
                ->join('companies', 'companies.id', 'ashana_logs.company_id')
                ->leftJoin('positions', 'positions.id', 'users.position_id')
                ->groupBy('ashana_logs.user_id');

            if($company_id != 0) {
                $logs = $logs->where('ashana_logs.company_id', $company_id);
            }

            if($cashier_id != 0) {
                $logs = $logs->where('ashana_logs.cashier_id', '=', $cashier_id);
            }

            return response($logs->get());
        }

        if($from_date == $to_date) {
            $logs = AshanaLog::whereDate('ashana_logs.date', '>=', $from_date)->whereDate('ashana_logs.date', '<=', $to_date)
                ->selectRaw('users.id, users.full_name, companies.short_ru_name as company_name, positions.title as p_name, ashana_logs.din_type, COUNT(ashana_logs.id) as cnt')
                ->join('users', 'users.id', 'ashana_logs.user_id')
                ->join('companies', 'companies.id', 'ashana_logs.company_id')
                ->leftJoin('positions', 'positions.id', 'users.position_id');
        } else {
            $logs = AshanaLog::whereDate('ashana_logs.date', '>=', $from_date)->whereDate('ashana_logs.date', '<=', $to_date)/*whereBetween('ashana_logs.date', [$from_date, $to_date])*/
                ->selectRaw('users.id, users.full_name, companies.short_ru_name as company_name, positions.title as p_name, ashana_logs.din_type, COUNT(ashana_logs.id) as cnt')
                ->join('users', 'users.id', 'ashana_logs.user_id')
                ->join('companies', 'companies.id', 'ashana_logs.company_id')
                ->leftJoin('positions', 'positions.id', 'users.position_id');
        }

        if($cashier_id != 0) {
            $logs = $logs->where('ashana_logs.cashier_id', '=', $cashier_id);
        }

        if($company_id == 0) {
            $logs = $logs->groupBy('ashana_logs.user_id')->get();
            /*foreach($logs as $log) {
                $user = User::findOrFail($log->id);
                $work_status = $user->getWorkingStatus();
                //if($work_status->status == 'works') {
                    $arr[] = $log;
                //}
            }*/

            return response($logs);
        } else {
            $logs = $logs->where('ashana_logs.company_id', $company_id)->groupBy('ashana_logs.user_id')->get();
            /*foreach($logs as $log) {
                $user = User::findOrFail($log->id);
                $work_status = $user->getWorkingStatus();
                //if($work_status->status == 'works') {
                    $arr[] = $log;
                //}
            }*/

            return response($logs);
        }
    }
}
