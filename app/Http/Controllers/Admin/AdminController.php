<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Artisan;
use Auth;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function getSendByWhatsApp()
    {
        return view('admin.mail.index');
    }

    public function sendByWhatsApp(Request $request)
    {
        $message = $request->input('prefer');
        Artisan::call('whatsapp:run', [
            'msg' => $message
        ]);

        return redirect()->route('admin.whatsapp.index');
    }

    public function authByEmployee($employee_id)
    {
        $user = User::findOrFail($employee_id);
        Auth::login($user);
        return redirect()->route('cabinet');
    }
}
