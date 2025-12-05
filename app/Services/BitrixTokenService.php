<?php

namespace App\Services;

use App\Models\BitrixAccount;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
class BitrixTokenService
{
    public function refreshToken(BitrixAccount $account)
    {
        $response = Http::timeout(10)->asForm()->post(env('BITRIX24_REFRESH_URL'), [
            'grant_type'    => 'refresh_token',
            'client_id'     => env('BITRIX24_CLIENT_ID'),
            'client_secret' => env('BITRIX24_CLIENT_SECRET'),
            'refresh_token' => $account->refresh_token,
        ]);

        if ($response->failed()) {
            throw new \Exception('Token refresh error: ' . $response->body());
        }

        $data = $response->json();

        $account->update([
            'access_token'  => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_in'    => $data['expires_in'],
            'expires_at'    => Carbon::now()->addSeconds($data['expires_in']),
        ]);

        return $data['access_token'];
    }
}
