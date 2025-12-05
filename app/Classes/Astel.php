<?php

namespace App\Classes;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class Astel
{
    private Client $client;
    private string $base;

    public function __construct()
    {
        $this->base = rtrim(env('ASTEL_URL'), '/').'/';

        $this->client = new Client([
            'base_uri' => $this->base,
            'verify' => false,
            'timeout' => 10,
        ]);
    }

    public function getToken()
    {
        try {
            $loginCredentials = [
                'login' => env('ASTEL_USERNAME'),
                'password' => env('ASTEL_PASSWORD'),
            ];

            return $this->client->request('POST', 'generateAuthToken', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $loginCredentials
            ]);
        } catch (GuzzleException $e) {
            return $e;
        }
    }

    public function getData(?string $startDate = null, ?string $endDate = null)
    {
        $cacheKey = $startDate && $endDate
            ? "astel_data_{$startDate}_{$endDate}"
            : "astel_data_all";

        // Сначала проверяем кэш
        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        $path = 'getReportsMetringPoint/' . env('ASTEL_ID');
        if ($startDate && $endDate) {
            $path .= "/$startDate/$endDate";
        }

        try {
            $response = $this->requestWithTokenRetry('GET', $path);

            if ($response instanceof GuzzleResponse && $response->getStatusCode() === 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                $records = $data['data'] ?? [];

                // Кэшируем на 1 минуту
                Cache::put($cacheKey, $records, now()->addMinute());

                return $records;
            }

        } catch (\Exception $e) {
            Log::channel('astel')->error('Data request error: ' . $e->getMessage());
        }

        return [];
    }

    private function requestWithTokenRetry(string $method, string $url)
    {
        $token = Cache::get('astel_token');

        $response = $this->tryRequest($method, $url, $token);

        if (!($response instanceof GuzzleResponse)) {
            return $response;
        }

        if ($response->getStatusCode() !== 401) {
            return $response;
        }

        Log::channel('astel')->warning('Token expired, refreshing...');

        $newToken = $this->refreshToken();

        if ($newToken) {
            $retry = $this->tryRequest($method, $url, $newToken);

            return $retry instanceof GuzzleResponse
                ? $retry
                : $response;
        }

        return $response;
    }

    private function tryRequest(string $method, string $path, string $token = null)
    {
        try {
            return $this->client->request($method, $path, [
                'headers' => [
                    'Authorization' => 'Bearer ' . ($token ?? ''),
                    'Accept'        => 'application/json',
                ],
            ]);
        } catch (\Throwable $e) {
            Log::channel('astel')->error("Request error: {$e->getMessage()}");
            return $e;
        }
    }

    public function refreshToken(): ?string
    {
        try {
            $response = $this->client->post('generateAuthToken', [
                'json' => [
                    'login'    => env('ASTEL_USERNAME'),
                    'password' => env('ASTEL_PASSWORD'),
                ],
            ]);

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody()->getContents());

                Cache::put('astel_token', $data->token, now()->addHours(23)->addMinutes(55));

                Log::channel('astel')->info('Token refreshed');

                return $data->token;
            }
        } catch (\Exception $e) {
            Log::channel('astel')->error('Token refresh error: ' . $e->getMessage());
        }

        return null;
    }
}
