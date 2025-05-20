<?php

namespace App\Classes;

use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;

class Parqour
{
    private $client;

    public function __construct()
    {
        if(!$this->client) {
            $this->client = new Client([
                'base_uri' => env('PARQOUR_URL'),
                'verify' => false
            ]);
        }
    }

    /**
     * @param $data array
     * @return bool|JsonResponse
     */
    public function addToWhiteList(array $data)
    {
        try {
            $data = [
                'plateNumber' => $data['plateNumber'],
                'additionMomentType' => 'PERMANENT',
                'whitelistType' => 'UNLIMITED',
                'comment' => $data['comment'],
                'groupName' => $data['groupName'],
                'fullName' => $data['fullName'],
            ];

            $response = $this->client->request('POST', env('PARQOUR_URL') . 'rest/v2/whitelist/create', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'auth' => [env('PARQOUR_USERNAME'), env('PARQOUR_PASSWORD')],
                'json' => $data
            ]);

            if($response->getStatusCode() == 200) {
                return true;
            } else {
                return false;
            }
        } catch (\guzzlehttp\exception\GuzzleException $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param $gov_number
     * @return bool|JsonResponse
     */
    public function removeFromWhiteList($gov_number)
    {
        try {
            $response = $this->client->request('DELETE', env('PARQOUR_URL') . "rest/whitelist/remove?plateNumber=$gov_number", [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'auth' => [env('PARQOUR_USERNAME'), env('PARQOUR_PASSWORD')],
            ]);

            if($response->getStatusCode() == 200) {
                return true;
            } else {
                return false;
            }
        } catch (\guzzlehttp\exception\GuzzleException $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }
}
