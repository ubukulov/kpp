<?php

namespace App\Http\Controllers\Cabinet;

use App\Driver;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Auth;

class CabinetController extends BaseController
{
    public function cabinet()
    {
        return view('cabinet.index');
    }

    public function getDriversList()
    {
        $result = Driver::orderBy('drivers.id', 'DESC')
            ->join('permits', 'permits.ud_number', '=', 'drivers.ud_number')
            ->whereNotNull(['path_docs_fac', 'path_docs_back'])
            ->get();
        $drivers = [];
        foreach($result as $item) {
            if(file_exists(public_path().'/uploads/'.$item->path_docs_fac)) {
                $drivers[] = $item;
            }
        }

        return json_encode($drivers);
    }
}
