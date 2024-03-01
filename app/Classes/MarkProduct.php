<?php


namespace App\Classes;


use GuzzleHttp\Client;

class MarkProduct
{
    protected $apiUrl = 'https://omscloud.ismet.kz/api/v2/';
    protected $token = '1b0638b7-e2b9-4343-96f9-0a0879ab7418';
    protected $omsId = 'c0ce9a9a-7422-4143-9d7d-2ef714d3c642';
    protected $client;

    public function __construct()
    {
        if(!$this->client) {
            $this->client = new Client(['base_uri' => $this->apiUrl,
                'verify' => false,
                'headers' => [
                    'Accept' => 'application/json',
                    'clientToken' => $this->token,
                ],
            ]);
        }
    }

    public function getKMForProducts()
    {
        $arr = [
            '04871228020134',
            '04871228020127',
            '04871228020110',
            '04871228020103',
            '04871228020066',
        ];

        $orderId = '81458ef2-94fe-4707-b53b-4a17343c603a';

        foreach($arr as $gt) {
            $response = $this->client->get("shoes/buffer/status?omsId=$this->omsId&orderId=$orderId&gtin=$gt");

            $data = $response->getBody()->getContents();

            if($response->getStatusCode() == 200) {
                $data = json_decode($data);


                $response = $this->client->get("shoes/codes?omsId=$this->omsId&gtin=$data->gtin&orderId=$orderId&quantity=$data->totalCodes");
                $data_km = $response->getBody()->getContents();
                if($response->getStatusCode() == 200) {
                    $data_km = json_decode($data_km);
                    if(count($data_km->codes) > 1) {
                        foreach ($data_km->codes as $km) {
                            echo $data->gtin." |".$km."<br>";
                        }
                    } else {
                        echo $data->gtin." |".$data_km->codes[0]."<br>";
                    }
                }
            }
        }
    }
}
