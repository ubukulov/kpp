<?php

namespace App\Http\Controllers\Cabinet;

use App\BodyType;
use App\CategoryTC;
use App\Driver;
use App\Http\Controllers\Controller;
use App\Permit;
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
        $company = Auth::guard('employee')->user()->company;
        $permits = Permit::where(['company_id' => $company->id])->orderBy('id', 'DESC')->get();
        return view('cabinet.permit.index', compact('permits'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $category_tc = CategoryTC::all();
        $body_type = BodyType::all();
        return view('cabinet.permit.create', compact('category_tc', 'body_type'));
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
        $company = Auth::guard('employee')->user()->company;
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
}
