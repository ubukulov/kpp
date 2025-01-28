<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Models\TechniqueStock;
use Illuminate\Http\Request;
use Auth;

class TechniqueController extends Controller
{
    public function technique()
    {
        $techniques = TechniqueStock::where(['company_id' => Auth::user()->company_id])->get();
        return view('cabinet.technique.index', compact('techniques'));
    }
}
