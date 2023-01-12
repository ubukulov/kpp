<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Permit;
use App\Models\PermitStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    public function authentication(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();
        if(!$user || Hash::check($request->input('password'), $user->password)){
            return response('Email or password not matched', 404);
        }
        return response($user, 200);
    }
}
