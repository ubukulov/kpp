<?php

namespace App\Classes;

use GuzzleHttp\Client;
use Cache;

class Astel
{
    private $client;

    public function __construct()
    {
        if(!$this->client) {
            $this->client = new Client([
                'base_uri' => env('ASTEL_URL'),
                'verify' => false
            ]);
        }
    }

    public function getToken()
    {
        try {
            $loginCredentials = [
                'login' => env('ASTEL_USERNAME'),
                'password' => env('ASTEL_PASSWORD'),
            ];
            $response = $this->client->request('POST', env('ASTEL_URL') . 'generateAuthToken', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode($loginCredentials)
            ]);

            if($response->getStatusCode() == 200) {
                return response($response->getBody()->getContents(), 200);
            } else {
                return false;
            }
        } catch (\guzzlehttp\exception\GuzzleException $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }

    public function getData(?string $startDate = null, ?string $endDate = null)
    {
        try {
            $baseUrl = env('ASTEL_URL') . 'getReportsMetringPoint/' . env('ASTEL_ID');
            $url = $startDate && $endDate ? "$baseUrl/$startDate/$endDate" : $baseUrl;
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . Cache::get('astel_token'),
                    'Accept' => 'application/json',
                ],
            ]);

            if($response->getStatusCode() == 200) {
                return response($response->getBody()->getContents(), 200);
            } else {
                return false;
            }
        } catch (\guzzlehttp\exception\GuzzleException $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }
}
