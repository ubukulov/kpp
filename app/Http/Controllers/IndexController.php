<?php

namespace App\Http\Controllers;

use App\Models\BT;
use App\Models\Car;
use App\Models\Company;
use App\Models\Direction;
use App\Models\Driver;
use App\Models\LiftCapacity;
use App\Models\Permit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use File;
use Auth;

class IndexController extends BaseController
{
    public function welcome()
    {
        return view('welcome');
    }

    public function securityKpp()
    {
        $permits = Permit::orderBy('id', 'DESC')->take(20)->get();
        $lift_capacity = LiftCapacity::all();
        $body_type = BT::all();
        $directions = Direction::all();
        return view('kpp', compact('permits', 'lift_capacity', 'body_type', 'directions'));
    }

    public function orderPermitByKpp(Request $request)
    {
        $company = Company::findOrFail($request->input('company_id'));
        $data = $request->except(['path_docs_fac', 'path_docs_back']);
        $data['company'] = $company->short_en_name;
        $data['gov_number'] = mb_strtoupper(trim($data['gov_number']));
        $data['tex_number'] = strtoupper(trim($data['tex_number']));
        $data['ud_number'] = mb_strtoupper(trim($data['ud_number']));
        $data['last_name'] = mb_strtoupper($data['last_name']);
        $data['from_company'] = (isset($data['from_company'])) ? mb_strtoupper($data['from_company']) : null;
        $data['foreign_car'] = (isset($data['foreign_car'])) ? mb_strtoupper($data['foreign_car']) : 0;
        $data['incoming_container_number'] = (isset($data['incoming_container_number'])) ? strtoupper($data['incoming_container_number']) : null;
		$data['date_in'] = date("Y-m-d H:i:s", strtotime($request->input('date_in')));
        $data['kpp_name'] = Auth::user()->kpp_name;
		// Маршруты
		if (isset($data['direction_id']) && $data['direction_id'] != 0 && $data['direction_id'] != 6) {
		    $direction = Direction::findOrFail($data['direction_id']);
		    $data['to_city'] = mb_strtoupper($direction->title);
        } else {
            $data['to_city'] = isset($data['to_city']) ? mb_strtoupper($data['to_city']) : null;
        }

		/*$permit = Permit::where(['tex_number' => $data['tex_number'], 'ud_number' => $data['ud_number'], 'status' => 'printed'])
                    ->whereNull('date_out')
                    ->whereDate('date_in', Carbon::now())
                    ->first();
		if ($permit) {
		    return response('Запрещено. Пропуск уже оформлен c такими данными.', 403);
        }*/

        $permit = Permit::create($data);

        // Если новый водитель, то добавляем в справочник
        if (!Driver::exists($data['ud_number'])) {
            Driver::create([
                'fio' => $data['last_name'], 'phone' => $data['phone'], 'ud_number' => $data['ud_number']
            ]);
        }

        // Отправляем смс к водителяем
        //Driver::send_sms($data['phone']);

        // Если новая машина, то добавляем в справочник
        if (!Car::exists($data['tex_number'])) {
            Car::create([
                'tex_number' => $data['tex_number'], 'gov_number' => $data['gov_number'], 'mark_car' => $data['mark_car'],
                'pr_number' => mb_strtoupper($data['pr_number']), 'lc_id' => $data['lc_id'], 'bt_id' => $data['bt_id'],
                'from_company' => $data['from_company'], 'foreign_car' => $data['foreign_car']
            ]);
        } else {
            $car = Car::where(['tex_number' => $data['tex_number']])->first();
            $car->lc_id = $data['lc_id'];
            $car->bt_id = $data['bt_id'];
            $car->foreign_car = $data['foreign_car'];
            $car->from_company = $data['from_company'];
            $car->save();
        }

        // Подготовка папок для сохранение картинки
        $dir = '/uploads/'. substr(md5(microtime()), mt_rand(0, 30), 2) . '/' . substr(md5(microtime()), mt_rand(0, 30), 2);
        if($request->path_docs_fac && !empty($request->path_docs_fac) || $request->path_docs_back && !empty($request->path_docs_back)) {
            if(!File::isDirectory(public_path(). $dir)){
                File::makeDirectory(public_path(). $dir, 0777, true);
            }
        }

        // Проверка на наличие картинки (лицевая)
        if ($request->path_docs_fac && !empty($request->path_docs_fac)){
            $image = $request->input('path_docs_fac'); // image base64 encoded
            preg_match("/data:image\/(.*?);/",$image,$image_extension); // extract the image extension
            $image = preg_replace('/data:image\/(.*?);base64,/','',$image); // remove the type part
            $image = str_replace(' ', '+', $image);
            $imageName = $permit->id.'_f_'.time() . '.' . $image_extension[1]; //generating unique file name;
            File::put(public_path(). $dir.'/'.$imageName,base64_decode($image));
            $permit->path_docs_fac = $dir.'/'.$imageName;
            $permit->save();
        }

        // Проверка на наличие картинки (обратная)
        if ($request->path_docs_back && !empty($request->path_docs_back)){
            $image2 = $request->input('path_docs_back'); // image base64 encoded
            preg_match("/data:image\/(.*?);/",$image2,$image_extension); // extract the image extension
            $image2 = preg_replace('/data:image\/(.*?);base64,/','',$image2); // remove the type part
            $image2 = str_replace(' ', '+', $image2);
            $imageName2 = $permit->id.'_b_'.time() . '.' . $image_extension[1]; //generating unique file name;
            File::put(public_path(). $dir.'/'.$imageName2,base64_decode($image2));
            $permit->path_docs_back = $dir.'/'.$imageName2;
            $permit->save();
        }

        // Отправка на печать
        $this->start_print($permit->id);

        if(Auth::user()->kpp_name == 'kpp4' && $permit->company_id == 2) {
            if($permit->operation_type > 1 && $permit->lc_id != 1) {
                $beautymail = app()->make(\Snowfire\Beautymail\Beautymail::class);
                $beautymail->send('emails.samsung', [
                    'permit' => $permit
                ], function($message)
                {
                    $message
                        ->from('webcont@dlg.kz')
                        ->to('propuskkpp@dlg.kz')
                        ->subject('Уведомление о прибытии ТС в ILC');
                });
            }
        }

        return response(['data' => 'Пропуск успешно создан']);
    }

