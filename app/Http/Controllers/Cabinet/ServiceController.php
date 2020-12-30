<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Sk;

class ServiceController extends Controller
{
    public function index()
    {
        Sk::sendHttpRequest();
        return view('cabinet.service.index');
    }
}
