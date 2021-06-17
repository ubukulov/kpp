<?php

namespace App\Http\Controllers\Admin;

use App\Models\Driver;
use App\Http\Controllers\Controller;
use Cache;

class DriverController extends Controller
{
    public function index()
    {
        if (Cache::has('admin_drivers')) {
            $drivers = Cache::get('admin_drivers');
        } else {
            $result = Driver::orderBy('drivers.id', 'DESC')
                ->join('permits', 'permits.ud_number', '=', 'drivers.ud_number')
                ->whereNotNull(['path_docs_fac', 'path_docs_back'])
                ->get();
            $drivers = [];
            foreach($result as $item) {
                $str = $item->path_docs_fac;
                $pos = strpos($str, 'uploads');
                if ($pos === false) {
                    $path_to_file = public_path() . '/uploads/' . $item->path_docs_fac;
                } else {
                    $path_to_file = public_path() . $item->path_docs_fac;
                }

                if(file_exists($path_to_file)) {
                    $drivers[] = $item;
                }
            }
            // Храним в кэше 1 неделя
            Cache::put('admin_drivers', $drivers, 604800);
        }

        return view('admin.driver.index', compact('drivers'));
    }
}
