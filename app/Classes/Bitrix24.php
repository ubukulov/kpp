<?php

namespace App\Classes;

use App\Models\BitrixAccount;
use App\Models\Status;
use GuzzleHttp\Client;

class Bitrix24
{
    private $client;

    public function __construct()
    {
        if(!$this->client) {
            $this->client = new Client([
                'base_uri' => env('BITRIX24_URL'),
                'verify' => false
            ]);
        }
    }

    public static function getAccessToken()
    {
        $bitrix24 = BitrixAccount::where(['domain' => 'portal.dlg.kz'])->first();
        return $bitrix24->access_token;
    }

    public function createSmartProcess($request)
    {
        try {
            $description = str_replace("</li>", "</li>\n", $request['description']);
            $description = html_entity_decode(strip_tags($description));
            switch ($request['typeCompany']) {
                case 'resident':
                    $categoryId = 19;
                break;

                default:
                    $categoryId = 16;
                break;
            }

            $data = [
                'auth' => self::getAccessToken(),
                'entityTypeId' => env('BITRIX24_SMART_PROCESS_ID'),
                'fields' => [
                    'categoryId' => $categoryId,
                    'ufCrm12_1755258667381' => $request['serviceCategoryId'],
                    "title" => $request['name'],
                    'ufCrm12_1755256862652' => $request['company'],
                    'ufCrm12_1755256988147' => $request['companyBin'],
                    'ufCrm12_1755257007694' => $request['quantity'],
                    'ufCrm12_1756811760334' => $description,
                    'ufCrm12_1755577814908' => $request['contractorId'], // подрядчик
                    'ufCrm12_1757580087353' => $request['fullName'],
                    'ufCrm12_1757580098958' => $request['email'],
                    'ufCrm12_1757580109352' => $request['phone'],
                    'ufCrm12_1757669692' => $request['dateTime']
                ]
            ];

            $response = $this->client->request('POST', 'crm.item.add', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $data
            ]);

            $body = json_decode($response->getBody(), true);

            if($response->getStatusCode() == 200) {
                return $body['result'];
            }

            return false;

            /*$body = json_decode($response->getBody(), true);
            if (isset($body['result'])) {
                return $body['result'];
            }
            return $body;*/
        } catch (\guzzlehttp\exception\GuzzleException $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }

    public function getStage($id)
    {
        try {
            $data = [
                'auth' => self::getAccessToken(),
                'entityTypeId' => env('BITRIX24_SMART_PROCESS_ID'),
                'id' => $id
            ];

            $response = $this->client->request('POST', 'crm.item.get', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $data
            ]);

            $body = json_decode($response->getBody(), true);

            if($response->getStatusCode() == 200) {
                return $body['result']['item'];
            }

            return false;
        } catch (\guzzlehttp\exception\GuzzleException $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }
}
