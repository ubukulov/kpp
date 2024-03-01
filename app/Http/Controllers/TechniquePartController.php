<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TechniquePartController extends BaseController
{
    public function create()
    {
        return view('technique_parts.create');
    }
}
