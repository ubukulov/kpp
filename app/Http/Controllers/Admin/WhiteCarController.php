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
        /*$arr = [
            ['full_name' => "Касаткин Кирилл Владимирович",'position' => "Начальник склада", 'gov_number' => "096XKA02", 'mark_car' => "Mitsubishi Pajero"],
            ['full_name' => "Абиыр Рустем Мухтарбекулы",'position' => "Старший администратор склада", 'gov_number' => "978BNZ05", 'mark_car' => "Nissan Cefiro"],
            ['full_name' => "Слангожа Еламан",'position' => "Администратор склада", 'gov_number' => "430AJJ05", 'mark_car' => "Chevrolet Cobalt "],
            ['full_name' => "Бейспаев Даурен Нурадинович",'position' => "Супервайзер", 'gov_number' => "391SNA02", 'mark_car' => "Mitsubishi Montero Sport  "],
            ['full_name' => "Долгов Евгений Александрович",'position' => "Техник", 'gov_number' => "258DLB05", 'mark_car' => "Mitsubishi L400"],
            ['full_name' => "Рамазан Серик",'position' => "Грузчик", 'gov_number' => "792ADG05", 'mark_car' => "Mitsubishi Galant"],
            ['full_name' => "Есимкулов Думан",'position' => "Грузчик", 'gov_number' => "129AIY05", 'mark_car' => "Mitsubishi Galant"],
            ['full_name' => "Аятхан Азамат Азатұлы",'position' => "Администратор склада", 'gov_number' => "233zsz05", 'mark_car' => "Audi C4"],
            ['full_name' => "Бондаренко Антон Сергеевич",'position' => "Супервайзер", 'gov_number' => "347XRA02", 'mark_car' => " Lexus RX300"],
            ['full_name' => "Головков Денис Васильевич",'position' => "Инженер", 'gov_number' => "542ACB05", 'mark_car' => "Subaru Legacy"],
            ['full_name' => "Медеубеков Ноян",'position' => "Администратор склада", 'gov_number' => "727AIW05", 'mark_car' => "Toyota Corolla"],
            ['full_name' => "Мащанло Даур Юсупович",'position' => "Служба охраны", 'gov_number' => "059SZA02", 'mark_car' => "Nissan Almera"],
            ['full_name' => "Нургожаев Айдын",'position' => "Служба охраны", 'gov_number' => "694IYA02", 'mark_car' => "Reno Sandero"],
            ['full_name' => "Белеков Жулдыз",'position' => "Инженер", 'gov_number' => "770AZA05", 'mark_car' => "BMV X5"],
            ['full_name' => "Бектөреев Сағидулла ",'position' => "Инженер", 'gov_number' => "294AKF02", 'mark_car' => "Chevrolet Cobalt"],
            ['full_name' => "Косбаев Жандос Ергалиевич",'position' => "Инженер", 'gov_number' => "484OCZ05", 'mark_car' => "Toyota Camry"],
            ['full_name' => "Шалабаев Кайрат",'position' => "Грузчик", 'gov_number' => "636AHF05", 'mark_car' => "Toyota Corolla"],
            ['full_name' => "Гаитов Азиз",'position' => "Грузчик", 'gov_number' => "551UWY05", 'mark_car' => "ВАЗ 211440"],
            ['full_name' => "Кодебаев Айдын",'position' => "Грузчик", 'gov_number' => "786AKC05", 'mark_car' => "Toyota Scepter"],
            ['full_name' => "Уварова Евгения",'position' => "Администратор склада", 'gov_number' => "339NVA05", 'mark_car' => "SUZUKI"],
            ['full_name' => "Капай Аскат",'position' => "Администратор склада", 'gov_number' => "577AEK05", 'mark_car' => "AUDI100"],
            ['full_name' => "Служебный автомобиль",'position' => "СЕО", 'gov_number' => "017LS02", 'mark_car' => "Lexus LX 570"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "Н769039", 'mark_car' => "Mercedes-Benz 315 Cdi Sprinter Van"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "Н769040", 'mark_car' => "Mercedes-Benz 315 Cdi Sprinter Van"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "Н769041", 'mark_car' => "Mercedes-Benz 315 Cdi Sprinter Van"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "H783869", 'mark_car' => "Toyota Avensis"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "H782230", 'mark_car' => "Toyota Avensis"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "Н764404", 'mark_car' => "Toyota Corolla"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "H777826", 'mark_car' => "Toyota Corolla"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "617LS02", 'mark_car' => "Toyota Hi-Lux"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "C295302", 'mark_car' => "Toyota Hi-Lux"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "С500802", 'mark_car' => "Toyota Hi-Lux"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "C295202", 'mark_car' => "Toyota Hi-Lux"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "С500202", 'mark_car' => "Toyota Hi-Lux"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "С501002", 'mark_car' => "Toyota Hi-Lux"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "С500102", 'mark_car' => "Toyota Hi-Lux"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "C225502", 'mark_car' => "Toyota Hi-Lux"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "С501102", 'mark_car' => "Toyota Hi-Lux"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "Н665902", 'mark_car' => "Toyota Hi-Lux"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "Н666002", 'mark_car' => "Toyota Hi-Lux"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "Н665802", 'mark_car' => "Toyota Hi-Lux"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "Н782705", 'mark_car' => "Toyota Hi-Lux"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "С498702", 'mark_car' => "Toyota Hi-Lux"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "Н782707", 'mark_car' => "Toyota Hi-Lux"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "Н746302", 'mark_car' => "Toyota RAV-4"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "Н746402", 'mark_car' => "Toyota RAV-4"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "Н713471", 'mark_car' => "Toyota RAV-4"],
            ['full_name' => "Служебный автомобиль",'position' => "Инженер", 'gov_number' => "Н712970", 'mark_car' => "Toyota RAV-4"]
        ];

        foreach($arr as $item) {
            $company_id = 20;
            $gov_number = str_replace(" ", "", $item['gov_number']);
            $wcl = WhiteCarList::where(['gov_number' => $gov_number])->first();
            if (!$wcl) {
                DB::beginTransaction();
                try {
                    $data['status'] = 'ok';
                    $data['kpp_name'] = 'kpp1';
                    $data['gov_number'] = $gov_number;
                    $data['full_name'] = (isset($item['full_name']) && $item['full_name'] == '') ? null : trim($item['full_name']);
                    $data['mark_car'] = ($item['mark_car'] == '') ? null : trim($item['mark_car']);
                    $data['position'] = trim($item['position']);
                    $white_car_list = WhiteCarList::create($data);

                    if(!WclCompany::exists($white_car_list->id, $company_id)) {
                        WclCompany::create([
                            'wcl_id' => $white_car_list->id, 'company_id' => $company_id
                        ]);
                    }

                    WhiteCarLog::create([
                        'user_id' => Auth::guard('admin')->id(),
                        'gov_number' => $white_car_list->gov_number, 'status' => 'ok'
                    ]);

                    DB::commit();
                } catch (\Exception $exception) {
                    DB::rollBack();
                    abort(400, "$exception");
                }
            }
        }*/

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
            $data['gov_number'] = $gov_number;

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