    public function getPermits()
    {
        $user = Auth::user();
        $permits = Permit::whereNotNull('date_in')->where(['kpp_name' => $user->kpp_name, 'status' => 'printed'])->orderBy('id', 'DESC')->take(15)->get();
        return json_encode($permits);
    }

    public function getPrevPermitsForToday()
    {
        $permits = Permit::where(['status' => 'awaiting_print'])->where('tex_number', '<>', 'AA123456')->where('is_driver', '>', 0)->whereDate('created_at', Carbon::today())->orderBy('id', 'DESC')->get();
        return json_encode($permits);
    }

    public function getUserInfo($permit_id)
    {
        $permit = Permit::findOrFail($permit_id);
        return json_encode($permit);
    }

    public function getCarInfo($tex_number)
    {
        $tex_number = strtolower(trim($tex_number));
        $car = Car::where(['tex_number' => $tex_number])->first();
        return json_encode($car);
    }

    public function getDriverInfo($ud_number)
    {
        $ud_number = mb_strtolower(trim($ud_number));
        $driver = Driver::where(['ud_number' => $ud_number])->first();
        return json_encode($driver);
    }

    public function getNotCompletedPermitsForWeek()
    {
        $permits = Permit::whereNotNull('date_in')->where('status', 'printed')->where('created_at', '>=', Carbon::now()->subDays(7))->orderBy('id', 'DESC')->get();
        return response()->json($permits);
    }

