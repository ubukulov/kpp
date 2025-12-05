<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\AuthService;
use App\Services\RoleRedirect;

class AuthController
{
    protected AuthService $authService;
    protected RoleRedirect $roleRedirect;

    public function __construct(AuthService $authService, RoleRedirect $roleRedirect)
    {
        $this->authService = $authService;
        $this->roleRedirect = $roleRedirect;
    }

    public function loginForm()
    {
    	return view('login');
    }

    public function authenticate(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->only('email', 'password');
    	$remember = (bool) $request->input('remember');

        $messages = [
            'email.required' => 'Поле Email обязательно',
            'email.email' => 'Некорректный формат Email',
            'password.required' => 'Поле пароль обязательно'
        ];

    	$validator = Validator::make($data, [
            'email' => 'required|email',
            'password' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator->errors())->withInput();
        }

        $user = $this->authService->attempt($data['email'], $data['password']);

        $token = $this->authService->createTokenFor($user);
        session()->put('token', $token);

        Auth::login($user, $remember);

        return $this->roleRedirect->redirectFor($user);
    }

    public function logout(): \Illuminate\Http\RedirectResponse
    {
        if (Auth::check()) {
            $session = Session::where(['user_id' => Auth::id()])->first();
            if($session) {
                $session->delete();
            }

            $this->authService->logout(Auth::user());
        }

        return redirect()->route('login');
    }
}
