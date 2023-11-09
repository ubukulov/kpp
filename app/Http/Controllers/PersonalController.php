<?php

namespace App\Http\Controllers;

use App\Models\BT;
use App\Models\Company;
use App\Models\Direction;
use App\Models\LiftCapacity;
use App\Models\Passage;
use App\Models\Permit;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;

class PersonalController extends BaseController
{
    public function index()
    {
        $permits = Permit::orderBy('id', 'DESC')->take(20)->get();
        $lift_capacity = LiftCapacity::all();
        $body_type = BT::all();
        $companies = Company::where(['type_company' => 'resident'])->orderBy('short_en_name')->get();
        return view('kpp-personal', compact('permits', 'lift_capacity', 'body_type', 'companies'));
    }

    public function scanningPersonalWithBarcode(Request $request)
    {
        $barcode = trim($request->input('barcode'));
        if($this->isKazakh($barcode)) {
            $html = "<div class='div_block warning_div' style='padding-top: 18px;font-size: 25px;'>
            ПОЖАЛУЙСТА ПОМЕНЯЙТЕ РАСКЛАДКУ НА АНГЛИЙСКУЮ ИЛИ НА РУССКУЮ
            </div>";
            return response([
                'data' => [
                    'html' => $html
                ]
            ], 404);
        }

        if($this->isRussian($barcode)) {
            $barcode = $this->switch_en($barcode);
        }

        $user = User::whereUuid($barcode)
            ->with('position', 'company')
            ->first();
        if ($user) {
            // найден пользователь по uuid
            $data = [];
            $data['user_id'] = $user->id;
            $data['operation_type'] = 0;
            $data['kpp_name'] = (!empty(Auth::user()->kpp_name)) ? Auth::user()->kpp_name : 'kpp1';
            $company = ($user->company->short_ru_name) ? $user->company->short_ru_name : '';
            $department = (isset($user->department->title)) ? $user->department->title : '';
            $position = ($user->position->title) ? $user->position->title : '';
            $image = (file_exists(public_path($user->image))) ? $user->image : '/img/default-user-image.png';
            $result = [
                'last_name' => $user->full_name,
                'company' => $company,
                'department' => $department,
                'position' => $position,
                'phone' => $user->phone,
                'avatar' => $image,
                'user' => $user,
                'working_status' => $user->getWorkingStatus()
            ];

            if($user->hasWorkPermission()) {
                $passage = Passage::create($data);
                //$user = $passage->user;
                $result['status'] = 200;
                $html = "<div class='div_block success_div'>
            ДОСТУП РАЗРЕШЕН
            </div>";
                $result['html'] = $html;
                return response()->json($result);
            } else {
                $result['status'] = 403;
                $html = "<div class='div_block failure_div'>
            ДОСТУП ЗАПРЕЩЕН
            </div>";
                $result['html'] = $html;
                return response(['data' => $result], 403);
            }
        } else {
            $html = "<div class='div_block warning_div'>
            НЕ НАЙДЕНО
            </div>";
            return response([
                'data' => [
                    'html' => $html
                ]
            ], 404);
		}
    }

    public function fixDateTimeForCurrentUser(Request $request)
    {
        $data = $request->all();
        $data['kpp_name'] = (!empty(Auth::user()->kpp_name)) ? Auth::user()->kpp_name : null;
        $passage = Passage::create($data);
        $user = $passage->user;
        $result = [
            'last_name' => $user->full_name,
            'company' => $user->company->short_ru_name,
            'department' => $user->department->title,
            'position' => $user->position->title,
            'phone' => $user->phone,
            'user' => $user
        ];
        return response()->json($result);
        /*$full_name = $passage->user->full_name;
        $operation_name = ($passage->operation_type == 1) ? 'вошел' : 'вышел';
        $date_time = $passage->created_at;

        $result = '<div class="scan-succes" align="center">Все впорядке!<br>Можете пропустить!<br>Сотрудник: <span>'
            .$full_name.'</span><br>Тип операции: <span>'
            .$operation_name.'</span><br>Дата: <span>'
            .$date_time.'</span></div>';
        return response(['data' => $result], 200);*/
    }
}
