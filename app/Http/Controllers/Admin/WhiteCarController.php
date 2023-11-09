<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Kpp;
use App\Models\WclCompany;
use App\Models\WhiteCarList;
use App\Models\WhiteCarLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;

class WhiteCarController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $white_car_lists = WhiteCarList::orderBy('white_car_lists.id', 'DESC')
            ->selectRaw('wcl_companies.id,wcl_companies.wcl_id,white_car_lists.gov_number, companies.short_ru_name, wcl_companies.status, wcl_companies.created_at')
            ->join('wcl_companies', 'wcl_companies.wcl_id', '=', 'white_car_lists.id')
            ->join('companies', 'companies.id', '=', 'wcl_companies.company_id')
            //->paginate();
            ->get();

        return view('admin.white_car.index', compact('white_car_lists'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companies = Company::orderBy('short_en_name')->get();
        return view('admin.white_car.create', compact('companies'));
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

        /*$request->validate([
            'gov_number' => 'required|unique:white_car_lists|max:20',
        ]);*/

        // Все данные прошло валидацию
        DB::beginTransaction();
        try {
            $gov_number = trim($data['gov_number']);
            $company_id = trim($data['company_id']);
            $kpp_id = trim($data['kpp_id']);
            $data['gov_number'] = $gov_number;
            $kpp = Kpp::findOrFail($kpp_id);
            $data['kpp_name'] = $kpp->name;

            if(WhiteCarList::exists($gov_number)) {
                $white_car_list = WhiteCarList::where(['gov_number' => $gov_number])->first();
            } else {
                $white_car_list = WhiteCarList::create($data);
            }

            if(WclCompany::exists($white_car_list->id, $company_id)) {
                $wcl_company = WclCompany::where(['wcl_id' => $white_car_list->id, 'company_id' => $company_id])->first();
                $wcl_company->status = 'ok';
                $wcl_company->save();
            } else {
                $wcl_company = WclCompany::create([
                    'wcl_id' => $white_car_list->id, 'company_id' => $company_id, 'status' => 'ok'
                ]);
            }

            WhiteCarLog::create([
                'user_id' => Auth::guard('admin')->id(), 'message' => json_encode($data),
                'gov_number' => $white_car_list->gov_number, 'status' => $wcl_company->status
            ]);

            DB::commit();
            return redirect()->route('admin.white-car-list.index');
        } catch (\Exception $exception) {
            DB::rollBack();
            abort(500, 'Произошло ошибка на стороне сервера. Попробуйте позже'. $exception);
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
        $white_car_list = WhiteCarList::findOrFail($wcl_company->wcl_id);
        $companies = Company::orderBy('short_en_name')->get();
        return view('admin.white_car.edit', compact('white_car_list', 'companies', 'wcl_company'));
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

            if(WclCompany::exists($white_car_list->id, $data['company_id'])) {
                $wcl_company = WclCompany::where(['wcl_id' => $white_car_list->id, 'company_id' => $data['company_id']])->first();
                $wcl_company->status = $data['status'];
                $wcl_company->save();
            } else {
                $wcl_company = WclCompany::create([
                    'wcl_id' => $white_car_list->id, 'company_id' => $data['company_id'], 'status' => 'ok'
                ]);
            }

            WhiteCarLog::create([
                'user_id' => Auth::guard('admin')->id(), 'message' => json_encode($data),
                'gov_number' => $white_car_list->gov_number, 'status' => $wcl_company->status
            ]);

            DB::commit();
            return redirect()->route('admin.white-car-list.index');
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
        WhiteCarList::destroy($id);
        return redirect()->route('admin.white-car-list.index');
    }

    public function getWCLReports()
    {
        return view('admin.white_car.report');
    }

    public function getWCLChanges($company_id)
    {
        $company = Company::find($company_id);
        $result = WhiteCarLog::where(['users.company_id' => $company_id])
                    ->selectRaw('white_car_logs.*')
                    ->with('user')
                    ->join('users', 'users.id', 'white_car_logs.user_id')
                    ->leftJoin('white_car_lists', 'white_car_lists.gov_number', 'white_car_logs.gov_number')
                    ->get();
        //dd($result);
        $arr = [];
        foreach($result as $item) {
            $message = json_decode($item->message);
            $arr[] = [
                'date' => $item->created_at->format('d.m.Y H:i:s'),
                'operation_type' => $item->status,
                'full_name' => $item->user->full_name,
                'company' => $company->short_en_name,
                'gov_number' => $item->gov_number,
                'driver_name' => ($item->status == 'delete') ? '' : $message->full_name,
                'driver_pos'  => ($item->status == 'delete') ? '' : $message->position,
                'pass_type'   => ($item->status == 'delete') ? '' : $message->pass_type
            ];
        }

        return $arr;
    }

    public function importForm()
    {
        $kpps = Kpp::all();
        return view('admin.white_car.import', compact('kpps'));
    }

    public function importExecute(Request $request)
    {
        $company_id = $request->input('company_id');
        $company = Company::findOrFail($company_id);
        $kpp_id = $request->input('kpp_id');
        $kpp = Kpp::findOrFail($kpp_id);
        $file = $request->file('import_file');

        $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file->getRealPath());
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
        $spreadsheet = $reader->load($file->getRealPath());
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $importResults = [];

        foreach ($sheetData as $key=>$arr) {
            if ($key == 1) continue;
            $full_name = trim($arr['A']);
            $position = trim($arr['B']);
            $gov_number = str_replace(" ", "", $arr['C']);
            $mark_car = trim($arr['D']);
            $wcl = WhiteCarList::where(['gov_number' => strtoupper($gov_number)])->first();
            if($wcl){
                $wcl_company = WclCompany::where(['wcl_id' => $wcl->id, 'company_id' => $company_id])->first();
                if($wcl_company) {
                    $importResults[] = [
                        'company_name' => $company->short_ru_name, 'full_name' => $full_name, 'p_name' => $position,
                        'gov_number' => $gov_number, 'mark_car' => $mark_car, 'status' => 'Уже добавлено', 'kpp_name' => $wcl->kpp_name,
                        'date' => $wcl_company->created_at
                    ];
                } else {
                    $wcl_company = WclCompany::create([
                        'wcl_id' => $wcl->id, 'company_id' => $company_id, 'status' => 'ok'
                    ]);
                    $importResults[] = [
                        'company_name' => $company->short_ru_name, 'full_name' => $full_name, 'p_name' => $position,
                        'gov_number' => $gov_number, 'mark_car' => $mark_car, 'status' => 'Машина добавлен', 'kpp_name' => $wcl->kpp_name,
                        'date' => $wcl_company->created_at
                    ];
                }
            } else {
                DB::beginTransaction();
                try {
                    $wcl = WhiteCarList::create([
                        'gov_number' => $gov_number, 'mark_car' => $mark_car, 'full_name' => $full_name, 'position' => $position,
                        'kpp_name' => $kpp->name, 'pass_type' => 1
                    ]);
                    $wcl_company = WclCompany::create([
                        'wcl_id' => $wcl->id, 'company_id' => $company_id, 'status' => 'ok'
                    ]);
                    $wcl_log = WhiteCarLog::create([
                        'user_id' => Auth::guard('admin')->user()->id, 'gov_number' => $gov_number, 'status' => 'ok'
                    ]);
                    $importResults[] = [
                        'company_name' => $company->short_ru_name, 'full_name' => $full_name, 'p_name' => $position,
                        'gov_number' => $gov_number, 'mark_car' => $mark_car, 'status' => 'Машина добавлен', 'kpp_name' => $wcl->kpp_name,
                        'date' => $wcl_company->created_at
                    ];
                    DB::commit();
                } catch (\Exception $exception) {
                    DB::rollBack();
                }
            }
        }

        return response()->json($importResults);
    }
}
