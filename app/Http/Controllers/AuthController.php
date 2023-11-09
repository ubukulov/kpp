<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AuthController
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
            return back()->withErrors($validator->errors())->withInput();
        } else {
            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Не найдено пользователь с такими данными'],
                ]);
            }

            //dd($user->tokens);

            $token = $user->createToken('API TOKEN')->plainTextToken;
            session()->put('token', $token);

            Auth::login($user);

            //if(Auth::attempt($credentials, $remember)) {

                if ($user->hasRole('kpp-operator')) {
                    return redirect()->route('security.kpp');
                } elseif ($user->hasRole('personal-control')){
                    return redirect()->route('personal.control');
                } elseif($user->hasRole('otdel-kadrov')) {
                    return redirect()->route('cabinet.employees.index');
                } elseif($user->hasRole('kt-operator')) {
                    return redirect()->route('kt.kt_operator');
                } elseif($user->hasRole('kt-crane')) {
                    return redirect()->route('kt.kt_crane');
                } elseif($user->hasRole('kt-controller')) {
                    return redirect()->route('kt.controller');
                } elseif($user->hasRole('ashana')) {
                    return redirect()->route('kitchen.index');
                } elseif($user->hasRole('mark-manager')) {
                    return redirect()->route('mark.index');
                } elseif($user->hasRole('mark-dispatcher')) {
                    return redirect()->route('mark.manager');
                } elseif($user->hasRole('kpp-security')) {
                    return redirect()->route('white.car.lists');
                } else {
                    return redirect()->route('cabinet');
                }
            /*} else {
                return back()->with('message', 'Не найдено пользователь с такими данными');
            }*/
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
