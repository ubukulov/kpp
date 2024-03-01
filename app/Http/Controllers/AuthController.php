<?php

namespace App\Http\Controllers;

use App\Models\Session;
use CKUD;
use App\Models\CKUDLogs;
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
        /*$lastEvent = CKUDLogs::orderBy('id', 'DESC')->first();
        $events = CKUD::getEvents($lastEvent->ckud_id);
        $events = str_replace('Биометрический считыватель "Выход"', "Биометрический считыватель Выход", $events);
        $events = str_replace('Биометрический считыватель "Вход"', "Биометрический считыватель Вход", $events);
        dd(json_decode($events, true));*/
//        dd(json_decode($events, true, 1024,JSON_OBJECT_AS_ARRAY));
        /*if($events AND count($events['Events']) != 0) {
            foreach($events['Events'] as $event) {
                $data = $event;
                $data['ckud_id'] = $event['Id'];
                CKUDLogs::create($data);
            }
        }*/
        /*$file = fopen(public_path() . '/1.txt', 'r');
        $count_file_lines = count(file(public_path() . '/1.txt'));
        $file2 = public_path() . "/2.txt";
        if (!$fp = fopen($file2, 'a')) {
            echo "Не могу открыть файл ($file2)";
            exit;
        }
        for ($i = 0; $i < $count_file_lines; $i++) {
            $ss = explode("91KZF0", fgets($file));
            file_put_contents($file2, "$ss[0] \n", FILE_APPEND);
        }

        fclose($file);
        fclose($fp);*/
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
                } elseif($user->hasRole('technique-controller')) {
                    return redirect()->route('technique.controller');
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
        $session = Session::where(['user_id' => Auth::id()])->first();
        if($session) {
            $session->delete();
        }
        Auth::logout();
        return redirect()->route('login');
    }
}
