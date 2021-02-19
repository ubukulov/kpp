<?php

namespace App\Http\Controllers;

use App\Models\Passage;
use App\Models\User;
use Illuminate\Http\Request;

class PersonalController extends Controller
{
    public function index()
    {
        return view('personal');
    }

    public function scanningPersonalWithBarcode(Request $request)
    {
        $barcode = $request->input('barcode');
        $user = User::whereUuid($barcode)
            ->with('position', 'company')
            ->first();
        if ($user) {
            // найден пользователь по uuid
            return response(json_encode($user), 200);
        }
    }

    public function fixDateTimeForCurrentUser(Request $request)
    {
        $passage = Passage::create($request->all());
        $full_name = $passage->user->full_name;
        $operation_name = ($passage->operation_type == 1) ? 'вошел' : 'вышел';
        $date_time = $passage->created_at;

        $result = '<div class="scan-succes" align="center">Все впорядке!<br>Можете пропустить!<br>Сотрудник: <span>'
            .$full_name.'</span><br>Тип операции: <span>'
            .$operation_name.'</span><br>Дата: <span>'
            .$date_time.'</span></div>';
        return response(['data' => $result], 200);
    }
}
