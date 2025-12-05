<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * @throws ValidationException
     */
    public function attempt(string $email, string $password) :User
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Не найдено пользователь с такими данными'],
            ]);
        }

        return $user;
    }

    public function createTokenFor(User $user) : string
    {
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
            return $user->createToken('API Token')->plainTextToken;
        }

        return '';
    }

    public function logout(?User $user) :void
    {
        if (! $user) return;

        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }

        Auth::logout();
    }
}
