<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('cabinet.login');
    }

    public function forgetPassword()
    {
        return view('cabinet.forget-password');
    }

    public function authenticate(Request $request)
    {
        if(Auth::guard('employee')->attempt(['email' => $request->input('email'), 'password' => $request->input('password')])){
            return redirect()->route('cabinet');
        } else {
            return redirect()->back();
        }
    }
}
