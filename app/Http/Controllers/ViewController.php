<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Kpp;
use App\Models\Permit;
use App\Models\WclCompany;
use App\Models\WclLog;
use App\Models\WhiteCarList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use File;
use Auth;

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
        $companies = Company::all();

        return view('view', compact('cur_month', 'pre_month', 'companies'));
    }

    public function getCarInfo($gov_number)
    {
        $gov_number = strtolower(trim($gov_number));
        $car = Car::where(['cars.gov_number' => $gov_number])->first();
        if($car) {
            $car = Car::where(['cars.gov_number' => $gov_number])
                ->join('permits', 'permits.tex_number', 'cars.tex_number')
                ->first();
        }
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
        $types_id = $data['types_id'];
        if($types_id == 0) {
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
        } else {
            switch ($report_id) {
                case 0:
                    $link_to_file = $this->generateLogsWcl($data);
                    break;

                case 1:
                    $link_to_file = $this->generateSvodWcl($data);
                    break;

                default:
                    $link_to_file = $this->generateLogsWcl($data);
                    break;
            }
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
                $kpp_name = "kpp1";
                break;

            case 2:
                $kpp_name = "kpp2";
                break;

            case 3:
                $kpp_name = "kpp4";
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
                $name = "КПП (все)";
                break;

            case 1:
                $kpp_name = "kpp1";
                $name = "КПП №1";
                break;

            case 2:
                $kpp_name = "kpp2";
                $name = "КПП №2";
                break;

            case 3:
                $kpp_name = "kpp4";
                $name = "КПП №4";
                break;

            default:
                $name = "КПП (все)";
                break;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('СВОД');
        $str = "Отчет по заезду автомашин через $name на объект «Даму» в период с $from_date по $to_date";
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
        $lists = WhiteCarList::/*where('kpp_name', '<>', 'kpp7')*/where('wcl_companies.status', 'ok')
            ->selectRaw('white_car_lists.kpp_name, white_car_lists.gov_number, companies.short_ru_name, wcl_companies.id as wcl_com_id, white_car_lists.id')
            ->join('wcl_companies', 'wcl_companies.wcl_id', '=', 'white_car_lists.id')
            ->join('companies', 'wcl_companies.company_id', '=', 'companies.id')
            //->where('wcl_companies.status', 'ok')
            //->where('wcl_companies.wcl_id', 1553)
            ->get();
        return view('white_car_lists', compact('lists'));
    }

    public function fixDateInTime(Request $request, $wcl_com_id)
    {
        $wcl_company = WclCompany::findOrFail($wcl_com_id);
        $wcl_log = WclLog::create([
            'wcl_com_id' => $wcl_com_id, 'gov_number' => $wcl_company->wcl->gov_number, 'company' => $wcl_company->company->short_ru_name
        ]);

        return response()->json($wcl_log);
    }

    public function searchByNumberInWCL(Request $request)
    {
        $search_number = $request->input('search_key');
        /*$lists = WhiteCarList::where('wcl_companies.status', 'ok')
            ->selectRaw('white_car_lists.kpp_name, white_car_lists.gov_number, companies.short_ru_name, wcl_companies.id as wcl_com_id, white_car_lists.id')
            ->join('wcl_companies', 'wcl_companies.wcl_id', '=', 'white_car_lists.id')
            ->join('companies', 'wcl_companies.company_id', '=', 'companies.id')
            ->where('white_car_lists.gov_number', 'LIKE', '%'.$search_number.'%')
            ->get();*/

        $lists = WhiteCarList::where('wcl_companies.status', 'ok')
            ->selectRaw('white_car_lists.kpp_name, white_car_lists.gov_number, companies.short_ru_name, wcl_companies.id as wcl_com_id, white_car_lists.id, wcl_companies.company_id')
            ->join('wcl_companies', 'wcl_companies.wcl_id', '=', 'white_car_lists.id')
            ->join('companies', 'wcl_companies.company_id', '=', 'companies.id')
            ->where('white_car_lists.gov_number', 'LIKE', '%'.$search_number.'%')
            ->get();

        $user_kpp = Kpp::whereName(Auth::user()->kpp_name)->first();
        foreach($lists as $list) {
            $company = Company::findOrFail($list->company_id);
            if($company->hasKpp(Auth::user()->kpp_name)) {
                $list->kpp_name = $user_kpp->title . ". ЗАЕЗД РАЗРЕШЕН!";
                $list->permit = true;
            } else {
                $kpps = $company->kpps;
                $arr = [];
                foreach($kpps as $kpp) {
                    $arr[] = $kpp->title;
                }

                $list->kpp_name = $user_kpp->title . ". ЗАЕЗД ДОЛЖЕН ОСУЩЕСТВЛЯТЬСЯ ЧЕРЕЗ: " . implode(', ', $arr);
                $list->permit = false;
            }
        }

        return response()->json($lists);
    }

    public function generateLogsWcl($data)
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];
        $kpp_id = $data['kpp_id'];

        /*switch ($kpp_id) {
            case 0:
                $kpp_name = "";
                break;

            case 1:
                $kpp_name = "kpp1";
                break;

            case 2:
                $kpp_name = "kpp2";
                break;

            case 3:
                $kpp_name = "kpp4";
                break;
        }*/

        $kpp_name = "";

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Список машин (белый список)');

        $sheet->setCellValue('A1', 'Гос.номер');
        $sheet->setCellValue('B1', 'Компания');
        $sheet->setCellValue('C1', 'Марка');
        $sheet->setCellValue('D1', 'ФИО');
        $sheet->setCellValue('E1', 'Должность');
        $sheet->setCellValue('F1', 'Дата');

        if ($company_id == 0) {
            if($kpp_name == "") {
                $logs = WclLog::whereRaw("(wcl_logs.created_at >= ? AND wcl_logs.created_at <= ?)", [$from_date." 00:00", $to_date." 23:59"])
                    ->selectRaw('wcl_logs.*, white_car_lists.mark_car,white_car_lists.full_name,white_car_lists.position')
                    ->join('wcl_companies', 'wcl_companies.id', 'wcl_logs.wcl_com_id')
                    ->join('white_car_lists', 'white_car_lists.id', 'wcl_companies.wcl_id')
                    ->get();
            } else {
                $logs = WclLog::whereRaw("(wcl_logs.created_at >= ? AND wcl_logs.created_at <= ?)", [$from_date." 00:00", $to_date." 23:59"])
                    ->selectRaw('wcl_logs.*, white_car_lists.mark_car,white_car_lists.full_name,white_car_lists.position')
                    ->join('wcl_companies', 'wcl_companies.id', 'wcl_logs.wcl_com_id')
                    ->join('white_car_lists', 'white_car_lists.id', 'wcl_companies.wcl_id')
                    ->get();
            }
        } else{
            if($kpp_name == "") {
                $logs = WclLog::whereRaw("(wcl_logs.created_at >= ? AND wcl_logs.created_at <= ?)", [$from_date." 00:00", $to_date." 23:59"])
                    ->selectRaw('wcl_logs.*, white_car_lists.mark_car,white_car_lists.full_name,white_car_lists.position')
                    ->join('wcl_companies', 'wcl_companies.id', 'wcl_logs.wcl_com_id')
                    ->join('white_car_lists', 'white_car_lists.id', 'wcl_companies.wcl_id')
                    ->where(['wcl_companies.company_id' => $company_id])
                    ->get();
            } else {
                $logs = WclLog::whereRaw("(wcl_logs.created_at >= ? AND wcl_logs.created_at <= ?)", [$from_date." 00:00", $to_date." 23:59"])
                    ->selectRaw('wcl_logs.*, white_car_lists.mark_car,white_car_lists.full_name,white_car_lists.position')
                    ->join('wcl_companies', 'wcl_companies.id', 'wcl_logs.wcl_com_id')
                    ->join('white_car_lists', 'white_car_lists.id', 'wcl_companies.wcl_id')
                    ->where(['wcl_companies.company_id' => $company_id])
                    ->get();
            }
        }

        $row_start = 1;
        $current_row = $row_start;
        foreach($logs as $key=>$log) {
            $current_row++;
            $sheet->insertNewRowBefore($row_start + $key+1, 1);
            $sheet->setCellValue('A'.$current_row,$log->gov_number);
            $sheet->setCellValue('B'.$current_row,$log->company);
            $sheet->setCellValue('C'.$current_row,$log->mark_car);
            $sheet->setCellValue('D'.$current_row,$log->full_name);
            $sheet->setCellValue('E'.$current_row,$log->position);
            $sheet->setCellValue('F'.$current_row,$log->created_at);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = date('Y_m_d_H_i_s') . '_wcl_logs.xlsx';
        $path_to_file = '/reports/it/' . $filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    public function generateSvodWcl($data)
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        /*$kpp_id = $data['kpp_id'];
        switch ($kpp_id) {
            case 0:
                $kpp_name = "";
                $name = "КПП (все)";
                break;

            case 1:
                $kpp_name = "kpp1";
                $name = "КПП №1";
                break;

            case 2:
                $kpp_name = "kpp2";
                $name = "КПП №2";
                break;

            case 3:
                $kpp_name = "kpp4";
                $name = "КПП №4";
                break;

            default:
                $name = "КПП (все)";
                break;
        }*/

        $kpp_name = "";
        $name = "КПП №1";

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('СВОД (белый список)');
        $str = "Отчет по заезду автомашин через $name на объект «Даму» в период с $from_date по $to_date";
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(16)->setBold(true);

        if($kpp_name == "") {
            $logs = WclLog::whereRaw("(created_at >= ? AND created_at <= ?)", [$from_date." 00:00", $to_date." 23:59"])
                ->selectRaw('company, COUNT(*) as cnt')
                ->groupBy('company')
                ->orderBy('company')
                ->get();
        } else {
            $logs = WclLog::whereRaw("(created_at >= ? AND created_at <= ?)", [$from_date." 00:00", $to_date." 23:59"])
                ->selectRaw('company, COUNT(*) as cnt')
                ->groupBy('company')
                ->orderBy('company')
                ->get();
        }


        $sheet->setCellValue('A3', 'Компании');
        $sheet->setCellValue('B3', 'Общий итог');

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);

        $row_start = 3;
        $current_row = $row_start;
        $sum = 0;
        foreach($logs as $key=>$log) {
            $current_row++;
            $sheet->insertNewRowBefore($row_start + $key+1, 1);
            $sheet->setCellValue('A'.$current_row,$log->company);
            $sheet->setCellValue('B'.$current_row,$log->cnt);
            $sum += $log->cnt;
        }

        $current_row += 2;

        $sheet->setCellValue('A'.$current_row,'Общий итог');
        $sheet->setCellValue('B'.$current_row,$sum);


        $sheet->getStyle('A3')->getFont()->setBold(true);
        $sheet->getStyle('B3')->getFont()->setBold(true);

        $sheet->getStyle('A'.$current_row)->getFont()->setBold(true);
        $sheet->getStyle('B'.$current_row)->getFont()->setBold(true);

        $writer = new Xlsx($spreadsheet);
        $filename = date('Y_m_d_H_i_s') . '_svods_wcl.xlsx';
        $path_to_file = '/reports/it/' . $filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }
}
