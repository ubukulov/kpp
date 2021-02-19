<?php

namespace App\Http\Controllers\Cabinet;

use App\Models\BT;
use App\Models\Car;
use App\Models\Direction;
use App\Models\Driver;
use App\Http\Controllers\Controller;
use App\Models\LiftCapacity;
use App\Models\Permit;
use Illuminate\Http\Request;
use Auth;

class PermitController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $company = Auth::user()->company;
        $permits = Permit::where(['company_id' => $company->id, 'is_driver' => 2])->orderBy('id', 'DESC')->get();
        return view('cabinet.permit.index', compact('permits'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category_tc = LiftCapacity::all();
        $body_type = BT::all();
        $directions = Direction::all();
        return view('cabinet.permit.create', compact('category_tc', 'body_type', 'directions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $company = Auth::user()->company;
        $data['company_id'] = $company->id;
        $data['company'] = $company->short_ru_name;
        $data['is_driver'] = 2;
        $data['gov_number'] = mb_strtoupper(trim($data['gov_number']));
        $data['tex_number'] = mb_strtoupper(trim($data['tex_number']));
        $data['ud_number'] = mb_strtoupper(trim($data['ud_number']));
        $data['last_name'] = mb_strtoupper($data['last_name']);
        $data['status'] = 'awaiting_print';

        Permit::create($data);

        // Если новый водитель, то добавляем в справочник
        if (!Driver::exists($data['ud_number'])) {
            Driver::create([
                'fio' => $data['last_name'], 'phone' => $data['phone'], 'ud_number' => $data['ud_number']
            ]);
        }

        // Если новая машина, то добавляем в справочник
        if (!Car::exists($data['tex_number'])) {
            Car::create([
                'tex_number' => $data['tex_number'], 'gov_number' => $data['gov_number'], 'mark_car' => $data['mark_car'],
                'pr_number' => mb_strtoupper($data['pr_number']), 'lc_id' => $data['lc_id'], 'bt_id' => $data['bt_id'],
                'from_company' => $data['from_company']
            ]);
        } else {
            $car = Car::where(['tex_number' => $data['tex_number']])->first();
            $car->lc_id = $data['lc_id'];
            $car->bt_id = $data['bt_id'];
            $car->from_company = $data['from_company'];
            $car->save();
        }
        return redirect()->route('cabinet.permits.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getPermits(Request $request)
    {
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $company_id = $request->input('company_id');
        $permits = Permit::where(['company_id' => $company_id, 'status' => 'printed'])->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])->get();
        return json_encode($permits);
    }
}
