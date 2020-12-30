<?php


namespace App\Classes;


class ServiceDesk
{
    protected $api_key = 'B58B291A-6AD9-4134-9903-BECEF36625FC';
    protected $url = 'http://servicedesk:8080/sdpapi/request/?OPERATION_NAME=ADD_REQUEST&';

    public function sendHttpRequest()
    {
        $json_values = [
            'INPUT_DATA' => [
                'operation' => [
                    'details' => [
                        'requester' => 'Павел Лебедев',
                        'subject' => 'Проверка №1',
                        'description' => 'Заявка сформирована в система КПП',
                        'requesttemplate' => 'Unable to browse',
                        'priority' => 'High',
                        'site' => 'New York',
                        'group' => 'Network',
                        'technician' => 'asda',
                        'level' => 'Tier 3',
                        'status' => 'open',
                        'service' => 'Email'
                    ]
                ]
            ]
        ];
		//http://servicedesk:8080/WOListView.do
        $ch = curl_init();
        $this->url = $this->url . 'TECHNICIAN_KEY=' . $this->api_key.'&format=json';
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json_values));
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL, $this->url);
        //curl_setopt($ch,CURLOPT_USERPWD, $this->username.':'.$this->password);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        $output = curl_exec($ch);
        echo $output;
        curl_close($ch);
    }
}
