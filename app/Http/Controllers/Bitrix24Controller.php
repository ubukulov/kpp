<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BitrixAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class Bitrix24Controller extends Controller
{
    public function install(Request $request)
    {
        // Получаем auth-данные от Bitrix24
        $auth = $request->input('auth');

        Log::info('Bitrix24 установка', $auth);

        // Сохраняем или обновляем аккаунт
        BitrixAccount::updateOrCreate(
            ['member_id' => $auth['member_id']],
            [
                'domain' => $auth['domain'],
                'access_token' => $auth['access_token'],
                'refresh_token' => $auth['refresh_token'],
                'user_id' => $auth['user_id'],
                'expires_in' => $auth['expires_in'],
            ]
        );

        // Пример регистрации события — OnTaskUpdate
        $bindResponse = Http::post("https://{$auth['domain']}/rest/event.bind.json", [
            'event' => 'OnTaskUpdate',
            'handler' => route('bitrix24.events'), // Убедись, что маршрут существует
            'auth' => $auth['access_token'],
        ]);

        Log::info('Регистрация события OnTaskUpdate', $bindResponse->json());

        return response()->json(['result' => 'ok']);
    }

    public function events(Request $request)
    {
        if ($request->has('code') && !empty($request->code)) {
            $code = $request->input('code');

            $clientId = env('BITRIX24_CLIENT_ID');
            $clientSecret = env('BITRIX24_CLIENT_SECRET');

            $url = "https://portal.dlg.kz/oauth/token/?" . http_build_query([
                    'grant_type' => 'authorization_code',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'code' => $code,
                    'redirect_uri' => "https://kpp.dlg.kz:8900/bitrix24/access-token"
                ]);

            // Открываем Bitrix24 в новой вкладке (на клиенте)
            return redirect()->away($url);
        }

        return response()->json(['error' => 'Не получено код авторизации']);
    }

    public function createAccount()
    {
        $data = [
            "access_token" =>
                "b7839c68007ae4e6003a5cc8000004340000072126421dd746eb1e45637c5bcaad8427",
            "expires" => 1755087799,
            "expires_in" => 3600,
            "scope" => "app",
            "domain" => "portal.dlg.kz",
            "server_endpoint" => "https://oauth.bitrix.info/rest/",
            "status" => "L",
            "client_endpoint" => "https://portal.dlg.kz/rest/",
            "member_id" => "a36f380eae399695dc23be715ff66471",
            "user_id" => 1076,
            "refresh_token" =>
                "a702c468007ae4e6003a5cc800000434000007b3ca420f64fd8a72bbd598c79e828d48",
        ];
        if (isset($data['access_token'])) {
            BitrixAccount::updateOrCreate(
                ['member_id' => $data['member_id']],
                [
                    'domain' => $data['domain'],
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'],
                    'user_id' => $data['user_id'],
                    'expires_in' => $data['expires_in'],
                    'expires_at' => now()->addSeconds($data['expires_in'])
                ]
            );

            return response()->json(['message' => 'Успешно получен токен и сохранён']);
        }
    }
}
