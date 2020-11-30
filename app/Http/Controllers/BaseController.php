<?php

namespace App\Http\Controllers;

use App\Company;
use Illuminate\Http\Request;
use View;

class BaseController extends Controller
{
    public function __construct()
    {
        $companies = Company::all();
        View::share('companies', $companies);
    }
}
