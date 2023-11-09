<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Models\AshanaLog;
use App\Models\Company;
use Illuminate\Http\Request;
use Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use File;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KitchenController extends Controller
{
    public function ashana()
    {
        $user = Auth::user();
        $logs = $user->ashana;
        return view('cabinet.ashana.index', compact('logs'));
    }

    public function talon()
    {
        $user = Auth::user();
        return view('cabinet.ashana.talon', compact('user'));
    }

    public function reports()
    {
        $companies = Company::whereAshana(0)->get();
        return view('cabinet.ashana.reports', compact('companies'));
    }

    public function getLogs(Request $request)
    {
        $company_id = $request->input('company_id');
        $cashier_id = $request->input('cashier_id') ?? 0;
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $krt_id = $request->input('krt_id');

        if($krt_id == 1) {
            $logs = AshanaLog::whereDate('ashana_logs.date', '>=', $from_date)->whereDate('ashana_logs.date', '<=', $to_date)
                ->selectRaw('users.full_name,companies.short_ru_name as company_name,positions.title as p_name, SUM(IF(ashana_logs.cashier_id = 1097, 1, 0)) as abk,SUM(IF(ashana_logs.cashier_id = 1773, 1, 0)) as kpp3,SUM(IF(ashana_logs.cashier_id = 1238, 1, 0)) as mob')
                ->join('users', 'users.id', 'ashana_logs.user_id')
                ->join('companies', 'companies.id', 'ashana_logs.company_id')
                ->leftJoin('positions', 'positions.id', 'users.position_id')
                ->groupBy('ashana_logs.user_id', 'ashana_logs.company_id');

            if($company_id != 0) {
                $logs = $logs->where('ashana_logs.company_id', $company_id);
            }

            if($cashier_id != 0) {
                if($cashier_id == 1097) {
                    $logs = $logs->whereIn('ashana_logs.cashier_id', [1097,1238,1773]);
                } else {
                    $logs = $logs->where('ashana_logs.cashier_id', '=', $cashier_id);
                }
            }

            return response($logs->get());
        }

        if($from_date == $to_date) {
            $logs = AshanaLog::whereDate('ashana_logs.date', '>=', $from_date)->whereDate('ashana_logs.date', '<=', $to_date)
                ->selectRaw('users.id, users.full_name, companies.short_ru_name as company_name, positions.title as p_name, ashana_logs.din_type, COUNT(ashana_logs.id) as cnt')
                ->join('users', 'users.id', 'ashana_logs.user_id')
                ->join('companies', 'companies.id', 'ashana_logs.company_id')
                ->leftJoin('positions', 'positions.id', 'users.position_id');
        } else {
            $logs = AshanaLog::whereDate('ashana_logs.date', '>=', $from_date)->whereDate('ashana_logs.date', '<=', $to_date)
                ->selectRaw('users.id, users.full_name, companies.short_ru_name as company_name, positions.title as p_name, ashana_logs.din_type, COUNT(ashana_logs.id) as cnt')
                ->join('users', 'users.id', 'ashana_logs.user_id')
                ->join('companies', 'companies.id', 'ashana_logs.company_id')
                ->leftJoin('positions', 'positions.id', 'users.position_id');
        }

        /*if($cashier_id != 0) {
            $logs = $logs->where('ashana_logs.cashier_id', '=', $cashier_id);
        }*/
        if($cashier_id == 1097) {
            $logs = $logs->whereIn('ashana_logs.cashier_id', [1097,1238,1773]);
        } else {
            $logs = $logs->where('ashana_logs.cashier_id', '=', $cashier_id);
        }

        if($company_id == 0) {
            $logs = $logs->groupBy('ashana_logs.user_id')->get();

            return response($logs);
        } else {
            $logs = $logs->where('ashana_logs.company_id', $company_id)->groupBy('ashana_logs.user_id', 'ashana_logs.company_id')->get();

            return response($logs);
        }
    }

    public function generateReports(Request $request)
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
        $sheet->setCellValue('E5', 'КПП3');
        $sheet->setCellValue('F5', 'Мобильная');
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
            $sheet->setCellValue('E'.$current_row,$item->kpp3);
            $sheet->setCellValue('F'.$current_row,$item->mob);
            $sum = (int) $item->abk + (int) $item->mob + (int) $item->kpp3;
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
        $sheet->setCellValue('E'.$current_row,$kpp3);
        $sheet->setCellValue('F'.$current_row,$mob);
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
