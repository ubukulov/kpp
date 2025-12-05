<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

class FirebaseController extends BaseApiController
{
    public function saveFcmToken(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate(['token' => 'required|string']);

        $user = $this->user;
        if ($user) {
            $user->fcm_token = $request->token;
            $user->save();
        }

        return response()->json(['success' => true]);
    }
}
