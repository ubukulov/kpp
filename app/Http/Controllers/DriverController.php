<?php

namespace App\Http\Controllers;

use App\BodyType;
use App\Car;
use App\CategoryTC;
use App\Driver;
use App\Permit;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        $categories_tc = CategoryTC::all();
        $body_types = BodyType::all();
        return view('driver', compact('categories_tc', 'body_types'));
    }

    public function check_driver(Request $request)
    {
        $tex_number = $request->input('tex_number');
        $ud_number = $request->input('ud_number');
        $tex_number = strtolower(trim($tex_number));
        $info = Permit::where(['tex_number' => $tex_number, 'ud_number' => $ud_number])->latest('id')->first();
        if($info && !is_null($info)) {
            return response($info);
        } else {
            return response(['data' => 'Водитель не найдено'], 404);
        }
    }

    public function orderPermitByDriver(Request $request)
    {
        $data = $request->except('wantToOrder');
        $permit = Permit::where(['tex_number' => $data['tex_number'], 'ud_number' => $data['ud_number']])->latest('id')->first();
        if($permit && !is_null($permit)) {
            $data['mark_car'] = $permit->mark_car;
            $data['gov_number'] = $permit->gov_number;
            $data['pr_number'] = $permit->pr_number;
            $data['last_name'] = $permit->last_name;
            $data['phone'] = $permit->phone;
            $data['is_driver'] = 1;
            $data['status'] = 'awaiting_print';
            $new_permit = Permit::create($data);

            // По номеру тех паспорта получаем из справочника данные и сохраняем туда
            $car = Car::where(['tex_number' => $data['tex_number']])->first();
            $car->cat_tc_id = $data['cat_tc_id'];
            $car->body_type_id = $data['body_type_id'];
            $car->save();

            if($request->input('wantToOrder') == 'true') {
                $driver = Driver::where(['ud_number' => mb_strtoupper(trim($data['ud_number']))])->first();
                if(!is_null($driver)) {
                    $driver->want_to_order = 1;
                    $driver->save();
                    $this->sendMail($new_permit);
                }
            }
            return json_encode($new_permit);
        } else {
            return response(['data' => 'При формирование пропуска произошло ошибка'], 500);
        }
    }

    public function sendMail($permit)
    {
        $to      = 'Rustem.Ibraimov@htl.kz';
        $subject = 'Водитель хочеть заказ!!!';
        $fio = $permit->last_name;
        $phone = $permit->phone;
        $gov_number = $permit->gov_number;
        $ud_number = $permit->ud_number;
        $mark_car = $permit->mark_car;
        $tex_number = $permit->tex_number;
        $company = $permit->company;
        $pr_number = $permit->pr_number;
        if($permit->operation_type == 1) {
            $type_operation = "Погрузка";
        } elseif ($permit->operation_type == 2) {
            $type_operation = "Разгрузка";
        } else {
            $type_operation = "Другие действие";
        }

        if($permit->cat_tc_id != 0) {
            $cat_tc = CategoryTC::findOrFail($permit->cat_tc_id);
            $tc = $cat_tc->title;
        } else {
            $tc = 'Не указал';
        }

        if($permit->body_type_id != 0) {
            $body_type = BodyType::findOrFail($permit->body_type_id);
            $bt = $body_type->title;
        } else {
            $bt = 'Не указал' ;
        }

        $message = '
        <html>
        <head>
          <title>Водител выразил желания работать!</title>
        </head>
        <body>
          <p>Водител выразил желания работать!</p>
          <table>
            <tr>
              <th>ФИО</th><th>Телефон</th><th>Номер машины</th><th>Номер вод.удос</th>
              <th>Марка авто</th><th>Тех.паспорт</th><th>Компания</th><th>№прицепа</th>
              <th>Тип опера.</th><th>Грузоподъемность ТС</th><th>Тип кузова</th>
            </tr>
        ';
        $message .= "
            <tr>
              <td>$fio</td><td>$phone</td><td>$gov_number</td><td>$ud_number</td>
              <td>$mark_car</td><td>$tex_number</td><td>$company</td><td>$pr_number</td>
              <td>$type_operation</td><td>$tc</td><td>$bt</td>
            </tr>
        ";
        $message .= '</table></body></html>';

        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= 'To: Rustem Ibraimov <rustem.ibraimov@example.com>';
        $headers .= 'From: KPP <info@htl.kz>' . "\r\n";
        $headers .= 'Cc: vladimir.yemelyanov@htl.kz, kairat.ubukulov@htl.kz, bekzhan.salibayev@ailp.kz';

        mail($to, $subject, $message, $headers);
    }
}
