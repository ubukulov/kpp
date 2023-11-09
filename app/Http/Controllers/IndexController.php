<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Container;
use App\Models\ContainerStock;
use App\Models\Driver;
use App\Models\Permit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use File;
use Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use DPD;
use CKUD;


class IndexController extends BaseController
{
    public function welcome()
    {
//        $user = User::with('company', 'position')->find(1);
//        $data = [
//            'Comment' => $user->position->title,
//            'employeeGroupID' => 'f6e6250b-69f4-4f4a-9c4b-2bc357645d6b',
//            'Number' => $user->id,
//            'KeyNumber' => 54585655,
//            'ResidentialAddress' => $user->company->full_company_name,
//            'photo_http' => 'https://kpp.dlg.kz:8900/users_photos/d3/c8/1400_l_1682571448.jpeg',
//        ];
//
//        $arr = explode(" ", $user->full_name);
//        $LastName = (array_key_exists(0, $arr)) ? $arr[0] : '';
//        $FirstName = (array_key_exists(1, $arr)) ? $arr[1] : '';
//        $SecondName = (array_key_exists(2, $arr)) ? $arr[2] : '';
//        $data['LastName'] = $LastName;
//        $data['FirstName'] = $FirstName;
//        $data['SecondName'] = $SecondName;
        //dd(json_encode($data, JSON_UNESCAPED_SLASHES));
        //dd(CKUD::addEmployee($data));
        //dd(CKUD::getEmployees());
        return view('welcome');
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
            if(Carbon::create($permit->date_in) < Carbon::create($date_out)) {
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

    public function form4()
    {
        return view('form4');
    }

    public function form4Create()
    {
        return view('form4Create');
    }

    public function form4Show($id)
    {
        return view('form4Show', compact('id'));
    }

    public function scanQR()
    {
        return view('scan-qr');
    }

    public function avigilon(Request $request)
    {
        $f = fopen(public_path() . '/avigilon.txt', 'a');
        fwrite($f, $request);
    }

    public function checkingContainerForApplication(Request $request)
    {
        $containers = $request->input('containers');
        $operation_type = $request->input('operation_type');
        $arr = explode(',', $containers);
        if(count($arr) == 1) {
            $container = Container::whereNumber($arr[0])->first();
            if($container) {
                if($operation_type == 1) {
                    $container_stock = ContainerStock::where(['container_id' => $container->id, 'status' => 'in_order'])->first();
                    if($container_stock) {
                        return response('На контейнер есть заявка на выдачу');
                    } else {
                        return response('Не найден заявка на выдачу контейнера', 403);
                    }
                }

                if($operation_type == 2) {
                    $container_stock = ContainerStock::where(['container_id' => $container->id, 'status' => 'incoming'])->first();
                    if($container_stock) {
                        return response('На контейнер есть заявка на прием');
                    } else {
                        return response('Не найден заявка на прием контейнера', 403);
                    }
                }



                if ($operation_type == 3) {
                    return response('Не нужно проверять');
                }
            } else {
                return response('Контейнер не найдено', 403);
            }
        } else {
            foreach ($arr as $item) {
                $this->checkContainer($item, $operation_type);
            }
        }
    }

    public function checkContainer($container_number, $operation_type)
    {

    }
}
