<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    public function loginForm()
    {
        /*$str = '[
  [
    {
      "vendorCode": "6000000002",
      "characteristics": [
        {
          "ТНВЭД": [
            "6403993600"
          ]
        },
        {
          "Пол": [
            "Мужской"
          ]
        },
        {
          "Стилистика": [
            "casual"
          ]
        },
        {
          "Предмет": "Платья"
        }
      ],
      "sizes": [
        {
          "techSize": "40-41",
          "wbSize": "",
          "price": 3999,
          "skus": [
            "1000000001"
          ]
        }
      ]
    }
  ]
]';
        $str = json_decode($str);
        dd($str);*/
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
            if(Auth::attempt($credentials, $remember)) {
                if (Auth::user()->hasRole('kpp-operator')) {
                    return redirect()->route('security.kpp');
                } elseif (Auth::user()->hasRole('personal-control')){
                    return redirect()->route('personal.control');
                } elseif(Auth::user()->hasRole('otdel-kadrov')) {
                    return redirect()->route('cabinet.employees.index');
                } elseif(Auth::user()->hasRole('kt-operator')) {
                    return redirect()->route('kt.kt_operator');
                } elseif(Auth::user()->hasRole('kt-crane')) {
                    return redirect()->route('kt.kt_crane');
                } elseif(Auth::user()->hasRole('kt-controller')) {
                    return redirect()->route('kt.controller');
                } elseif(Auth::user()->hasRole('ashana')) {
                    return redirect()->route('kitchen.index');
                } elseif(Auth::user()->hasRole('mark-manager')) {
                    return redirect()->route('mark.index');
                } elseif(Auth::user()->hasRole('mark-dispatcher')) {
                    return redirect()->route('mark.manager');
                } else {
                    return redirect()->route('cabinet');
                }
            } else {
                return back()->with('message', 'Не найдено пользователь с такими данными');
            }
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
