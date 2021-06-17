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
            return redirect()->route('login')->withErrors($validator)->withInput();
        } else {
            if(Auth::attempt($credentials, $remember)) {
                if (Auth::user()->hasRole('kpp-operator')) {
                    return redirect()->route('security.kpp');
                } elseif (Auth::user()->hasRole('personal-control')){
                    return redirect()->route('personal.control');
                } elseif(Auth::user()->hasRole('otdel-kadrov')) {
                    return redirect()->route('cabinet.employees.index');
                } else {
                    return redirect()->route('cabinet.report.index');
                }
            } else {
                return back()->with('error', 'По введенному Email или пароль не найден пользователь');
            }
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
