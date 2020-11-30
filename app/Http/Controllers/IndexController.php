<?php

namespace App\Http\Controllers;

use App\Classes\PrintIPP;
use App\Company;
use App\Driver;
use App\Permit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class IndexController extends BaseController
{
    public function welcome()
    {
        return view('welcome');
    }

    public function securityKpp()
    {
        $permits = Permit::orderBy('id', 'DESC')->take(20)->get();
        return view('kpp', compact('permits'));
    }

    public function orderPermitByKpp(Request $request)
    {
        $company = Company::findOrFail($request->input('company_id'));
        $data = $request->except(['path_docs_fac', 'path_docs_back']);
        $data['company'] = $company->short_ru_name;
        $com_id = $request->input('computer_name');
        $data['gov_number'] = mb_strtoupper(trim($data['gov_number']));
        $data['tex_number'] = mb_strtoupper(trim($data['tex_number']));
        $data['ud_number'] = mb_strtoupper(trim($data['ud_number']));
        $data['last_name'] = mb_strtoupper($data['last_name']);
        $permit = Permit::create($data);

        // Если новый водитель, то добавляем в справочник
        if (!Driver::exists($data['ud_number'])) {
            Driver::create([
                'fio' => $data['last_name'], 'phone' => $data['phone'], 'ud_number' => $data['ud_number']
            ]);
        }

        if ($request->path_docs_fac && !empty($request->path_docs_fac)){
            $image = $request->input('path_docs_fac'); // image base64 encoded
            preg_match("/data:image\/(.*?);/",$image,$image_extension); // extract the image extension
            $image = preg_replace('/data:image\/(.*?);base64,/','',$image); // remove the type part
            $image = str_replace(' ', '+', $image);
            $imageName = $permit->id.'_f_'.time() . '.' . $image_extension[1]; //generating unique file name;
            \File::put(public_path(). '/uploads/' .$imageName,base64_decode($image));
            $permit->path_docs_fac = $imageName;
            $permit->save();
        }

        if ($request->path_docs_back && !empty($request->path_docs_back)){
            $image2 = $request->input('path_docs_back'); // image base64 encoded
            preg_match("/data:image\/(.*?);/",$image2,$image_extension); // extract the image extension
            $image2 = preg_replace('/data:image\/(.*?);base64,/','',$image2); // remove the type part
            $image2 = str_replace(' ', '+', $image2);
            $imageName2 = $permit->id.'_b_'.time() . '.' . $image_extension[1]; //generating unique file name;
            \File::put(public_path(). '/uploads/' .$imageName2,base64_decode($image2));
            $permit->path_docs_back = $imageName2;
            $permit->save();
        }

        $this->start_print($permit->id, $com_id);

//        return redirect()->route('security.kpp');
        return response(['data' => 'Пропуск успешно создан']);
    }

    public function getPermits()
    {
        $permits = Permit::orderBy('id', 'DESC')->take(20)->get();
        return json_encode($permits);
    }

    public function getPrevPermitsForToday()
    {
        $permits = Permit::where(['is_driver' => 1, 'status' => 'awaiting_print'])->whereDate('created_at', Carbon::today())->orderBy('id', 'DESC')->get();
        return json_encode($permits);
    }

    public function getUserInfo($permit_id)
    {
        $permit = Permit::findOrFail($permit_id);
        return json_encode($permit);
    }

    public function getCarInfo($gov_number)
    {
        $gov_number = strtolower(trim($gov_number));
        $car = Permit::where(['gov_number' => $gov_number])->latest('id')->first();
        return json_encode($car);
    }

    public function getDriverInfo($ud_number)
    {
        $ud_number = mb_strtolower(trim($ud_number));
        $driver = Driver::where(['ud_number' => $ud_number])->first();
        return json_encode($driver);
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
        $permit = Permit::find($permit_id);

        if ($request->has('set_date_out_manual') && !empty($request->input('date_out'))) {
            $date_out = $request->input('date_out');
        } else {
            $date_out = date('d.m.Y H:i');
        }

        if($permit && is_null($permit->date_out)){
            $permit->date_out = $date_out;
            $permit->save();
            return response(['data' => 'Дата успешно зафиксирован']);
        } else{
            return response(['data' => 'Дата уже зафиксирован'], 500);
        }
    }

    public function start_print($permit_id, $com_id)
    {
        switch ($com_id) {
            case 1:
                $computer_name = "DL-0210";
                $printer_name = "\Zebra ZM400 (203 dpi) - ZPL";
                break;

            case 2:
                $computer_name = "HKS-097";
                $printer_name = "\Zebra ZM400 (203 dpi) - ZPL1";
                break;

            default:
                $computer_name = "DL-0210";
                $printer_name = "\Zebra ZM400 (203 dpi) - ZPL";
                break;
        }
        $permit = Permit::findOrFail($permit_id);
        $printer = "\\\\".$computer_name.$printer_name;
        //$printer = "\\\\HTLs-2394\zebra";
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
//        var_dump($fp);
        $id = $permit->id;
        $date_in = $permit->date_in;
        $mark_car = $permit->mark_car;
        $gov_number = $permit->gov_number;
        $fio = $permit->last_name;
        $company = $permit->company;
        $phone = $permit->phone;
        $ud_number = $permit->ud_number;
        $tex_number = $permit->tex_number;
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
^FT101,62^AZN,42,42,TT0003M_^FH\^CI17^F8^FDПРОПУСК №$id^FS^CI0
^BY3,3,58^FT499,80^BCN,,N,N
^FD>;$id^FS
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
^PQ1,0,1,Y^XZ
HERE;


        if (!fwrite($fp,$data)){
//            die('writing failed');
            return response(['data' => 'writing failed'], 500);
        }

        if ($permit->status == 'awaiting_print') {
            $permit->status = 'printed';
            $permit->save();
        }
    }
}
