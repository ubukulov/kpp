<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Permit;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class WriteXls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'write:xls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];
        $company_id = $data['company_id'];

        $company = Company::findOrFail($company_id);
        $permits = Permit::where(['company_id' => $company_id, 'status' => 'printed'])
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
        $permits = Permit::where(['company_id' => $company_id, 'status' => 'printed'])
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
        $legs = Permit::where(['company_id' => $company_id, 'status' => 'printed'])
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
}
