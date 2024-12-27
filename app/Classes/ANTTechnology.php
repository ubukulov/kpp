<?php

namespace App\Classes;

use GuzzleHttp\Client;

class ANTTechnology
{
    protected $apiUrl = "https://tsm.ant-tech.ru:19481/v1.1/";
    protected $token = "50cec7e1-22cf-4088-b32a-0380a04d3364";
    protected $client;

    public function __construct()
    {
        if(!$this->client) {
            $this->client = new Client([
                'base_uri' => $this->apiUrl,
                'verify' => false
            ]);
        }
    }

    public function getToken()
    {
        return $this->token;
    }

    public function stockUpdate($data)
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $response = $this->client->request('POST', $this->apiUrl . 'stock_update', [
            'content-type' => 'application/json',
            'body' => $data
        ]);

        if($response->getStatusCode() == 200) {
            return true;
        } else {
            return false;
        }
    }
}
