<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Permit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use File;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends Controller
{
    public function index()
    {
        if(\Gate::allows('posmotret-otchet')) {
            $companies = Company::orderBy('short_en_name')->get();
            return view('cabinet.report.bux', compact('companies'));
        } else{
            $companies = Company::orderBy('short_en_name')->get();
            return view('cabinet.report.index', compact('companies'));
        }
    }

    public function downloadReport(Request $request)
    {
        $data = $request->all();
        switch ($data['company_id']) {
            case 24:
                $file_link = $this->generateReportForMechta($data);
                break;

            case 13:
                $file_link = $this->generateReportForMagnum($data);
                break;

            /*case 20:
                $file_link = $this->generateReportForKcell($data);
                break;*/

            default:
                $file_link = $this->generateReportForOther($data);
                break;
        }

        return response($file_link);
    }

    public function generateReportForMagnum($data)
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];

        $company = Company::findOrFail($company_id);
        $permits = Permit::where(['company_id' => $company_id])
                        ->selectRaw('id,gov_number,pr_number,mark_car,lc_id,date_in,date_out, FLOOR(TIME_TO_SEC(timediff(date_out,date_in))/3600) AS count_hero')
                        ->where('lc_id', '!=', 0)->whereNotNull('date_in')->whereNotNull('date_out')->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])->get();
        $leg = 0;
        $d10 = 0;
        $gru = 0;
        $ids = array();
        foreach($permits as $permit) {
            /*$date_in = new Carbon($permit->date_in);
            $date_out = new Carbon($permit->date_out);
            $diff = $date_in->diff($date_out);*/
            // Учитываем только те машины которые находились на территории более 2 часа
            if ($permit->count_hero > 2) { //$diff->h >= 2
                if ($permit->lc_id == 1) $leg += 1;
                if ($permit->lc_id == 2) $d10 += 1;
                if ($permit->lc_id == 3) $gru += 1;
                $ids[] = $permit->id;
            }
        }

        $ito = $d10 + $gru;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Свод грузовые для счета');

        $str = $company->short_en_name.". Отчет по пропускам за период с ".$from_date." по ".$to_date;
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(16)->setBold(true);

        $this->setCellValue($sheet, array('A3', 'B3', 'C3', 'D3'), array('Названия строк','до 10тонн', 'Грузовые', 'Общий итог'));

        $this->setCellValue($sheet, ['A4', 'B4', 'C4', 'D4'], [$company->short_en_name, $d10, $gru, $ito]);

        // Заголовки сделаем жирными
        $this->setBoldFontForColumns($sheet, true, 'A3', 'B3', 'C3', 'D3');

        // Вставляем авто размер для колонок
        $this->setAutoSizeColumn($sheet, true, 'A', 'B', 'C', 'D');

