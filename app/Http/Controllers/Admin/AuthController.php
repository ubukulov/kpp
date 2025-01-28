<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('admin.login');
    }

    public function authenticate(Request $request)
    {
        if(Auth::guard('admin')->attempt(['email' => $request->input('email'), 'password' => $request->input('password')])){
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->back();
        }
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}
