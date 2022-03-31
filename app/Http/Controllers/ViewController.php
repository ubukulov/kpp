<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Permit;
use App\Models\WhiteCarList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use File;

class ViewController extends BaseController
{
    public function index()
    {
        $cur_month = DB::select("SELECT COUNT(*) as cnt FROM permits
                         WHERE date_in IS NOT NULL AND status='printed'
                         AND MONTH(date_in)=MONTH(CURDATE()) AND YEAR(date_in)=YEAR(CURDATE())");

        $pre_month = DB::select("SELECT COUNT(*) as cnt FROM permits
                         WHERE date_in IS NOT NULL AND status='printed'
                         AND MONTH(date_in)=MONTH(DATE_ADD(NOW(), INTERVAL -1 MONTH)) AND YEAR(date_in)=YEAR(NOW())");

        return view('view', compact('cur_month', 'pre_month'));
    }

    public function getCarInfo($tex_number)
    {
        $tex_number = strtolower(trim($tex_number));
        $car = Car::where(['cars.tex_number' => $tex_number])
            ->join('permits', 'permits.tex_number', '=', 'cars.tex_number')
            ->first();
        return json_encode($car);
    }

    public function getDriverInfo($ud_number)
    {
        $ud_number = mb_strtolower(trim($ud_number));
        $driver = Driver::where(['ud_number' => $ud_number])->first();
        return json_encode($driver);
    }

    public function getPermitsForSelectedTime(Request $request)
    {
        $data = $request->all();
        $report_id = $data['report_id'];
        switch ($report_id) {
            case 0:
                $link_to_file = $this->generateLogs($data);
                break;

            case 1:
                $link_to_file = $this->generateSvod($data);
                break;

            default:
                $link_to_file = $this->generateLogs($data);
                break;
        }

        return $link_to_file;
    }

    public function generateLogs($data)
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];
        $kpp_id = $data['kpp_id'];

        switch ($kpp_id) {
            case 0:
                $kpp_name = "";
                break;

            case 1:
                $kpp_name = "kpp2";
                break;

            case 2:
                $kpp_name = "kpp4";
                break;

            case 3:
                $kpp_name = "kpp5";
                break;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Список пропусков за все время');

        $sheet->setCellValue('A1', 'Номер пропуска');
        $sheet->setCellValue('B1', 'Компания');
        $sheet->setCellValue('C1', 'Марка');
        $sheet->setCellValue('D1', 'Гос.номер');
        $sheet->setCellValue('E1', 'Прицеп');
        $sheet->setCellValue('F1', 'Тех.паспорт');
        $sheet->setCellValue('G1', 'Операция');
        $sheet->setCellValue('H1', 'Дата заезда');
        $sheet->setCellValue('I1', 'Дата выезда');
        $sheet->setCellValue('J1', 'ФИО');
        $sheet->setCellValue('K1', 'Телефон');
        $sheet->setCellValue('L1', 'Права');
        $sheet->setCellValue('M1', 'Грузоподъемность');
        $sheet->setCellValue('N1', 'Кузов');
        $sheet->setCellValue('O1', 'Собственник по тех.пас');
        $sheet->setCellValue('P1', 'Маршрут');
        $sheet->setCellValue('Q1', 'Наниматель');
        $sheet->setCellValue('R1', 'Иностранная');
        $sheet->setCellValue('S1', 'Статус');
        $sheet->setCellValue('T1', 'Оформил');
        $sheet->setCellValue('U1', 'КПП');

        if ($company_id == 0) {
            if($kpp_name == "") {
                $permits = Permit::where(['status' => 'printed'])
                    ->where('lc_id', '>', 0)
                    ->whereNotNull('date_in')
                    ->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])
                    ->get();
            } else {
                $permits = Permit::where(['status' => 'printed'])
                    ->where('lc_id', '>', 0)
                    ->where('kpp_name', '=', $kpp_name)
                    ->whereNotNull('date_in')
                    ->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])
                    ->get();
            }
        } else{
            if($kpp_name == "") {
                $permits = Permit::where(['company_id' => $company_id, 'status' => 'printed'])
                    ->where('lc_id', '>', 0)
                    ->whereNotNull('date_in')
                    ->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])
                    ->get();
            } else {
                $permits = Permit::where(['company_id' => $company_id, 'status' => 'printed'])
                    ->where('lc_id', '>', 0)
                    ->where('kpp_name', '=', $kpp_name)
                    ->whereNotNull('date_in')
                    ->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])
                    ->get();
            }
        }

        $row_start = 1;
        $current_row = $row_start;
        foreach($permits as $key=>$permit) {
            $current_row++;
            $sheet->insertNewRowBefore($row_start + $key+1, 1);
            $sheet->setCellValue('A'.$current_row,$permit->id);
            $sheet->setCellValue('B'.$current_row,$permit->company);
            $sheet->setCellValue('C'.$current_row,$permit->mark_car);
            $sheet->setCellValue('D'.$current_row,$permit->gov_number);
            $sheet->setCellValue('E'.$current_row,$permit->pr_number);
            $sheet->setCellValue('F'.$current_row,$permit->tex_number);
            if($permit->operation_type == 1) {
                $operation_type = 'Погрузка';
            } elseif($permit->operation_type == 2) {
                $operation_type = 'Разгрузка';
            } else {
                $operation_type = 'Другие действия';
            }
            $sheet->setCellValue('G'.$current_row,$operation_type);
            $sheet->setCellValue('H'.$current_row,$permit->date_in);
            $sheet->setCellValue('I'.$current_row,$permit->date_out);
            $sheet->setCellValue('J'.$current_row,$permit->last_name);
            $sheet->setCellValue('K'.$current_row,$permit->phone);
            $sheet->setCellValue('L'.$current_row,$permit->ud_number);
            if($permit->lc_id == 1) {
                $lc_id = 'Легковые';
            } elseif($permit->lc_id == 2) {
                $lc_id = 'до 10тонн';
            } else {
                $lc_id = 'Грузовые';
            }
            $sheet->setCellValue('M'.$current_row,$lc_id);
            $bt_id = ($permit->bt_id == 1) ? 'Рефрижератор (холод)' : 'Обычный';
            $sheet->setCellValue('N'.$current_row,$bt_id);
            $sheet->setCellValue('O'.$current_row,$permit->from_company);
            $sheet->setCellValue('P'.$current_row,$permit->to_city);
            $sheet->setCellValue('Q'.$current_row,$permit->employer_name);
            if($permit->foreign_car == 0) {
                $foreign_car = 'Не указано';
            } elseif($permit->foreign_car == 1) {
                $foreign_car = 'Казахстанская';
            } else {
                $foreign_car = 'Иностранная';
            }
            $sheet->setCellValue('R'.$current_row,$foreign_car);
            $sheet->setCellValue('S'.$current_row,$permit->status);
            $sheet->setCellValue('T'.$current_row,$permit->is_driver);
            $sheet->setCellValue('U'.$current_row, strtoupper($permit->kpp_name));
        }

        $writer = new Xlsx($spreadsheet);
        $filename = date('Y_m_d_H_i_s') . '_permits.xlsx';
        $path_to_file = '/reports/it/' . $filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    public function generateSvod($data)
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $kpp_id = $data['kpp_id'];
        switch ($kpp_id) {
            case 0:
                $kpp_name = "";
                break;

            case 1:
                $kpp_name = "kpp2";
                break;

            case 2:
                $kpp_name = "kpp4";
                break;

            case 3:
                $kpp_name = "kpp5";
                break;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('СВОД');
        $str = "Отчет по заезду автомашин через КПП № 2 на объект «Даму» в период с $from_date по $to_date";
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(16)->setBold(true);

        if($kpp_name == "") {
            $permits = Permit::where(['status' => 'printed'])
                ->selectRaw('company, SUM(CASE WHEN lc_id = 1 THEN 1 ELSE 0 END) as leg, SUM(CASE WHEN lc_id = 2 THEN 1 ELSE 0 END) as d10, SUM(CASE WHEN lc_id = 3 THEN 1 ELSE 0 END) as grz, SUM(CASE WHEN from_company = "ЧАСТНИК" THEN 1 ELSE 0 END) as cht')
                ->where('lc_id', '>', 0)->whereNotNull('date_in')
                ->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])
                ->groupBy('company')
                ->orderBy('company')
                ->get();
        } else {
            $permits = Permit::where(['status' => 'printed'])
                ->selectRaw('company, SUM(CASE WHEN lc_id = 1 THEN 1 ELSE 0 END) as leg, SUM(CASE WHEN lc_id = 2 THEN 1 ELSE 0 END) as d10, SUM(CASE WHEN lc_id = 3 THEN 1 ELSE 0 END) as grz, SUM(CASE WHEN from_company = "ЧАСТНИК" THEN 1 ELSE 0 END) as cht')
                ->where('lc_id', '>', 0)
                ->where('kpp_name', '=', $kpp_name)
                ->whereNotNull('date_in')
                ->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])
                ->groupBy('company')
                ->orderBy('company')
                ->get();
        }


        $sheet->setCellValue('A3', 'Компании');
        $sheet->setCellValue('B3', 'Легковые');
        $sheet->setCellValue('C3', 'До 10тонн');
        $sheet->setCellValue('D3', 'Грузовые');
        $sheet->setCellValue('E3', 'Общий итог');
        $sheet->setCellValue('F3', 'Частники');

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);

        $row_start = 3;
        $current_row = $row_start;
        $sum_grz = 0; $sum_d10 = 0; $sum_leg = 0; $sum_all = 0; $cht = 0;
        foreach($permits as $key=>$permit) {
            $current_row++;
            $sheet->insertNewRowBefore($row_start + $key+1, 1);
            $sheet->setCellValue('A'.$current_row,$permit->company);
            $sheet->setCellValue('B'.$current_row,$permit->leg);
            $sheet->setCellValue('C'.$current_row,$permit->d10);
            $sheet->setCellValue('D'.$current_row,$permit->grz);
            $sum = $permit->leg + $permit->d10 + $permit->grz;
            $sheet->setCellValue('E'.$current_row,$sum);
            $sheet->setCellValue('F'.$current_row,$permit->cht);
            $sum_grz += $permit->grz;
            $sum_d10 += $permit->d10;
            $sum_leg += $permit->leg;
            $sum_all += $sum;
            $cht     += $permit->cht;
        }

        $current_row += 2;

        $sheet->setCellValue('A'.$current_row,'Общий итог');
        $sheet->setCellValue('B'.$current_row,$sum_leg);
        $sheet->setCellValue('C'.$current_row,$sum_d10);
        $sheet->setCellValue('D'.$current_row,$sum_grz);
        $sheet->setCellValue('E'.$current_row,$sum_all);
        $sheet->setCellValue('F'.$current_row,$cht);


        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('B3')->getFont()->setBold(true);
        $sheet->getStyle('C3')->getFont()->setBold(true);
        $sheet->getStyle('D3')->getFont()->setBold(true);
        $sheet->getStyle('E3')->getFont()->setBold(true);
        $sheet->getStyle('F3')->getFont()->setBold(true);

        $sheet->getStyle('A'.$current_row)->getFont()->setBold(true);
        $sheet->getStyle('B'.$current_row)->getFont()->setBold(true);
        $sheet->getStyle('C'.$current_row)->getFont()->setBold(true);
        $sheet->getStyle('D'.$current_row)->getFont()->setBold(true);
        $sheet->getStyle('E'.$current_row)->getFont()->setBold(true);
        $sheet->getStyle('F'.$current_row)->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
        $filename = date('Y_m_d_H_i_s') . '_svods.xlsx';
        $path_to_file = '/reports/it/' . $filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    public function whiteCarLists()
    {
        $lists = WhiteCarList::where('kpp_name', '<>', 'kpp7')
            ->join('wcl_companies', 'wcl_companies.wcl_id', '=', 'white_car_lists.id')
            ->join('companies', 'wcl_companies.company_id', '=', 'companies.id')
            ->where('wcl_companies.status', 'ok')
            //->where('wcl_companies.wcl_id', 1553)
            ->get();
        return view('white_car_lists', compact('lists'));
    }

    public function whiteCarListsForKpp7()
    {
        $lists = WhiteCarList::where('kpp_name', '=', 'kpp7')
            ->join('wcl_companies', 'wcl_companies.wcl_id', '=', 'white_car_lists.id')
            ->join('companies', 'wcl_companies.company_id', '=', 'companies.id')
            ->where('wcl_companies.status', 'ok')
            //->where('wcl_companies.wcl_id', 1553)
            ->get();
        return view('white_car_lists_kpp7', compact('lists'));
    }
}
