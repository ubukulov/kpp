<?php


namespace App\Classes;

use Cache;
use GuzzleHttp\Client;

class RusGUARD
{
    protected $client;
    protected $data = array();

    public function __construct()
    {
        $this->data = [
            'host' => env('CKUD_HOST'),
            'user' => env('CKUD_USERNAME'),
            'psw'  => env('CKUD_PASSWORD')
        ];

        if(!$this->client) {
            $this->client = new Client(['base_uri' => env('CKUD_SERVER'), 'verify' => false]);
        }
    }

    public function getGroups()
    {
        if(Cache::has( 'rus_groups')) {
            return Cache::get('rus_groups');
        } else {
            $this->data['cmd'] = 'GetAcsEmployeeGroupsFull';
            $response = $this->client->get('?dat='.json_encode($this->data));
            $result = json_decode($response->getBody()->getContents());

            Cache::put('rus_groups', $result, 1440);
            return $result;
        }
    }

    public function getEmployees($pageNumber = 0, $pageSize = 1000)
    {
        $this->data['cmd'] = 'GetPageFilteredEmployeesByGroup';
        $this->data['pageNumber'] = $pageNumber;
        $this->data['pageSize'] = $pageSize;
        $response = $this->client->get('?dat='.json_encode($this->data));
        return json_decode($response->getBody()->getContents());
    }

    public function addEmployee($data)
    {
        $this->data['cmd'] = 'AddAcsEmployee';
        $this->data['Comment'] = $data['Comment'];
        $this->data['employeeGroupID'] = $data['employeeGroupID'];
        $this->data['FirstName'] = $data['FirstName'];
        $this->data['LastName'] = $data['LastName'];
        $this->data['SecondName'] = $data['SecondName'];
        $this->data['Number'] = $data['Number'];
        $this->data['KeyNumber'] = $data['KeyNumber'];
        $this->data['ResidentialAddress'] = $data['ResidentialAddress'];
        $this->data['photo_http'] = $data['photo_http'];
        $response = $this->client->get('?dat='.json_encode($this->data, JSON_UNESCAPED_SLASHES));
        return $response->getStatusCode();
    }
}
