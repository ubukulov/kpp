<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
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
            0=> [20=> "096XKA02"],
			1=> [20=> "978BNZ05"],
			2=> [20=> "736ZVZ05"],
			3=> [20=> "049XDZ05"],
			4=> [20=> "410ACB05"],
			5=> [20=> "258DLB05"],
			6=> [20=> "792ADG05"],
			7=> [20=> "347XRA02"],
			8=> [20=> "391SNA02"],
			9=> [20=> "233zsz05"],
			10=> [20=> "H787000"],
			11=> [20=> "Н769039"],
			12=> [20=> "Н769040"],
			13=> [20=> "Н769041"],
			14=> [20=> "H713383"],
			15=> [20=> "H783869"],
			16=> [20=> "H782230"],
			17=> [20=> "Н765407"],
			18=> [20=> "Н768469"],
			19=> [20=> "Н765257"],
			20=> [20=> "Н765255"],
			21=> [20=> "H777822"],
			22=> [20=> "H777823"],
			23=> [20=> "H777824"],
			24=> [20=> "Н768318"],
			25=> [20=> "H344402"],
			26=> [20=> "Н765406"],
			27=> [20=> "Н764404"],
			28=> [20=> "H777826"],
			29=> [20=> "С499902"],
			30=> [20=> "C295302"],
			31=> [20=> "С500802"],
			32=> [20=> "C295202"],
			33=> [20=> "С500202"],
			34=> [20=> "С501002"],
			35=> [20=> "С500102"],
			36=> [20=> "C225502"],
			37=> [20=> "С501102"],
			38=> [20=> "Н665902"],
			39=> [20=> "Н666002"],
			40=> [20=> "Н665802"],
			41=> [20=> "Н782705"],
			42=> [20=> "С498702"],
			43=> [20=> "Н782707"],
			44=> [20=> "Н762006"],
			45=> [20=> "Н808300"],
			46=> [20=> "Н808400"],
			47=> [20=> "C125302"],
			48=> [20=> "H766551"],
			49=> [20=> "Н766550"],
			50=> [20=> "Н766568"],
			51=> [20=> "Н746302"],
			52=> [20=> "Н746402"],
			53=> [20=> "Н763778"],
			54=> [20=> "Н763779"],
			55=> [20=> "Н713471"],
			56=> [20=> "Н712970"],
			57=> [20=> "С572002"],
			58=> [20=> "С571402"],
			59=> [20=> "С567902"],
			60=> [20=> "С079202"],
			61=> [20=> "H713459"],
			62=> [20=> "H713275"],
			63=> [20=> "H007902"],
			64=> [20=> "542ACB05"],
			65=> [20=> "059SZA02"],
			66=> [20=> "664UEY05"],
			67=> [20=> "691GHZ05"],
			68=> [20=> "770AZA05"]
        ];

        foreach($arr as $item) {
            $company_id = array_keys($item)[0];
            $gov_number = str_replace(" ", "", array_values($item)[0]);
            $wcl = WhiteCarList::where(['gov_number' => $gov_number])->first();
            if (!$wcl) {
                //dd($company_id, $gov_number);
                DB::beginTransaction();
                try {
                    $data['status'] = 'ok';
                    $data['kpp_name'] = 'kpp2';
                    $data['company_id'] = $company_id;
                    $data['gov_number'] = $gov_number;
                    $white_car_list = WhiteCarList::create($data);

                    WhiteCarLog::create([
                        'wcl_id' => $white_car_list->id, 'user_id' => Auth::guard('admin')->id(), 'company_id' => $white_car_list->company_id,
                        'gov_number' => $white_car_list->gov_number, 'status' => $white_car_list->status
                    ]);

                    DB::commit();
                } catch (\Exception $exception) {
                    DB::rollBack();
                    abort(500, "$exception");
                }
            }
        }*/

        $white_car_lists = WhiteCarList::orderBy('id', 'DESC')->paginate(50);
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

        $request->validate([
            'gov_number' => 'required|unique:white_car_lists|max:20',
        ]);

        // Все данные прошло валидацию
        DB::beginTransaction();
        try {
            $data['status'] = 'ok';
            $white_car_list = WhiteCarList::create($data);

            WhiteCarLog::create([
                'wcl_id' => $white_car_list->id, 'user_id' => Auth::guard('admin')->id(), 'company_id' => $white_car_list->company_id,
                'gov_number' => $white_car_list->gov_number, 'status' => $white_car_list->status
            ]);

            DB::commit();
            return redirect()->route('admin.white-car-list.index');
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
        $white_car_list = WhiteCarList::findOrFail($id);
        $companies = Company::orderBy('short_en_name')->get();
        return view('admin.white_car.edit', compact('white_car_list', 'companies'));
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
            $white_car_list = WhiteCarList::findOrFail($id);
            $white_car_list->update($request->all());

            WhiteCarLog::create([
                'wcl_id' => $white_car_list->id, 'user_id' => Auth::guard('admin')->id(), 'company_id' => $white_car_list->company_id,
                'gov_number' => $white_car_list->gov_number, 'status' => $white_car_list->status
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
