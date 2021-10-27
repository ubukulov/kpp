<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    public function loginForm()
    {
    	return view('login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
    	$remember = $request->input('remember');

        $messages = [
            'email.required' => 'Поле Email обязательно',
            'password.required' => 'Поле пароль обязательно'
        ];

    	$validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        } else {
            if(Auth::attempt($credentials, $remember)) {
                if (Auth::user()->hasRole('kpp-operator')) {
                    return response(route('security.kpp'));
                } elseif (Auth::user()->hasRole('personal-control')){
                    return response(route('personal.control'));
                } elseif(Auth::user()->hasRole('otdel-kadrov')) {
                    return response(route('cabinet.employees.index'));
                } elseif(Auth::user()->hasRole('kt-operator')) {
                    return response(route('kt.kt_operator'));
                } elseif(Auth::user()->hasRole('kt-crane')) {
                    return response(route('kt.kt_crane'));
                } elseif(Auth::user()->hasRole('kt-controller')) {
                    return response(route('kt.controller'));
                } else {
                    return response(route('cabinet.report.index'));
                }
            } else {
                return response('По введенному Email или пароль не найден пользователь', 404);
            }
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
