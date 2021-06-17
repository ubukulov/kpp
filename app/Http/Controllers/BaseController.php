<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use View;

class BaseController extends Controller
{
    public function __construct()
    {
        $companies = Company::orderBy('short_en_name')->get();
        View::share('companies', $companies);
    }
}