    public function putToArchive(Request $request)
    {
        $permit_id = (int) $request->input('permit_id');
        $note = $request->input('note');
        $permit = Permit::findOrFail($permit_id);
        if (is_null($permit->date_out)) {
            $permit->status = 'deleted';
            $permit->note = $note;
            $permit->save();
            return response('Пропуск успешно перемещен в архив', 200);
        } else {
            return response('Запрещено. У пропуска дата выезда фиксирован.', 403);
        }
    }

    public function searchPermit(Request $request)
    {
        $search_word = trim($request->input('search'));
        $permit = Permit::where(['is_driver' => 1, 'gov_number' => $search_word])->whereDate('created_at', Carbon::today())->latest('id')->first();

        if(!is_null($permit)){
            return json_encode($permit);
        }

        $permit2 = Permit::find($search_word);
        if(!is_null($permit2)){
            return json_encode($permit2);
        }

        return response(['data' => 'Данные не найдено'], 500);
    }

    public function fixDateOutForCurrentPermit(Request $request)
    {
        $permit_id = (int) $request->input('permit_id');
        $from_company = $request->input('from_company');
        $outgoing_container_number = $request->input('outgoing_container_number');
        $to_city = $request->input('to_city');
        $permit = Permit::find($permit_id);

        if ($request->has('set_date_out_manual') && !empty($request->input('date_out'))) {
            $date_out = date('Y-m-d H:i:s', strtotime($request->input('date_out')));
        } else {
            $date_out = date('Y-m-d H:i:s');
        }

        if($permit && is_null($permit->date_out)){
            if($permit->date_in < $date_out) {
                $permit->date_out = $date_out;
                if (!empty($outgoing_container_number)) {
                    $permit->outgoing_container_number = $outgoing_container_number;
                }

                $permit->from_company = $from_company;
                $permit->to_city = $to_city;
                $permit->save();
                return response(['data' => 'Дата успешно зафиксирован']);
            } else {
                return response(['data' => 'Дата выезда больше чем дата заезда'], 400);
            }

        } else{
            return response(['data' => 'Дата уже зафиксирован'], 500);
        }
    }

    public function getPermitById($permit_id)
    {
        $permit = Permit::findOrFail($permit_id);
        return json_encode($permit);
    }

    public function checkingPermitForPrint($permit_id)
    {
        $permit = Permit::findOrFail($permit_id);
        if (is_null($permit->planned_arrival_date)) {
            return response()->json([
               'data' => [
                   'permit' => $permit,
                   'message' => 'Пропуск можно распечатать',
                   'allowPrint' => true
               ]
            ]);
        } else {
            $current_date = Carbon::now();
            $planned_arrival_date = new Carbon($permit->planned_arrival_date);
            $diff = $current_date->diff($planned_arrival_date);
            if($diff->i <= 30) {
                return response()->json([
                    'data' => [
                        'permit' => $permit,
                        'message' => 'Пропуск можно распечатать',
                        'allowPrint' => true
                    ]
                ]);
            } else {
                $message = "Внимание!!! Пропуск должно распечатывается в указанное время. Дата запланируемго заезда: " . $planned_arrival_date->format('d.m.Y / H:i:s');
                return response()->json([
                    'data' => [
                        'permit' => $permit,
                        'message' => $message,
                        'allowPrint' => false
                    ]
                ], 409);
            }
        }
    }

