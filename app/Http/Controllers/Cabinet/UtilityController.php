<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use ASTEL;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

class UtilityController extends Controller
{
    public function energy()
    {
        $startDate = $_GET['start_date'] ?? null;
        $endDate   = $_GET['end_date'] ?? null;
        $records_for_period = [];

        if ($startDate && $endDate) {
            $records_for_period = ASTEL::getData($startDate, $endDate);
        }

        // Получаем все данные
        $records = ASTEL::getData();

        return view('cabinet.utilities.energy', compact('records', 'records_for_period'));
    }
}
