<?php

namespace App\Http\Controllers\Cabinet;

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
        $white_car_lists = WhiteCarList::orderBy('id', 'DESC')->where(['status' => 'ok'])->get();
        return view('cabinet.white_car.index', compact('white_car_lists'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companies = Company::orderBy('short_en_name')->get();
        return view('cabinet.white_car.create', compact('companies'));
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
                'wcl_id' => $white_car_list->id, 'user_id' => Auth::id(), 'company_id' => $white_car_list->company_id,
                'gov_number' => $white_car_list->gov_number, 'status' => $white_car_list->status
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
        $white_car_list = WhiteCarList::findOrFail($id);
        $companies = Company::orderBy('short_en_name')->get();
        return view('cabinet.white_car.edit', compact('white_car_list', 'companies'));
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
                'wcl_id' => $white_car_list->id, 'user_id' => Auth::id(), 'company_id' => $white_car_list->company_id,
                'gov_number' => $white_car_list->gov_number, 'status' => $white_car_list->status
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
        //WhiteCarList::destroy($id);
        //return redirect()->route('admin.white-car-list.index');
    }
}
