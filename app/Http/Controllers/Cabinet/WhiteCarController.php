<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\WclCompany;
use App\Models\WhiteCarList;
use App\Models\WhiteCarLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;

class WhiteCarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $white_car_lists = WclCompany::orderBy('white_car_lists.id', 'DESC')
            //->with('company')
            ->select('wcl_companies.*', 'companies.short_ru_name', 'white_car_lists.gov_number')
            ->join('white_car_lists', 'wcl_companies.wcl_id', '=', 'white_car_lists.id')
            ->join('companies', 'companies.id', '=', 'wcl_companies.company_id')
            ->where(['wcl_companies.company_id' => Auth::user()->company_id])
            ->get();
        return view('cabinet.white_car.index', compact('white_car_lists'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('cabinet.white_car.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->except('_token');
        $data['company_id'] = Auth::user()->company_id;

        /*$request->validate([
            'gov_number' => 'required|unique:white_car_lists|max:20',
        ]);*/

        // Все данные прошло валидацию
        DB::beginTransaction();
        try {
            $gov_number = trim($data['gov_number']);
            $company_id = Auth::user()->company_id;
            $data['gov_number'] = $gov_number;

            if(WhiteCarList::exists($gov_number)) {
                $white_car_list = WhiteCarList::where(['gov_number' => $gov_number])->first();
            } else {
                $white_car_list = WhiteCarList::create($data);
            }

            if(WclCompany::exists($white_car_list->id, $company_id)) {
                $wcl_company = WclCompany::where(['wcl_id' => $white_car_list->id, 'company_id' => Auth::user()->company_id])->first();
                $wcl_company->status = 'ok';
                $wcl_company->save();
            } else {
                $wcl_company = WclCompany::create([
                    'wcl_id' => $white_car_list->id, 'company_id' => $company_id, 'status' => 'ok'
                ]);
            }

            WhiteCarLog::create([
                'user_id' => Auth::id(),
                'gov_number' => $white_car_list->gov_number, 'status' => $wcl_company->status,
                'message' => json_encode($data)
            ]);

            DB::commit();
            return redirect()->route('cabinet.white-car-list.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            abort(500, 'Произошло ошибка на стороне сервера. Попробуйте позже');
        }
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
        $wcl_company = WclCompany::findOrFail($id);
        $white_car_list = $wcl_company->wcl;
        //$wcl_company = WclCompany::where(['wcl_id' => $white_car_list->id, 'company_id' => Auth::user()->company_id])->first();
        return view('cabinet.white_car.edit', compact('white_car_list', 'wcl_company'));
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
        DB::beginTransaction();
        try {
            $data = $request->except('_token');
            $white_car_list = WhiteCarList::findOrFail($id);
            $white_car_list->update($data);

            if(WclCompany::exists($white_car_list->id, Auth::user()->company_id)) {
                $wcl_company = WclCompany::where(['wcl_id' => $white_car_list->id, 'company_id' => Auth::user()->company_id])->first();
                $wcl_company->status = $data['status'];
                $wcl_company->save();
            } else {
                $wcl_company = WclCompany::create([
                    'wcl_id' => $white_car_list->id, 'company_id' => Auth::user()->company_id, 'status' => 'ok'
                ]);
            }

            WhiteCarLog::create([
                'user_id' => Auth::id(),
                'gov_number' => $white_car_list->gov_number, 'status' => $wcl_company->status,
                'message' => json_encode($data)
            ]);

            DB::commit();
            return redirect()->route('cabinet.white-car-list.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            abort(500, 'Произошло ошибка на стороне сервера. Попробуйте позже');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $wcl_company = WclCompany::findOrFail($id);
        $white_car_list = $wcl_company->wcl;

        WhiteCarLog::create([
            'user_id' => Auth::id(),
            'gov_number' => $white_car_list->gov_number, 'status' => 'delete',
            'message' => 'Client destroy'
        ]);

        WclCompany::destroy($id);

        return redirect()->route('cabinet.white-car-list.index');
    }
}
