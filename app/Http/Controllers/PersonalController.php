<?php

namespace App\Http\Controllers;

use App\Models\Passage;
use App\Models\User;
use Illuminate\Http\Request;
use Auth;

class PersonalController extends Controller
{
    public function index()
    {
        return view('kpp-personal');
    }

    public function scanningPersonalWithBarcode(Request $request)
    {
        $barcode = trim($request->input('barcode'));
        $user = User::whereUuid($barcode)
            ->with('position', 'company')
            ->first();
        if ($user) {
            // найден пользователь по uuid
            $data = [];
            $data['user_id'] = $user->id;
            $data['operation_type'] = 0;
            $data['kpp_name'] = (!empty(Auth::user()->kpp_name)) ? Auth::user()->kpp_name : null;
            $result = [
                'last_name' => $user->full_name,
                'company' => $user->company->short_ru_name,
                'department' => $user->department->title,
                'position' => $user->position->title,
                'phone' => $user->phone,
                'avatar' => $user->image,
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

            /*$full_name = $passage->user->full_name;
            //$operation_name = ($passage->operation_type == 1) ? 'вошел' : 'вышел';
            $date_time = $passage->created_at;
            $img_path = (empty($user->image)) ? "/img/default-user-image.png" : $user->image;

            $result = "<div class='container'><div class='row'><div class='col-md-6'><div style='margin-top: 30px !important;' class='scan-succes' align='center'><div><img width='200' src=\"$img_path\"/></div>Сотрудник: <span>"
                .$full_name."</span></span></div></div>";

            $result .= "<div class='col-md-6'><div style='margin-top: 30px !important;' class='scan-succes' align='center'>Все впорядке!<br>Можете пропустить!"
                ."<br>Дата: <span>".$date_time."</span></div></div></div>";

            $result .= "</div></div>";
            return response(['data' => $result], 200);
            //return response(json_encode($user), 200);*/
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
