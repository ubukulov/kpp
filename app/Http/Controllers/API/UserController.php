<?php


namespace App\Http\Controllers\API;


use App\Models\User;

class UserController extends BaseApiController
{
    public function getUserInfoByToken()
    {
        $user = User::with('position', 'company')->findOrFail($this->user->id);
        return response()->json($user);
    }

    public function getUserRoles()
    {
        $roles = $this->user->roles;
        return response()->json($roles);
    }
}
