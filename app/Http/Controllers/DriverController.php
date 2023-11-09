<?php

namespace App\Http\Controllers;

use App\Models\BT;
use App\Models\Car;
use App\Models\Direction;
use App\Models\Driver;
use App\Models\LiftCapacity;
use App\Models\Permit;
use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        $categories_tc = LiftCapacity::all();
        $body_types = BT::all();
        $directions = Direction::all();
        return view('driver', compact('categories_tc', 'body_types', 'directions'));
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
            $data['from_company'] = $permit->from_company;
            $data['is_driver'] = 1;
            //$data['from_company'] = (isset($data['from_company'])) ? mb_strtoupper($data['from_company']) : null;

            // Маршруты
            if (isset($data['direction_id']) && $data['direction_id'] != 0 && $data['direction_id'] != 6) {
                $direction = Direction::findOrFail($data['direction_id']);
                $data['to_city'] = mb_strtoupper($direction->title);
            } else {
                $data['to_city'] = isset($data['to_city']) ? mb_strtoupper($data['to_city']) : null;
            }

            $data['status'] = 'awaiting_print';
            $new_permit = Permit::create($data);

            // По номеру тех паспорта получаем из справочника данные и сохраняем туда
            $car = Car::where(['tex_number' => $data['tex_number']])->first();
            if(isset($data['lc_id'])) {
				$car->lc_id = $data['lc_id'];
			}
			if(isset($data['bt_id'])) {
				$car->bt_id = $data['bt_id'];
			}
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
            return response(['data' => 'Не найдено! Попробуйте вести правильные данные'], 404);
        }
    }

    public function sendMail($permit)
    {
        $to      = 'nadamu.drivers@dlg.kz';
        $subject = 'Водитель хочет получить заказы!';
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

        if($permit->lc_id != 0) {
            $cat_tc = LiftCapacity::findOrFail($permit->lc_id);
            $tc = $cat_tc->title;
        } else {
            $tc = 'Не указал';
        }

        if($permit->bt_id != 0) {
            $body_type = BT::findOrFail($permit->bt_id);
            $bt = $body_type->title;
        } else {
            $bt = 'Не указал' ;
        }

        $message = '
        <html>
        <head>
          <title>Водитель выразил желание получать заказы!</title>
        </head>
        <body>
          <p>Водитель выразил желание получать заказы!</p>
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
        $headers .= 'To: Nadamu Drivers <nadamu.drivers@dlg.kz>';
        $headers .= 'From: KPP <nadamu@dlg.kz>' . "\r\n";
        //$headers .= 'Cc: vladimir.yemelyanov@htl.kz, kairat.ubukulov@htl.kz, bekzhan.salibayev@ailp.kz, Nurken.Ramankul@htl.kz';

        mail($to, $subject, $message, $headers);
    }
}