    // Метод для печати пропуска
    public function start_print($permit_id, $company_id = 0, $foreign_car = 0)
    {
        $user = Auth::user();
        $computer_name = $user->computer_name;
        $printer_name = $user->printer_name;
        $kpp_name = $user->kpp_name;

        $permit = Permit::findOrFail($permit_id);
        $printer = "\\\\".$computer_name.$printer_name;
        var_dump($printer);
        // Open connection to the thermal printer
        $fp = fopen($printer, "w");
        if (!$fp){
//            die('no connection');
            return response([
                'data' => [
                    'message' => 'no connection to printer'
                ]
            ], 500);
        }

        if ($company_id != 0){
            $comp = Company::findOrFail($company_id);
            $permit->company = $comp->short_en_name;
            $permit->company_id = $company_id;
            $permit->kpp_name = $kpp_name;
            $permit->save();
        }

        if ($foreign_car != 0){
            $permit->foreign_car = $foreign_car;
            $permit->save();
        }

        if ($permit->status == 'awaiting_print') {
            $permit->status = 'printed';
            $permit->date_in = date('Y-m-d H:i:s');
            $permit->save();
        }

        $id = $permit->id;
        $date_in = date("d.m.Y H:i", strtotime($permit->date_in));
        $mark_car = $permit->mark_car;
        $gov_number = $permit->gov_number;
        $fio = $permit->last_name;
        $company = $permit->company;
        $phone = $permit->phone;
        $ud_number = $permit->ud_number;
        $tex_number = $permit->tex_number;
        if (strpos($permit->incoming_container_number, ',') !== false) {
            $arr = explode(',', $permit->incoming_container_number);
            $incoming_container_number = $arr[0];
        } else {
            $incoming_container_number = $permit->incoming_container_number;
        }
        $pr_number = (!empty($permit->pr_number)) ? $permit->pr_number : '';
        if($permit->operation_type == 1) {
            $type = 'Погрузка';
        } elseif($permit->operation_type == 2) {
            $type = 'Разгрузка';
        } else {
            $type = 'Другие действие';
        }

        $data = <<<HERE
^XA^LRN^CI0^XZ
^XA^CWZ,E:tt0003m_^FS^XZ
^XA
^MMT
^PW812
^LL0812
^LS0
^FT66,82^AZN,42,42,TT0003M_^FH\^CI17^F8^FDПРОПУСК №$id^FS^CI0
^BY3,3,58^FT499,102^BCN,,N,N
^FD$id^FS
^FT268,115^A0N,27,27,TT0003M_^FH\^CI17^F8^FD$company / $type^FS^CI0
^FT66,164^AZN,28,29,TT0003M_^FH\^CI17^F8^FDВъезд: $date_in^FS^CI0
^FT440,164^AZN,28,29,TT0003M_^FH\^CI17^F8^FDВыезд: ______________^FS^CI0
^FT66,207^A0N,28,29,TT0003M_^FH\^CI17^F8^FDВодитель:^FS^CI0
^FT440,207^A0N,28,29,TT0003M_^FH\^CI17^F8^FDТранспорт:^FS^CI0
^FT66,250^AZN,28,29,TT0003M_^FH\^CI17^F8^FD$fio^FS^CI0
^FT66,288^AZN,28,29,TT0003M_^FH\^CI17^F8^FD$ud_number^FS^CI0
^FT66,331^AZN,28,29,TT0003M_^FH\^CI17^F8^FD$phone^FS^CI0
^FT440,250^AZN,28,29,TT0003M_^FH\^CI17^F8^FD$gov_number ($mark_car)^FS^CI0
^FT440,288^AZN,28,29,TT0003M_^FH\^CI17^F8^FDКузов: $tex_number^FS^CI0
^FT440,331^AZN,28,29,TT0003M_^FH\^CI17^F8^FDПрицеп: $pr_number^FS^CI0
^FT66,373^A@N,28,29,TT0003M_^FH\^CI17^F8^FDВх.конт: $incoming_container_number^FS^CI0
^FT440,373^A@N,28,29,TT0003M_^FH\^CI17^F8^FDИсх.конт:____________^FS^CI0

^PQ1,0,1,Y^XZ
HERE;


        if (!fwrite($fp,$data)){
//            die('writing failed');
            return response(['data' => 'writing failed'], 500);
        }


    }
}
