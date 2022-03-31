<?php

namespace App\Http\Controllers\Admin;

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
        /*$arr = [
            ['full_name' => "Бедельхан Адилет", 'position' => "Кладовщик", 'gov_number' => "A 660 DUP ", 'mark_car' => "ZAZ Chance"],
            ['full_name' => "Сафонова Зоя Федоровна ", 'position' => "Кладовщик", 'gov_number' => "580 YHA 05", 'mark_car' => "Mitsubishi"],
            ['full_name' => "Гордеева Татьяна ", 'position' => "Кладовщик", 'gov_number' => "411 ARU 05", 'mark_car' => "Daewoo Matiz "],
            ['full_name' => "Усенов Олжас Мухтарович ", 'position' => "ОВК  ", 'gov_number' => "403 AHG 05", 'mark_car' => "Subaru legacy"],
            ['full_name' => "Иванченко Анатолии Юрьевич", 'position' => "Кладовщик", 'gov_number' => "214 AEI 05", 'mark_car' => "ВАЗ 2114"],
            ['full_name' => "Кудайберген Толагай", 'position' => "Кладовщик", 'gov_number' => "650 ABQ 05", 'mark_car' => "Honda Odyssey "],
            ['full_name' => "Алдаберген Нуржан Нурлыбайулы ", 'position' => "Кладовщик", 'gov_number' => "427 ACH 05", 'mark_car' => "Nissan Maxima "],
            ['full_name' => "Иванченко Валерий Юрьевич", 'position' => "Кладовщик", 'gov_number' => "441 ADI 05", 'mark_car' => "ВАЗ 2114"],
            ['full_name' => "Кадыр Райымбек Айдынулы", 'position' => "Кладовщик", 'gov_number' => "297 IOZ 05", 'mark_car' => "Honda Odyssey"],
            ['full_name' => "Зуев Евгений Владимирович ", 'position' => "Зам нач безопасности", 'gov_number' => "361 HTA 02", 'mark_car' => "Toyota Land Cruiser"],
            ['full_name' => "Даутжанов Елдос Жазыкбекович", 'position' => "Водитель", 'gov_number' => "924 AAN 02", 'mark_car' => "VanHool"],
            ['full_name' => "Турдаханов Дидар Амырханович", 'position' => "Клининговая компания", 'gov_number' => "815 ACY 05", 'mark_car' => "Lada"],
            ['full_name' => "Кейм Евгений Дмитриевич ", 'position' => "Клининговая компания", 'gov_number' => "399 JPZ 05", 'mark_car' => "Volkswagen Jetta "],
            ['full_name' => "Жданов Илья Сергеевич", 'position' => "Менеджер смены", 'gov_number' => "690 QAZ 05", 'mark_car' => "Hyundai Accent  "],
            ['full_name' => "Бадалов Мехман Мусаевич", 'position' => "Менеджер по логистике  ", 'gov_number' => "464 YMY 05", 'mark_car' => "Hyundai Accent  "],
            ['full_name' => "Серебряков Владимир Владимирович", 'position' => "Менеджер смены", 'gov_number' => "540 OQO 02", 'mark_car' => "Skoda "],
            ['full_name' => "Абдулин Рамиз Пархатович", 'position' => "Кладовщик ", 'gov_number' => "752 HTB 05", 'mark_car' => "BMW"],
            ['full_name' => "Федера Владимир Григорьевич ", 'position' => "Кладовщик ", 'gov_number' => "968 ASA 08", 'mark_car' => "Volkswagen Passat "],
            ['full_name' => "Каверина Татьяна    Юрьевна", 'position' => "Кладовщик ", 'gov_number' => "203 ZVU 05", 'mark_car' => "Nissan Sunny "],
            ['full_name' => "Василяди Андрей Валерьевич ", 'position' => "Кладовщик ", 'gov_number' => "669 BFA 05", 'mark_car' => "Hyundai Getz"],
            ['full_name' => "Прокопова Алена Александровна", 'position' => "Кладовщик ", 'gov_number' => "090 CDA 05", 'mark_car' => "BMW X5"],
            ['full_name' => "Жамуров Нурболат Бейсенбайулы", 'position' => "Кладовщик ", 'gov_number' => "989 BSZ 05", 'mark_car' => "Nissan Cefiro "],
            ['full_name' => "Нагишбаев Темирлан Еркимбекулы", 'position' => "Оператор ", 'gov_number' => "109 ACK 05", 'mark_car' => "Toyota Mark 2"],
            ['full_name' => "Шиве Анна Сергеевна ", 'position' => "Оператор ", 'gov_number' => "685 AAO 05", 'mark_car' => "Nissan Cefiro"],
        ];

        foreach($arr as $item) {
            $company_id = 83;
            $gov_number = str_replace(" ", "", $item['gov_number']);
            $wcl = WhiteCarList::where(['gov_number' => $gov_number])->first();
            if (!$wcl) {
                DB::beginTransaction();
                try {
                    $data['status'] = 'ok';
                    $data['kpp_name'] = 'kpp1';
                    $data['gov_number'] = $gov_number;
                    $data['full_name'] = $item['full_name'];
                    $data['mark_car'] = $item['mark_car'];
                    $data['position'] = $item['position'];
                    $white_car_list = WhiteCarList::create($data);

                    if(!WclCompany::exists($white_car_list->id, $company_id)) {
                        WclCompany::create([
                            'wcl_id' => $white_car_list->id, 'company_id' => $company_id
                        ]);
                    }

                    WhiteCarLog::create([
                        'user_id' => Auth::guard('admin')->id(),
                        'gov_number' => $white_car_list->gov_number, 'status' => $white_car_list->status
                    ]);

                    DB::commit();
                } catch (\Exception $exception) {
                    DB::rollBack();
                    abort(500, "$exception");
                }
            }
        }*/

        $white_car_lists = WhiteCarList::orderBy('white_car_lists.id', 'DESC')
            ->selectRaw('wcl_companies.id,wcl_companies.wcl_id,white_car_lists.gov_number, companies.short_ru_name, wcl_companies.status, wcl_companies.created_at')
            ->join('wcl_companies', 'wcl_companies.wcl_id', '=', 'white_car_lists.id')
            ->join('companies', 'companies.id', '=', 'wcl_companies.company_id')
            ->paginate();

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
}
