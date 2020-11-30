<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class AuthController extends BaseController
{
    public function loginForm()
    {
    	//dd(strtotime('1998-03-26'));
    	return view('login');
    }

    public function authenticate(Request $request)
    {
    	$credentials = $request->only('email', 'password');
    	//dd($data);
    	if(Auth::attempt($credentials)) {
    		return redirect()->route('security.kpp');
    	} else {
    		return redirect()->route('login');
    	}
    }
}
