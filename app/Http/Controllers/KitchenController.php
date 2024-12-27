<?php

namespace App\Http\Controllers;

use App\Models\AshanaLog;
use App\Models\Company;
use App\Models\User;
use App\Traits\KitchenTraits;
use Illuminate\Http\Request;
use File;
use Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class KitchenController extends BaseController
{
    use KitchenTraits;

    public function index()
    {
        return view('ashana.index');
    }

    public function getStatistics()
    {
        $user = Auth::user();
        $logs = AshanaLog::whereDate('ashana_logs.date', '=', date("Y-m-d"))
            ->selectRaw('ashana_logs.id,users.full_name,companies.short_ru_name as company_name,positions.title as p_name, SUM(IF(ashana_logs.cashier_id = 1097, 1, 0)) as abk,SUM(IF(ashana_logs.cashier_id = 1773, 1, 0)) as kpp3,SUM(IF(ashana_logs.cashier_id = 1238, 1, 0)) as mob')
            ->join('users', 'users.id', 'ashana_logs.user_id')
            ->join('companies', 'companies.id', 'ashana_logs.company_id')
            ->leftJoin('positions', 'positions.id', 'users.position_id')
            ->groupBy('ashana_logs.user_id', 'ashana_logs.company_id');

        if($user->company_id == 121) {
            $logs = $logs->whereIn('ashana_logs.cashier_id', [1097,1238,1773]);
        } else {
            $logs = $logs->where('ashana_logs.cashier_id', '=', 1099);
        }

        return response($logs->get());
    }

    public function getUserInfo(Request $request)
    {
        $username = $request->input('username');
        $username = trim($username);
        if($this->isKazakh($username)) {
            return response([
                'message' => 'ПОЖАЛУЙСТА ПОМЕНЯЙТЕ РАСКЛАДКУ НА АНГЛИЙСКУЮ ИЛИ НА РУССКУЮ'
            ], 406);
        }

        if($this->isRussian($username)) {
            $username = $this->switch_en($username);
        }

        $users = User::where(['users.uuid' => $username])->get();

        if(count($users) == 0) {
            return response([
                'message' => 'НЕ НАЙДЕНО. ОБРАЩАЙТЕСЬ В ОТДЕЛ КАДРОВ ЗА ТАЛОНОМ.'
            ], 404);
        }

        foreach($users as $user) {
            if($user->hasWorkPermission()) {

                if($user->company->ashana === 1) {
                    return response([
                        'message' => 'Для сотрудников этой компании запрещено питаться в столовое'
                    ], 406);
                }

                if($user->position_id === 224 && $user->id != 1593) {
                    return response([
                        'message' => 'Для гостей запрещено использовать бейджик в столовое'
                    ], 406);
                }

                return response([
                    'full_name' => $user->full_name,
                    'company_name' => $user->company->short_ru_name,
                    'count' => $user->countAshanaToday(),
                    'user_id' => $user->id,
                    'image' => (file_exists(public_path() . $user->image)) ? $user->image : null
                ], 200);
            }
        }

        return response([
            'message' => 'СОТРУДНИК УВОЛЕН'
        ], 406);
    }

    public function fixChanges(Request $request)
    {
        $user_id     = (int) $request->input('user_id');
        $cashier_id  = (int) $request->input('cashier_id') ?? 0;
        $din_type = (int) $request->input('din_type');

        $user = User::find($user_id);
        if($user) {
            if($user->company_id == 0) {
                return response('В штрих-коде отсутствует компания, срочно обратитесь в ИТ', 406);
            }

            $countAshanaToday = $user->countAshanaToday();

            if($user->position_id == 188 && $countAshanaToday == 1) {
                return response('Ваш лимит обедов за сегодня исчерпан', 406);
            }

            if($countAshanaToday > 1) {
                return response('Ваш лимит обедов за сегодня исчерпан', 406);
            }

            // Подготовка папок для сохранение картинки
            $dir = '/kitchen_photos/'. substr(md5(microtime()), mt_rand(0, 30), 2) . '/' . substr(md5(microtime()), mt_rand(0, 30), 2);
            if($request->input('path_to_image') && !empty($request->input('path_to_image'))) {
                if(!File::isDirectory(public_path(). $dir)){
                    File::makeDirectory(public_path(). $dir, 0777, true);
                }
            }

            if ($request->input('path_to_image') && !empty($request->input('path_to_image'))){
                $image = $request->input('path_to_image'); // image base64 encoded
                preg_match("/data:image\/(.*?);/",$image,$image_extension); // extract the image extension
                $image = preg_replace('/data:image\/(.*?);base64,/','',$image); // remove the type part
                $image = str_replace(' ', '+', $image);
                $imageName = $user->id.'_'.time() . '.' . $image_extension[1]; //generating unique file name;
                File::put(public_path(). $dir.'/'.$imageName,base64_decode($image));
                $pathToImage = $dir.'/'.$imageName;
            } else {
                $pathToImage = null;
            }


            AshanaLog::create([
                'user_id' => $user->id, 'company_id' => $user->company_id, 'din_type' => $din_type,
                'cashier_id' => $cashier_id, 'path_to_image' => $pathToImage
            ]);

            return response([
                'din_type' => $din_type,
                'full_name' => $user->full_name,
                'count' => $user->countAshanaToday()
            ], 200);
        } else {
            return response('Не найден пользватель, срочно обратитесь в ИТ', 404);
        }
    }

    public function generateLogs(Request $request)
    {
        $data = $request->all();
        return ($data['cashier_id'] == 1099) ? $this->akmuratov($data) : $this->cargoTraffic($data);
    }

    public function akmuratov($data)
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];
        $cashier_id = $data['cashier_id'];

        $company = Company::findOrFail($company_id);
        $logs = AshanaLog::whereDate('ashana_logs.date', '>=', $from_date)->whereDate('ashana_logs.date', '<=', $to_date)
            ->selectRaw('users.full_name,ashana_logs.din_type,companies.short_ru_name as company_name,positions.title as p_name, SUM(IF(ashana_logs.cashier_id = 1099, 1, 0)) as akm')
            ->join('users', 'users.id', 'ashana_logs.user_id')
            ->join('companies', 'companies.id', 'ashana_logs.company_id')
            ->leftJoin('positions', 'positions.id', 'users.position_id')
            ->where('ashana_logs.company_id', $company_id)
            ->where('ashana_logs.cashier_id', $cashier_id)
            ->groupBy('ashana_logs.user_id', 'ashana_logs.company_id')
            ->get();
        $kitchen_company = "ИП Акмуратов А.М";

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Отчет по столовое');

        $str = "Отчет по обедам за период: ".$from_date." - ".$to_date;
        $sheet->mergeCells('A1:D1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(12)->setBold(true);

        $sheet->mergeCells('A2:D2');
        $sheet->setCellValue('A2', "Оператор столовой: ".$kitchen_company);
        $sheet->getStyle("A2")->getFont()->setSize(12)->setBold(true);

        $sheet->mergeCells('A3:D3');
        $sheet->setCellValue('A3', "Клиент: ".$company->short_ru_name);
        $sheet->getStyle("A3")->getFont()->setSize(12)->setBold(true);

        $sheet->setCellValue('A5', 'Ф.И.О');
        $sheet->setCellValue('B5', 'Должность');
        $sheet->setCellValue('C5', 'Тип обеда');
        $sheet->setCellValue('D5', 'Сумма');

        // Вставляем авто размер для колонок
        $this->setAutoSizeColumn($sheet, true, 'A', 'B', 'C', 'D');

        $itog = 0;

        $row_start = 5;
        $current_row = $row_start;
        foreach($logs as $key=>$item){
            $current_row++;
            $sheet->insertNewRowBefore($row_start + $key+1, 1);
            $sheet->setCellValue('A'.$current_row,$item->full_name);
            $sheet->setCellValue('B'.$current_row,$item->p_name);
            $din_type = ($item->din_type == 1) ? "Стандарт" : "Булочки";
            $sheet->setCellValue('C'.$current_row,$din_type);
            $sheet->setCellValue('D'.$current_row,$item->akm);
            $itog += (int) $item->akm;
        }

        $current_row++;

        $sheet->mergeCells('A'.$current_row.':C'.$current_row);
        $sheet->setCellValue('A'.$current_row, "ИТОГО");
        $sheet->setCellValue('D'.$current_row,$itog);

        $sheet->getStyle("A".$current_row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle("D".$current_row)->getFont()->setSize(12)->setBold(true);

        // Выравниваем по левому краю
        $this->setHorizontal($sheet, Alignment::HORIZONTAL_RIGHT, 'A'.$current_row);

        $writer = new Xlsx($spreadsheet);
        $filename = "ashana_report_for_".date('d.m.Y_H_i_s_').$company_id.".xlsx";

        // Подготовка папок для сохранение картинки
        $dir = '/reports/ashana/'. $company_id . '/' . date('Y-m').'/';
        if(!File::isDirectory(public_path(). $dir)){
            File::makeDirectory(public_path(). $dir, 0777, true);
        }

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    public function cargoTraffic($data)
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];

        $company = Company::findOrFail($company_id);
        $logs = AshanaLog::whereDate('ashana_logs.date', '>=', $from_date)->whereDate('ashana_logs.date', '<=', $to_date)
            ->selectRaw('users.full_name,ashana_logs.din_type,companies.short_ru_name as company_name,positions.title as p_name, SUM(IF(ashana_logs.cashier_id = 1097, 1, 0)) as abk,SUM(IF(ashana_logs.cashier_id = 1238, 1, 0)) as mob, SUM(IF(ashana_logs.cashier_id = 1773, 1, 0)) as kpp3')
            ->join('users', 'users.id', 'ashana_logs.user_id')
            ->join('companies', 'companies.id', 'ashana_logs.company_id')
            ->leftJoin('positions', 'positions.id', 'users.position_id')
            ->where('ashana_logs.company_id', $company_id)
            ->whereIn('ashana_logs.cashier_id', [1097,1238,1773])
            ->groupBy('ashana_logs.user_id', 'ashana_logs.company_id')
            ->get();
        $kitchen_company = "ИП Cargotraffic(АБК + Мобильная + КПП3)";

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Отчет по столовое');

        $str = "Отчет по обедам за период: ".$from_date." - ".$to_date;
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(12)->setBold(true);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', "Оператор столовой: ".$kitchen_company);
        $sheet->getStyle("A2")->getFont()->setSize(12)->setBold(true);

        $sheet->mergeCells('A3:G3');
        $sheet->setCellValue('A3', "Клиент: ".$company->short_ru_name);
        $sheet->getStyle("A3")->getFont()->setSize(12)->setBold(true);

        $sheet->setCellValue('A5', 'Ф.И.О');
        $sheet->setCellValue('B5', 'Должность');
        $sheet->setCellValue('C5', 'Тип обеда');
        $sheet->setCellValue('D5', 'АБК');
        $sheet->setCellValue('E5', 'Мобильная');
        $sheet->setCellValue('F5', 'КПП3');
        $sheet->setCellValue('G5', 'Сумма');

        // Вставляем авто размер для колонок
        $this->setAutoSizeColumn($sheet, true, 'A', 'B', 'C', 'D', 'E', 'F', 'G');

        $abk = 0;
        $mob = 0;
        $kpp3 = 0;
        $itog = 0;

        $row_start = 5;
        $current_row = $row_start;
        foreach($logs as $key=>$item){
            $current_row++;
            $sheet->insertNewRowBefore($row_start + $key+1, 1);
            $sheet->setCellValue('A'.$current_row,$item->full_name);
            $sheet->setCellValue('B'.$current_row,$item->p_name);
            $din_type = ($item->din_type == 1) ? "Стандарт" : "Булочки";
            $sheet->setCellValue('C'.$current_row,$din_type);
            $sheet->setCellValue('D'.$current_row,$item->abk);
            $sheet->setCellValue('E'.$current_row,$item->mob);
            $sheet->setCellValue('F'.$current_row,$item->kpp3);
            $sum = (int) $item->abk + (int) $item->mob+ (int) $item->kpp3;
            $sheet->setCellValue('G'.$current_row,$sum);
            $abk += (int) $item->abk;
            $mob += (int) $item->mob;
            $kpp3 += (int) $item->kpp3;
            $itog += (int) $sum;
        }

        $current_row++;

        $sheet->mergeCells('A'.$current_row.':C'.$current_row);
        $sheet->setCellValue('A'.$current_row, "ИТОГО");
        $sheet->setCellValue('D'.$current_row,$abk);
        $sheet->setCellValue('E'.$current_row,$mob);
        $sheet->setCellValue('F'.$current_row,$kpp3);
        $sheet->setCellValue('G'.$current_row,$itog);

        $sheet->getStyle("A".$current_row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle("D".$current_row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle("E".$current_row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle("F".$current_row)->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle("G".$current_row)->getFont()->setSize(12)->setBold(true);

        // Выравниваем по левому краю
        $this->setHorizontal($sheet, Alignment::HORIZONTAL_RIGHT, 'A'.$current_row);

        $writer = new Xlsx($spreadsheet);
        $filename = "ashana_report_for_".date('d.m.Y_H_i_s_').$company_id.".xlsx";

        // Подготовка папок для сохранение картинки
        $dir = '/reports/ashana/'. $company_id . '/' . date('Y-m').'/';
        if(!File::isDirectory(public_path(). $dir)){
            File::makeDirectory(public_path(). $dir, 0777, true);
        }

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }


    // метод установливает выравнение (горизантально) по ячейке в зависимости от заданного условия
    public function setHorizontal($sheet, $position, ...$columns)
    {
        foreach($columns as $column) {
            $sheet->getStyle($column)->getAlignment()->setHorizontal($position);
        }
    }

    // метод установливает авто размер в зависимости от заданного условия
    public function setAutoSizeColumn($sheet, $boolean = false, ...$columns)
    {
        foreach($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize($boolean);
        }
    }
}
