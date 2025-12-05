<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;

class DispatcherController extends Controller
{
    public function index()
    {
        return view('cabinet.dispatcher.index');
    }
}
