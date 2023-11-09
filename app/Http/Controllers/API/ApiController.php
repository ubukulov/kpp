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
use Auth;

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

    /**
     * Login The User
     * @param Request $request
     * @return User
     */
    public function loginUser(Request $request)
    {
        try {
            if(!Auth::attempt($request->only(['email', 'password']))){
                return response()->json([
                    'status' => false,
                    'message' => 'Email & Password does not match with our record.',
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            return response()->json([
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
