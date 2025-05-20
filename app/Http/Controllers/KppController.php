<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use App\Repositories\Interfaces\IBTRepository;
use App\Repositories\Interfaces\ICarRepository;
use App\Repositories\Interfaces\ICompanyRepository;
use App\Repositories\Interfaces\IDirectionRepository;
use App\Repositories\Interfaces\IDriverRepository;
use App\Repositories\Interfaces\ILiftCapacityRepository;
use App\Repositories\Interfaces\IPermitRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Auth;
use File;

class KppController extends Controller
{
    private $permitRepository;
    private $companyRepository;
    private $liftCapacityRepository;
    private $btRepository;
    private $directionRepository;
    private $carRepository;
    private $driverRepository;

    public function __construct
    (
        IPermitRepository $permitRepository,
        ICompanyRepository $companyRepository,
        ILiftCapacityRepository $liftCapacityRepository,
        IBTRepository $btRepository,
        IDirectionRepository $directionRepository,
        ICarRepository $carRepository,
        IDriverRepository $driverRepository
    )
    {
        $this->permitRepository = $permitRepository;
        $this->companyRepository = $companyRepository;
        $this->liftCapacityRepository = $liftCapacityRepository;
        $this->btRepository = $btRepository;
        $this->directionRepository = $directionRepository;
        $this->carRepository = $carRepository;
        $this->driverRepository = $driverRepository;
    }

    public function index()
    {
        $permits = $this->permitRepository->getLastPermits();
        $lift_capacity = $this->liftCapacityRepository->all();
        $body_type = $this->btRepository->all();
        $directions = $this->directionRepository->all();
        $companies = $this->companyRepository->getCompaniesOrderBy('short_en_name');
        return view('kpp.index', compact('permits', 'lift_capacity', 'body_type', 'directions', 'companies'));
    }

    public function orderPermitByKpp(Request $request)
    {
        $company = $this->companyRepository->getById($request->input('company_id'));
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
            $direction = $this->directionRepository->getById($data['direction_id']);
            $data['to_city'] = mb_strtoupper($direction->title);
        } else {
            $data['to_city'] = isset($data['to_city']) ? mb_strtoupper($data['to_city']) : null;
        }

        // По просьбе Данияра Балабековича отключили 19.03.2025
        /*$permit = Permit::where(['tex_number' => $data['tex_number'], 'ud_number' => $data['ud_number'], 'status' => 'printed'])
                    ->whereNull('date_out')
                    ->whereDate('date_in', Carbon::now())
                    ->first();
        if ($permit) {
            return response('Запрещено. Пропуск уже оформлен c такими данными.', 403);
        }*/

        $data['status'] = 'exit_permitted';
        $permit = $this->permitRepository->create($data);
        if(!$permit) {
            abort(500, "Произошло ошибка при создание пропуска.");
        }

        // Если новый водитель, то добавляем в справочник
        if (!$this->driverRepository->exists($data['ud_number'])) {
            $this->driverRepository->create([
                'fio' => $data['last_name'], 'phone' => $data['phone'], 'ud_number' => $data['ud_number']
            ]);
        }

        // Отправляем смс к водителяем
        //Driver::send_sms($data['phone']);

