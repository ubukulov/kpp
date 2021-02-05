<?php

namespace App\Http\Controllers\Cabinet;

use App\Company;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;

class ReportController extends Controller
{
    public function index()
    {
        return view('cabinet.report.index');
    }
}