//        // Создаем новый шит - Перечень пропусков
        $permits = Permit::where(['company_id' => $company_id])
            ->selectRaw('id,gov_number,pr_number,mark_car,lc_id,date_in,date_out')
            ->where('lc_id', '!=', 0)->whereNotNull('date_in')->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])->get();

        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(1);
        $sheet->setTitle('Перечень пропусков');
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(16)->setBold(true);

        $this->setCellValue($sheet, ['A3', 'B3', 'C3', 'D3', 'E3', 'F3', 'G3'], ['Номер пропуска', 'Марка', 'Гос.номер', 'Номер прицепа', 'Груз.под','Дата заезда', 'Дата выезда']);

        // Вставляем авто размер для колонок
        $this->setAutoSizeColumn($sheet, true, 'A', 'B', 'C', 'D', 'E', 'F', 'G');

        $row_start = 3;
        $current_row = $row_start;
        foreach($permits as $key=>$permit){
            $current_row++;
            $sheet->insertNewRowBefore($row_start + $key+1, 1);
            $sheet->setCellValue('A'.$current_row,$permit->id);
            $sheet->setCellValue('B'.$current_row,$permit->mark_car);
            $sheet->setCellValue('C'.$current_row,$permit->gov_number);
            $sheet->setCellValue('D'.$current_row,$permit->pr_number);
            $sheet->setCellValue('E'.$current_row,$this->returnGrzName($permit->lc_id));
            //$sheet->setCellValue('F'.$current_row,$permit->count_hero); //$diff->h
            $sheet->setCellValue('F'.$current_row,$permit->date_in);
            $sheet->setCellValue('G'.$current_row,$permit->date_out);
        }

        // Выравниваем по левому краю
        $this->setHorizontal($sheet, Alignment::HORIZONTAL_LEFT, 'A');

        // Заголовки сделаем жирными
        $this->setBoldFontForColumns($sheet, true, 'A3', 'B3', 'C3', 'D3', 'E3', 'F3', 'G3');

        // Создаем новый шит - Свод легковые для счета
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(2);
        $sheet->setTitle('Свод легковые для счета');
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(16)->setBold(true);

        $this->setCellValue($sheet, ['A3', 'B3', 'C3'], ['Дата заезда', 'Количество пропусков', 'Свыше 10']);

        // Вставляем авто размер для колонок
        $this->setAutoSizeColumn($sheet, true, 'A', 'B', 'C');
        $legs = Permit::where(['company_id' => $company_id])
            ->selectRaw('DATE(`date_in`) as dt, COUNT(*) as cnt')
            ->where('lc_id', '=', 1)->whereNotNull('date_in')->whereNotNull('date_out')
            ->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])
            ->groupByRaw('DATE(date_in)')
            ->orderByRaw('DATE(date_in)')
            ->get();

        $row_start = 3;
        $current_row = $row_start;
        foreach($legs as $k=>$item){
            $current_row++;
            $sheet->insertNewRowBefore($row_start + $k+1, 1);
            $sheet->setCellValue('A'.$current_row,$item->dt);
            $sheet->setCellValue('B'.$current_row,$item->cnt);
            $cnt = ($item->cnt > 10) ? $item->cnt - 10 : 0;
            $sheet->setCellValue('C'.$current_row,$cnt);
        }

        // Выравниваем по левому краю
        $this->setHorizontal($sheet, Alignment::HORIZONTAL_LEFT, 'A');

        // Заголовки сделаем жирными
        $this->setBoldFontForColumns($sheet, true, 'A3', 'B3', 'C3');

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $filename = date('d.m.Y_H_i_s_').$company_id.".xlsx";

        // Подготовка папок для сохранение картинки
        $dir = '/reports/'. $company_id . '/' . date('Y-m').'/';
        if(!File::isDirectory(public_path(). $dir)){
            File::makeDirectory(public_path(). $dir, 0777, true);
        }

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    public function generateReportForKcell($data)
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];

        $company = Company::findOrFail($company_id);
        $permits = Permit::where(['company_id' => $company_id])
            ->selectRaw('DATE(`date_in`) as dt, SUM(CASE WHEN lc_id = 1 THEN 1 ELSE 0 END) as leg,SUM(CASE WHEN lc_id = 2 THEN 1 ELSE 0 END) as d10, SUM(CASE WHEN lc_id = 3 THEN 1 ELSE 0 END) as grz')
            ->where('lc_id', '!=', 0)->whereNotNull('date_in')->whereNotNull('date_out')
            ->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])
            ->groupByRaw('DATE(date_in)')
            ->orderByRaw('DATE(date_in)')
            ->get();

        $leg = 0;
        $d10 = 0;
        $gru = 0;
        $ids = array();
        foreach($permits as $permit) {
            $leg += ($permit->leg > 7) ? $permit->leg - 7 : 0;
            $d10 += ($permit->d10 > 7) ? $permit->d10 - 7 : 0;
            $gru += ($permit->grz > 7) ? $permit->grz - 7 : 0;
            $ids[] = $permit->id;
        }

        $ito = $leg + $d10 + $gru;
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Свод');

        $str = $company->short_en_name.". Отчет по пропускам за период с ".$from_date." по ".$to_date;
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(16)->setBold(true);

        $this->setCellValue($sheet, array('A3', 'B3', 'C3', 'D3', 'E3'), array('Названия строк', 'Легковые', 'до 10тонн', 'Грузовые', 'Общий итог'));

        $this->setCellValue($sheet, ['A4', 'B4', 'C4', 'D4', 'E4'], [$company->short_en_name, $leg, $d10, $gru, $ito]);

        // Заголовки сделаем жирными
        $this->setBoldFontForColumns($sheet, true, 'A3', 'B3', 'C3', 'D3', 'E3');

        // Вставляем авто размер для колонок
        $this->setAutoSizeColumn($sheet, true, 'A', 'B', 'C', 'D', 'E');

        // Создаем новый шит - Расшифровка счета
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(1);
        $sheet->setTitle('Расшифровка счета');
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(16)->setBold(true);

        $this->setCellValue($sheet, ['A3', 'B3', 'C3', 'D3'], ['Дата заезда', 'Количество Легковых > 7', 'Количество До 10тонн > 7', 'Количество Грузовые > 7']);

        // Вставляем авто размер для колонок
        $this->setAutoSizeColumn($sheet, true, 'A', 'B', 'C', 'D');

        $row_start = 3;
        $current_row = $row_start;
        for($i=0; $i<count($permits); $i++){
            $cnt_leg = ($permits[$i]->leg > 7) ? $permits[$i]->leg - 7 : 0;
            $cnt_d10 = ($permits[$i]->d10 > 7) ? $permits[$i]->d10 - 7 : 0;
            $cnt_grz = ($permits[$i]->grz > 7) ? $permits[$i]->grz - 7 : 0;
            if($cnt_leg == 0 && $cnt_d10 == 0 && $cnt_grz == 0) {
                continue;
            } else {
                $current_row++;
                $sheet->insertNewRowBefore($row_start + $i+1, 1);
                $sheet->setCellValue('A'.$current_row,$permits[$i]->dt);
                $sheet->setCellValue('B'.$current_row,$cnt_leg);
                $sheet->setCellValue('C'.$current_row,$cnt_d10);
                $sheet->setCellValue('D'.$current_row,$cnt_grz);
            }
        }

        // Выравниваем по левому краю
        $this->setHorizontal($sheet, Alignment::HORIZONTAL_LEFT, 'A');

        // Заголовки сделаем жирными
        $this->setBoldFontForColumns($sheet, true, 'A3', 'B3', 'C3', 'D3');

        // Создаем новый шит - Перечень пропусков
        $permits = Permit::where(['company_id' => $company_id])
            ->where('lc_id', '!=', 0)->whereNotNull('date_in')->whereNotNull('date_out')->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])->get();

        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(2);
        $sheet->setTitle('Перечень пропусков');
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(16)->setBold(true);

        $this->setCellValue($sheet, ['A3', 'B3', 'C3', 'D3', 'E3', 'F3', 'G3'], ['Номер пропуска', 'Марка', 'Гос.номер', 'Номер прицепа', 'Груз.под', 'Дата заезда', 'Дата выезда']);

        // Вставляем авто размер для колонок
        $this->setAutoSizeColumn($sheet, true, 'A', 'B', 'C', 'D', 'E', 'F', 'G');

        $row_start = 3;
        $current_row = $row_start;
        for($i=0; $i<count($permits); $i++){
            $current_row++;
            $sheet->insertNewRowBefore($row_start + $i+1, 1);
            $sheet->setCellValue('A'.$current_row,$permits[$i]->id);
            $sheet->setCellValue('B'.$current_row,$permits[$i]->mark_car);
            $sheet->setCellValue('C'.$current_row,$permits[$i]->gov_number);
            $sheet->setCellValue('D'.$current_row,$permits[$i]->pr_number);

            $sheet->setCellValue('E'.$current_row,$this->returnGrzName($permits[$i]->lc_id));
            $sheet->setCellValue('F'.$current_row,$permits[$i]->date_in);
            $sheet->setCellValue('G'.$current_row,$permits[$i]->date_out);
        }

        // Выравниваем по левому краю
        $this->setHorizontal($sheet, Alignment::HORIZONTAL_LEFT, 'A');

        // Заголовки сделаем жирными
        $this->setBoldFontForColumns($sheet, true, 'A3', 'B3', 'C3', 'D3', 'E3', 'F3', 'G3');

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $filename = date('d.m.Y_H_i_s_').$company_id.".xlsx";

        // Подготовка папок для сохранение картинки
        $dir = '/reports/'. $company_id . '/' . date('Y-m').'/';
        if(!File::isDirectory(public_path(). $dir)){
            File::makeDirectory(public_path(). $dir, 0777, true);
        }

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    public function generateReportForMechta($data)
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];

        $company = Company::findOrFail($company_id);
        $permits = Permit::where(['company_id' => $company_id])->where('lc_id', '>', 0)->whereNotNull('date_in')->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])->get();

        $leg = 0;
        $d10 = 0;
        $gru = 0;

        $ids = array();

        foreach($permits as $key=>$permit) {
            $date_in = substr($permit->date_in,0, 10);
            // Учитываем только те машины которые заехали между 18:00 и 08:00
            //dd($permit->date_in, $date_in." 18:00:00");
            if ($permit->date_in <= $date_in." 18:00:00" AND $permit->date_in >= $date_in." 08:00:00") {
                if ($permit->lc_id == 1) $leg += 1;
                if ($permit->lc_id == 2) $d10 += 1;
                if ($permit->lc_id == 3) $gru += 1;
                $ids[] = $permit->id;
            }
        }

        $ito = $leg + $d10 + $gru;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('СВОД');
        $str = $company->short_en_name.". Отчет по пропускам за период с ".$from_date." по ".$to_date;
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(16)->setBold(true);

        $this->setCellValue($sheet, array('A3', 'B3', 'C3', 'D3', 'E3'), array('Названия строк', 'Легковые', 'до 10тонн', 'Грузовые', 'Общий итог'));

        $this->setCellValue($sheet, ['A4', 'B4', 'C4', 'D4', 'E4'], [$company->short_en_name, $leg, $d10, $gru, $ito]);

        // Заголовки сделаем жирными
        $this->setBoldFontForColumns($sheet, true, 'A3', 'B3', 'C3', 'D3', 'E3');

        // Вставляем авто размер для колонок
        $this->setAutoSizeColumn($sheet, true, 'A', 'B', 'C', 'D', 'E');

        // Создаем новый шит - Всего пропусков
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(1);
        $sheet->setTitle('Всего пропусков');
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(16)->setBold(true);

        $this->setCellValue($sheet, ['A3', 'B3', 'C3', 'D3', 'E3', 'F3', 'G3'], ['Номер пропуска', 'Марка', 'Гос.номер', 'Номер прицепа', 'Груз.под', 'Дата заезда', 'Дата выезда']);

        // Вставляем авто размер для колонок
        $this->setAutoSizeColumn($sheet, true, 'A', 'B', 'C', 'D', 'E', 'F', 'G');

        $row_start = 3;
        $current_row = $row_start;
        for($i=0; $i<count($permits); $i++){
            $current_row++;
            $sheet->insertNewRowBefore($row_start + $i+1, 1);
            $sheet->setCellValue('A'.$current_row,$permits[$i]->id);
            $sheet->setCellValue('B'.$current_row,$permits[$i]->mark_car);
            $sheet->setCellValue('C'.$current_row,$permits[$i]->gov_number);
            $sheet->setCellValue('D'.$current_row,$permits[$i]->pr_number);
            $sheet->setCellValue('E'.$current_row,$this->returnGrzName($permits[$i]->lc_id));
            $sheet->setCellValue('F'.$current_row,$permits[$i]->date_in);
            $sheet->setCellValue('G'.$current_row,$permits[$i]->date_out);
            if (in_array($permits[$i]->id, $ids)) {
                $sheet->getStyle("A".$current_row.":G".$current_row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('01B050');
            } else {
                $sheet->getStyle("A".$current_row.":G".$current_row)->getFill()->setFillType(Fill::FILL_NONE);
            }
        }

        // Выравниваем по левому краю
        $this->setHorizontal($sheet, Alignment::HORIZONTAL_LEFT, 'A');

        // Заголовки сделаем жирными
        $this->setBoldFontForColumns($sheet, true, 'A3', 'B3', 'C3', 'D3', 'E3', 'F3', 'G3');

        // Создаем новый шит - Расшифровка оплаты
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(2);
        $sheet->setTitle('Расшифровка оплаты');
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(16)->setBold(true);

        $this->setCellValue($sheet, ['A3', 'B3', 'C3', 'D3', 'E3', 'F3', 'G3'], ['Номер пропуска', 'Марка', 'Гос.номер', 'Номер прицепа', 'Груз.под','Дата заезда', 'Дата выезда']);

        // Вставляем авто размер для колонок
        $this->setAutoSizeColumn($sheet, true, 'A', 'B', 'C', 'D', 'E', 'F', 'G');

        $row_start = 3;
        $current_row = $row_start;
        for($i=0; $i<count($permits); $i++){
            $date_in = substr($permits[$i]->date_in,0, 10);
            // Учитываем только те машины которые заехали между 18:00 и 08:00
            if ($permits[$i]->date_in <= $date_in." 18:00:00" AND $permits[$i]->date_in >= $date_in." 08:00:00") {
                $current_row++;
                $sheet->insertNewRowBefore($row_start + $i+1, 1);
                $sheet->setCellValue('A'.$current_row,$permits[$i]->id);
                $sheet->setCellValue('B'.$current_row,$permits[$i]->mark_car);
                $sheet->setCellValue('C'.$current_row,$permits[$i]->gov_number);
                $sheet->setCellValue('D'.$current_row,$permits[$i]->pr_number);
                $sheet->setCellValue('E'.$current_row,$this->returnGrzName($permits[$i]->lc_id));
                $sheet->setCellValue('F'.$current_row,$permits[$i]->date_in);
                $sheet->setCellValue('G'.$current_row,$permits[$i]->date_out);
            }
        }

        // Выравниваем по левому краю
        $this->setHorizontal($sheet, Alignment::HORIZONTAL_LEFT, 'A');

        // Заголовки сделаем жирными
        $this->setBoldFontForColumns($sheet, true, 'A3', 'B3', 'C3', 'D3', 'E3', 'F3', 'G3');

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $filename = date('d.m.Y_H_i_s_').$company_id.".xlsx";

        // Подготовка папок для сохранение картинки
        $dir = '/reports/'. $company_id . '/' . date('Y-m').'/';
        if(!File::isDirectory(public_path(). $dir)){
            File::makeDirectory(public_path(). $dir, 0777, true);
        }

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    public function generateReportForOther($data)
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];

        if($company_id == 0) {
            $permits = Permit::where('lc_id', '>', 0)->whereNotNull('date_in')->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])->get();
        } else {
            $company = Company::findOrFail($company_id);
            $permits = Permit::where(['company_id' => $company_id])->where('lc_id', '>', 0)->whereNotNull('date_in')->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])->get();
        }

        $leg = 0;
        $d10 = 0;
        $gru = 0;

        foreach($permits as $permit) {
            if ($permit->lc_id == 1) $leg += 1;
            if ($permit->lc_id == 2) $d10 += 1;
            if ($permit->lc_id == 3) $gru += 1;
        }

        $ito = $leg + $d10 + $gru;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('СВОД');
        $company_name = ($company_id == 0) ? 'Все' : $company->short_en_name;
        $str = $company_name.". Отчет по пропускам за период с ".$from_date." по ".$to_date;
        $sheet->mergeCells('A1:M1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(20)->setBold(true);

        $this->setCellValue($sheet, array('A2', 'B2', 'C2', 'D2', 'E2'), array('Названия строк', 'Легковые', 'до 10тонн', 'Грузовые', 'Общий итог'));

        $this->setCellValue($sheet, ['A3', 'B3', 'C3', 'D3', 'E3'], [$company_name, $leg, $d10, $gru, $ito]);

        // Заголовки сделаем жирными
        $this->setBoldFontForColumns($sheet, true, 'A2', 'B2', 'C2', 'D2', 'E2');

        // Вставляем авто размер для колонок
        $this->setAutoSizeColumn($sheet, true, 'A', 'B', 'C', 'D', 'E');

        // Создаем новый шит - Всего пропусков
        $spreadsheet->createSheet();
        $sheet = $spreadsheet->setActiveSheetIndex(1);
        $sheet->setTitle('Всего пропусков');

        $this->setCellValue($sheet, ['A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1'], ['Номер пропуска', 'Компания', 'Марка', 'Гос.номер', 'Номер прицепа', 'Груз.под', 'Дата заезда', 'Дата выезда']);

        // Вставляем авто размер для колонок
        $this->setAutoSizeColumn($sheet, true, 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');

        $row_start = 1;
        $current_row = $row_start;
        for($i=0; $i<count($permits); $i++){
            $current_row++;
            $sheet->insertNewRowBefore($row_start + $i+1, 1);
            $sheet->setCellValue('A'.$current_row,$permits[$i]->id);
            $sheet->setCellValue('B'.$current_row,$permits[$i]->company);
            $sheet->setCellValue('C'.$current_row,$permits[$i]->mark_car);
            $sheet->setCellValue('D'.$current_row,$permits[$i]->gov_number);
            $sheet->setCellValue('E'.$current_row,$permits[$i]->pr_number);
            $sheet->setCellValue('F'.$current_row, $this->returnGrzName($permits[$i]->lc_id));
            $sheet->setCellValue('G'.$current_row,$permits[$i]->date_in);
            $sheet->setCellValue('H'.$current_row,$permits[$i]->date_out);
        }

        // Выравниваем по левому краю
        $this->setHorizontal($sheet, Alignment::HORIZONTAL_LEFT, 'A');

        // Заголовки сделаем жирными
        $this->setBoldFontForColumns($sheet, true, 'A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1');

        // Выравниваем по левому краю
        $this->setHorizontal($sheet, Alignment::HORIZONTAL_LEFT, 'A');

        // Заголовки сделаем жирными
        $this->setBoldFontForColumns($sheet, true, 'A1', 'B1', 'C1', 'D1', 'E1', 'F1', 'G1', 'H1');

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $filename = date('d.m.Y_H_i_s_').$company_id.".xlsx";

        // Подготовка папок для сохранение картинки
        $dir = '/reports/'. $company_id . '/' . date('Y-m').'/';
        if(!File::isDirectory(public_path(). $dir)){
            File::makeDirectory(public_path(). $dir, 0777, true);
        }

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    // метод установливает авто размер в зависимости от заданного условия
    public function setAutoSizeColumn($sheet, $boolean = false, ...$columns)
    {
        foreach($columns as $column) {
            $sheet->getColumnDimension($column)->setAutoSize($boolean);
        }
    }

    // метод сделает жирными в зависимости от заданного условия
    public function setBoldFontForColumns($sheet, $boolean = false, ...$columns)
    {
        foreach($columns as $column) {
            $sheet->getStyle($column)->getFont()->setBold($boolean);
        }
    }

    // метод установливает выравнение (горизантально) по ячейке в зависимости от заданного условия
    public function setHorizontal($sheet, $position, ...$columns)
    {
        foreach($columns as $column) {
            $sheet->getStyle($column)->getAlignment()->setHorizontal($position);
        }
    }

    // метод установливает заданную значению в ячейку
    public function setCellValue($sheet, array $columns, array $values)
    {
        foreach($columns as $key=>$col) {
            $sheet->setCellValue($col, $values[$key]);
        }
    }

    public function returnGrzName($number)
    {
        if ($number == 1) {
            $grz = 'Легковые';
        } elseif($number == 2) {
            $grz = 'до 10тонн';
        }else {
            $grz = 'Грузовые';
        }
        return $grz;
    }
}