        // Если новая машина, то добавляем в справочник
        if (!$this->carRepository->exists($data['tex_number'])) {
            $this->carRepository->create([
                'tex_number' => $data['tex_number'], 'gov_number' => $data['gov_number'], 'mark_car' => $data['mark_car'],
                'pr_number' => mb_strtoupper($data['pr_number']), 'lc_id' => $data['lc_id'], 'bt_id' => $data['bt_id'],
                'from_company' => $data['from_company'], 'foreign_car' => $data['foreign_car']
            ]);
        } else {
            $car = $this->carRepository->getByWhereFirst(['tex_number' => $data['tex_number']]);

            if(!$car) {
                abort(404);
            }

            $this->carRepository->update($car->id, [
                'lc_id' => $data['lc_id'], 'bt_id' => $data['bt_id'], 'foreign_car' => $data['foreign_car'],
                'from_company' => $data['from_company']
            ]);
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
                        ->to(['propuskkpp@dlg.kz', 'b.gulsim@partner.samsung.com', 'r.bekbauva@partner.samsung.com', 'Zhotanova.Dinara@htl.kz'])
                        ->subject('Уведомление о прибытии ТС в ILC');
                });
            }
        }

        return response(['data' => 'Пропуск успешно создан']);
    }

    // Метод для печати пропуска
    public function start_print($permit_id, $company_id = 0, $foreign_car = 0)
    {
        $user = Auth::user();
        $computer_name = $user->computer_name;
        $printer_name = $user->printer_name;
        $kpp_name = $user->kpp_name;

        $permit = $this->permitRepository->getById($permit_id);
        $printer = "\\\\".$computer_name.$printer_name;

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
            $comp = $this->companyRepository->getById($company_id);
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
^FT26,52^AZN,42,42,TT0003M_^FH\^CI17^F8^FDПРОПУСК №$id^FS^CI0
^BY3,3,58^FT459,72^BCN,,N,N
^FD$id^FS
^FT238,120^AZN,27,27,TT0003M_^FH\^CI17^F8^FD$company / $type^FS^CI0
^FT46,164^AZN,28,29,TT0003M_^FH\^CI17^F8^FDВъезд: $date_in^FS^CI0
^FT440,164^AZN,28,29,TT0003M_^FH\^CI17^F8^FDВыезд: ______________^FS^CI0
^FT46,207^AZN,28,29,TT0003M_^FH\^CI17^F8^FDВодитель:^FS^CI0
^FT440,207^AZN,28,29,TT0003M_^FH\^CI17^F8^FDТранспорт:^FS^CI0
^FT46,250^AZN,28,29,TT0003M_^FH\^CI17^F8^FD$fio^FS^CI0
^FT46,288^AZN,28,29,TT0003M_^FH\^CI17^F8^FD$ud_number^FS^CI0
^FT46,331^AZN,28,29,TT0003M_^FH\^CI17^F8^FD$phone^FS^CI0
^FT440,250^AZN,28,29,TT0003M_^FH\^CI17^F8^FD$gov_number ($mark_car)^FS^CI0
^FT440,288^AZN,28,29,TT0003M_^FH\^CI17^F8^FDКузов: $tex_number^FS^CI0
^FT440,331^AZN,28,29,TT0003M_^FH\^CI17^F8^FDПрицеп: $pr_number^FS^CI0
^FT46,373^A@N,28,29,TT0003M_^FH\^CI17^F8^FDВх.конт: $incoming_container_number^FS^CI0
^FT440,373^A@N,28,29,TT0003M_^FH\^CI17^F8^FDИсх.конт:____________^FS^CI0

^PQ1,0,1,Y^XZ
HERE;


        if (!fwrite($fp,$data)){
//            die('writing failed');
            return response(['data' => 'writing failed'], 500);
        }


    }

    /**
     * @param Request $request
     * @return JsonResponse
     * 1. Среди сегодняшных пропусков при заезде
     */
    public function accessToEntrance(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'plateNumber' => 'required',
                'entryDate' => 'required'
            ]);

            $data = $request->all();

            $permit = Permit::where('gov_number', $data['plateNumber'])
                //->whereNotNull('date_in')
                ->whereDate('created_at', Carbon::today())
                ->whereNull('date_out')
                ->latest('id')
                ->limit(1)
                ->first();
            if($permit && is_null($permit->date_out)) {
                $permit->date_in = $data['entryDate'];
                $permit->status = 'exit_permitted';
                $permit->save();
                return response()->json([
                    'gov_number' => $data['plateNumber'],
                    'allowed' => true,
                    'message' => 'Въезд разрешён'
                ], 200);
            }

            return response()->json([
                'gov_number' => $data['plateNumber'],
                'allowed' => false,
                'message' => 'Въезд запрещен'
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * 1. за последние 7 дней при выезде (18.03.2025)
     */
    public function accessToExit(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'plateNumber' => 'required',
                'entryDate' => 'required',
            ]);

            $data = $request->all();

            $permit = Permit::where('gov_number', $data['plateNumber'])
                ->whereBetween('created_at', [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->endOfDay()]) // Последние 7 дней
                ->whereNotNull('date_in')
                ->whereNull('date_out')
                ->where('status', '=', 'exit_permitted')
                ->latest('id')
                ->limit(1)
                ->first();
            if($permit) {

                return response()->json([
                    'gov_number' => $data['plateNumber'],
                    'allowed' => true,
                    'message' => 'Выезд разрешён'
                ], 200);
            }

            return response()->json([
                'gov_number' => $data['plateNumber'],
                'allowed' => false,
                'message' => 'Выезд запрещен'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 500);
        }
    }

    public function completedToExit(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'plateNumber' => 'required',
                'exitDate' => 'required',
            ]);

            $data = $request->all();

            $permit = Permit::where('gov_number', $data['plateNumber'])
                ->whereBetween('created_at', [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->endOfDay()]) // Последние 7 дней
                ->whereNotNull('date_in')
                ->whereNull('date_out')
                ->where('status', '=', 'exit_permitted')
                ->latest('id')
                ->limit(1)
                ->first();
            if($permit) {
                $permit->date_out = $data['exitDate'];
                $permit->status = 'completed';
                $permit->save();
                return response()->json([
                    'gov_number' => $data['plateNumber'],
                    'allowed' => true,
                    'message' => 'Выезд завершен'
                ], 200);
            }

            return response()->json([
                'gov_number' => $data['plateNumber'],
                'allowed' => false,
                'message' => 'Выезд не завершен'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json($e->errors(), 500);
        }
    }
}
