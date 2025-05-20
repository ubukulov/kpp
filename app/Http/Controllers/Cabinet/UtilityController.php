<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use ASTEL;

class UtilityController extends Controller
{
    public function energy()
    {
        $records = [];
        $records_for_period = [];

        if(isset($_GET['start_date']) && isset($_GET['end_date'])){
            $response = ASTEL::getData($_GET['start_date'], $_GET['end_date']);
            if ($response->getStatusCode() == 200) {
                $responseContent = json_decode($response->getContent());
                $records_for_period = $responseContent->data;
            }
        }

        $response = ASTEL::getData();
        if ($response->getStatusCode() == 200) {
            $responseContent = json_decode($response->getContent());
            $records = $responseContent->data;
        }


        return view('cabinet.utilities.energy', compact('records', 'records_for_period'));
    }
}
