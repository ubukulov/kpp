<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use App\Http\Controllers\Controller;
use App\Models\Permit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $companies = Company::all();
        return view('admin.report.index', compact('companies'));
    }

    public function getPermits(Request $request)
    {
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $company_id = $request->input('company_id');
        if($company_id == 0) {
            $permits = Permit::where(['status' => 'printed'])->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])->get();
        } else {
            $permits = Permit::where(['company_id' => $company_id, 'status' => 'printed'])->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])->get();
        }
        return json_encode($permits);
    }

    public function statistics()
    {
        $cur_month = DB::select("SELECT COUNT(*) as cnt FROM permits
                         WHERE date_in IS NOT NULL AND status='printed'
                         AND MONTH(date_in)=MONTH(CURDATE()) AND YEAR(date_in)=YEAR(CURDATE())");

        $pre_month = DB::select("SELECT COUNT(*) as cnt FROM permits
                         WHERE date_in IS NOT NULL AND status='printed'
                         AND MONTH(date_in)=MONTH(DATE_ADD(NOW(), INTERVAL -1 MONTH)) AND YEAR(date_in)=YEAR(NOW())");

        return view('admin.report.statistics', compact('cur_month', 'pre_month'));
    }
}
