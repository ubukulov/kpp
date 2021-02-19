<?php

namespace App\Http\Controllers\Admin;

use App\Models\Driver;
use App\Http\Controllers\Controller;

class DriverController extends Controller
{
    public function index()
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
        return view('admin.driver.index', compact('drivers'));
    }
}
