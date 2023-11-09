<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\AshanaLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KitchenController extends BaseController
{
    public function index()
    {
        return view('admin.ashana.index');
    }

    public function getEmployees($company_id)
    {
        $employees = DB::select("SELECT
users.id,
users.full_name,
(SELECT users_histories.status FROM users_histories WHERE users_histories.user_id=users.id ORDER BY users_histories.id DESC LIMIT 1) as status
FROM `users`
WHERE
users.company_id=$company_id
HAVING
status='works'");

        return response()->json($employees);
    }

    public function changeAshanaLogs(Request $request)
    {
        $data = $request->all();
        AshanaLog::whereIn('user_id', explode(',', $data['employees']))
            ->whereDate('date', '>=', $data['from_date'])
            ->whereDate('date', '<=', $data['to_date'])
            ->update(['company_id' => $data['to_company_id']]);

        return response('Успешно обновлено');
    }
}
