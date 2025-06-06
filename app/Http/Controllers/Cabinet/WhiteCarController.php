<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Kpp;
use App\Models\Permit;
use App\Models\WclCompany;
use App\Models\WhiteCarList;
use App\Models\WhiteCarLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use PARQOUR;

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
            ->select('wcl_companies.*', 'companies.short_ru_name', 'white_car_lists.gov_number', 'white_car_lists.full_name')
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
        $kpps = Kpp::all();
        return view('cabinet.white_car.create', compact('kpps'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $data = $request->except('_token');
        $data['company_id'] = Auth::user()->company_id;
        //$kpp = Kpp::findOrFail($data['kpp_id']);
        //$data['kpp_name'] = $kpp->name;

        /*$request->validate([
            'gov_number' => 'required|unique:white_car_lists|max:20',
        ]);*/

        // Все данные прошло валидацию
        DB::beginTransaction();
        try {
            $gov_number = trim($data['gov_number']);
            $company_id = Auth::user()->company_id;
            $company = Company::findOrFail($company_id);
            $data['gov_number'] = strtoupper($gov_number);

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

            // отправка в систему Parquor
            $comment = (!is_null($white_car_list->full_name)) ? $company->short_en_name . "|" . $white_car_list->full_name : $company->short_en_name;
            $params = [
                'plateNumber' => $white_car_list->gov_number,
                'comment' => $comment,
                'groupName' => $company->short_en_name,
                'fullName' => $white_car_list->full_name,
            ];

            PARQOUR::addToWhiteList($params);

            DB::commit();
            return redirect()->route('cabinet.white-car-list.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            abort(400, $exception->getMessage());
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
        $kpps = Kpp::all();
        //$wcl_company = WclCompany::where(['wcl_id' => $white_car_list->id, 'company_id' => Auth::user()->company_id])->first();
        return view('cabinet.white_car.edit', compact('white_car_list', 'wcl_company', 'kpps'));
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
            //$kpp = Kpp::findOrFail($data['kpp_id']);
            //$data['kpp_name'] = $kpp->name;
            $company_id = Auth::user()->company_id;
            $company = Company::findOrFail($company_id);
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

            // отправка в систему Parquor
            $comment = (!is_null($white_car_list->full_name)) ? $company->short_en_name . "|" . $white_car_list->full_name : $company->short_en_name;
            $params = [
                'plateNumber' => $white_car_list->gov_number,
                'comment' => $comment,
                'groupName' => $company->short_en_name,
                'fullName' => $white_car_list->full_name,
            ];
            PARQOUR::addToWhiteList($params);

            DB::commit();
            return redirect()->route('cabinet.white-car-list.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            abort(500, $exception->getMessage());
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
        $gov_number = $white_car_list->gov_number;

        WhiteCarLog::create([
            'user_id' => Auth::id(),
            'gov_number' => $gov_number, 'status' => 'delete',
            'message' => 'Client destroy'
        ]);

        WclCompany::destroy($id);

        // удаляем из Parqour
        PARQOUR::removeFromWhiteList($gov_number);

        return redirect()->route('cabinet.white-car-list.index');
    }

    public function guestCars()
    {
        /*$white_car_lists = WhiteCarList::where(['white_car_lists.pass_type' => 3, 'wcl_companies.company_id' => Auth::user()->company_id]) // 3 для гостевых машин
            ->selectRaw('wcl_companies.id,wcl_companies.wcl_id,white_car_lists.gov_number, companies.short_ru_name, wcl_companies.status, wcl_companies.created_at, white_car_lists.full_name')
            ->join('wcl_companies', 'wcl_companies.wcl_id', '=', 'white_car_lists.id')
            ->join('companies', 'companies.id', '=', 'wcl_companies.company_id')
            ->orderBy('white_car_lists.id', 'DESC')
            ->get();*/
        $white_car_lists = Permit::where(['type' => 'guest', 'company_id' => Auth::user()->company_id])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('cabinet.white_car.guests', compact('white_car_lists'));
    }

    public function guestCreate()
    {
        return view('cabinet.white_car.guest-create');
    }

    public function guestStore(Request $request)
    {
        $data = $request->all();
        $company = Company::findOrFail(Auth::user()->company_id);
        $data['company'] = $company->short_en_name;
        $data['company_id'] = $company->id;
        $data['status'] = 'exit_permitted';
        $data['type'] = 'guest';
        $data['planned_arrival_date'] = date("Y-m-d H:i:s", strtotime($request->input('planned_arrival_date')));
        Permit::create($data);

        return redirect()->route('cabinet.white-cars.guest.index');

        /*DB::beginTransaction();
        try {
            $wcl = WhiteCarList::create([
                'gov_number' => $gov_number, 'pass_type' => 3
            ]);

            WclCompany::create([
                'wcl_id' => $wcl->id, 'company_id' => $company->id, 'status' => 'ok'
            ]);

            // отправка в систему Parquor
            $params = [
                'plateNumber' => $wcl->gov_number,
                'comment' => $company->short_en_name,
                'groupName' => "Гостевой",
                'fullName' => "Гость"
            ];

            PARQOUR::addToWhiteList($params);

            DB::commit();

            return redirect()->route('cabinet.white-cars.guest.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $exception->getMessage()]);
        }*/
    }
}
