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

    public function getUserRoles(): \Illuminate\Http\JsonResponse
    {
        $roles = $this->user->roles()->select('name', 'slug')->get()->makeHidden('pivot');
        return response()->json($roles);
    }
}
