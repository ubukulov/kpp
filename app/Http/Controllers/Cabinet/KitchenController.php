<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\BaseController;
use App\Models\AshanaLog;
use App\Traits\KitchenTraits;
use Illuminate\Http\Request;
use Auth;

class KitchenController extends BaseController
{
    use KitchenTraits;

    public function ashana()
    {
        $user = Auth::user();
        $logs = $user->ashana;
        return view('cabinet.ashana.index', compact('logs'));
    }

    public function talon()
    {
        $user = Auth::user();
        return view('cabinet.ashana.talon', compact('user'));
    }

    public function reports()
    {
        return view('cabinet.ashana.reports');
    }
}
