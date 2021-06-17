<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Permit;
use App\Models\PermitStatus;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function getDriverInfoByPhone($phone)
    {
		$phone = "8".$phone;
        $driver = Driver::wherePhone($phone)->first();
        return response()->json($driver);
    }

    public function getCompaniesInfo()
    {
        $companies = Company::all();
        return response()->json($companies);
    }

    public function changePermitStatus(Request $request)
    {
        $data = $request->all();
        $permit = Permit::findOrFail($data['permit_id']);
        if($permit) {
            PermitStatus::create($data);
            return response('Статус зафиксирован!', 200);
        } else {
            return response('Пропуск не найдено', 404);
        }
    }
}
