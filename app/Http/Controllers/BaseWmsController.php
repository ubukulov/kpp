<?php

namespace App\Http\Controllers;

use App\Services\WmsConnectionService;

class BaseWmsController extends Controller
{
    protected WmsConnectionService $connectionService;

    public function __construct(WmsConnectionService $connectionService)
    {
        $this->connectionService = $connectionService;
    }
}
