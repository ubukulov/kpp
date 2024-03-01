<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContainerAddress;
use App\Models\ContainerLog;
use App\Models\ContainerStock;
use App\Models\Technique;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use File;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class WebcontController extends Controller
{
    public function stocks()
    {
        $stocks = ContainerStock::selectRaw('containers.number,container_address.name,container_address.title,container_stocks.*')
                    ->join('containers', 'containers.id', '=', 'container_stocks.container_id')
                    ->join('container_address', 'container_address.id', '=', 'container_stocks.container_address_id')
                    ->get();

        return view('admin.webcont.stocks', compact('stocks'));
    }

    public function logs()
    {
        return view('admin.webcont.logs');
    }

    public function getLogsForAdmin()
    {
        $logs = ContainerLog::orderBy('id', 'DESC')
            ->selectRaw('container_logs.*, users.full_name, techniques.name as technique')
            ->join('users', 'users.id', '=', 'container_logs.user_id')
            ->join('techniques', 'techniques.id', '=', 'container_logs.technique_id')
            ->paginate(100);
        return response()->json($logs);
    }

    public function search(Request $request)
    {
        $number = $request->input('number');
        $date1 = $request->input('date1');
        $date2 = $request->input('date2');
        if (is_null($number)) {
            $container_logs = ContainerLog::whereRaw("(container_logs.created_at >= ? AND container_logs.created_at <= ?)", [$date1." 00:00", $date2." 23:59"])
                ->selectRaw('container_logs.*, users.full_name, techniques.name as technique')
                ->join('users', 'users.id', '=', 'container_logs.user_id')
                ->join('techniques', 'techniques.id', '=', 'container_logs.technique_id')
                ->get();
        } else {
            $container_logs = ContainerLog::where("container_logs.container_number", "LIKE", "%".$number)
                ->selectRaw('container_logs.*, users.full_name, techniques.name as technique')
                ->whereRaw("(container_logs.created_at >= ? AND container_logs.created_at <= ?)", [$date1." 00:00", $date2." 23:59"])
                ->leftJoin('users', 'users.id', '=', 'container_logs.user_id')
                ->leftJoin('techniques', 'techniques.id', '=', 'container_logs.technique_id')
                ->get();
        }

        return response()->json($container_logs);
    }

    public function reports()
    {
        return view('admin.webcont.reports');
    }

    public function getReports(Request $request)
    {
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');

        $getStatsByUserAndTechniques = ContainerLog::whereDate('container_logs.created_at', '>=', $from_date)->whereDate('container_logs.created_at', '<=', $to_date)
            ->selectRaw('users.id as user_id,users.full_name,container_logs.*')
            ->join('users', 'users.id', '=', 'container_logs.user_id')
            ->whereIn('container_logs.action_type', ['put', 'pick', 'move'])
            ->orderBy('container_logs.created_at')
            ->get();

        /*$stats = [
            125 => [
                '2024-01-02' => [
                    'r' => 25, 'v' => 33, 'p' => 45
                ],
                '2024-01-03' => [
                    'r' => 25, 'v' => 33, 'p' => 45
                ]
            ],
            126 => [],
        ];*/
        $stats = [];
        foreach($getStatsByUserAndTechniques as $item) {
            $date = date('d', strtotime($item->created_at));
            $r = ($item->action_type == 'put')  ? 1 : 0;
            $v = ($item->action_type == 'pick') ? 1 : 0;

            if($item->action_type == 'move') {
                $address_from = ContainerAddress::whereName($item->address_from)->first();
                $address_to   = ContainerAddress::whereName($item->address_to)->first();

                // Перемещение между зонами
                $p = ($address_from->zone != $address_to->zone) ? 1 : 0;
            } else {
                $p = 0;
            }


            if(array_key_exists($item->user_id, $stats)) {
                if(array_key_exists($date, $stats[$item->user_id]['items'])) {
                    $stats[$item->user_id]['items'][$date]['r'] += $r;
                    $stats[$item->user_id]['items'][$date]['v'] += $v;
                    $stats[$item->user_id]['items'][$date]['p'] += $p;
                } else {
                    $stats[$item->user_id]['items'][$date]['r'] = $r;
                    $stats[$item->user_id]['items'][$date]['v'] = $v;
                    $stats[$item->user_id]['items'][$date]['p'] = $p;
                }
            } else {
                $stats[$item->user_id]['full_name'] = $item->full_name;
                $stats[$item->user_id]['items'][$date]['r'] = $r;
                $stats[$item->user_id]['items'][$date]['v'] = $v;
                $stats[$item->user_id]['items'][$date]['p'] = $p;
            }
        }

        $stats = array_values($stats);

        //dd($stats);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Отчет по крановым операциям');

        $str = "Отчет по крановым операциям за период: ".$from_date." - ".$to_date;
        $sheet->mergeCells('A1:AH1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(12)->setBold(true);

        $sheet->setCellValue('A2', '№');
        $sheet->setCellValue('B2', 'Ф.И.О');
        $sheet->setCellValue('C2', 'Операция');

        $sheet->getStyle("A2")->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle("B2")->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle("C2")->getFont()->setSize(12)->setBold(true);

        // Вставляем авто размер для колонок
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);

        $col_start = 4;
        $col_cur = 2;

        for($d = date('d', strtotime($from_date)); $d <= date('d', strtotime($to_date)); $d++){
            $current_col = $this->getLetter($col_start).$col_cur;
            $sheet->setCellValue($current_col, $d*1);
            $sheet->getStyle($current_col)->getFont()->setSize(12)->setBold(true);
            $sheet->getStyle($current_col)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col_start += 1;
        }

        $row_start = 2;
        $current_row = $row_start;
        foreach($stats as $key=>$item){
            $current_row++;
            $b = $current_row;

            $sheet->setCellValue('C'.$current_row, 'Размещение');

            $c  = $current_row + 1;
            $sheet->setCellValue('C'. $c, 'Выдача');

            $k = $c + 1;
            $sheet->setCellValue('C'. $k, 'Перемещение между зонами');

            $sheet->mergeCells('A'.$b.':A'.$k);
            $sheet->setCellValue('A'.$b,$key+1);

            $sheet->mergeCells('B'.$b.':B'.$k);
            $sheet->setCellValue('B'.$b, $item['full_name']);

            // Выравниваем по левому краю
            $sheet->getStyle('A'.$b)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle('A'.$b)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('B'.$b)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $col_start = 4;

            for($d = date('d', strtotime($from_date)); $d <= date('d', strtotime($to_date)); $d++){
                $current_col = $this->getLetter($col_start);
                $d = ($d < '10') ? '0'.$d : $d;
                if(array_key_exists($d, $item['items'])) {
                    $sheet->setCellValue($current_col.$current_row, $item['items'][$d]['r']);
                    $sheet->setCellValue($current_col.$c, $item['items'][$d]['v']);
                    $sheet->setCellValue($current_col.$k, $item['items'][$d]['p']);
                } else {
                    $sheet->setCellValue($current_col.$current_row, 0);
                    $sheet->setCellValue($current_col.$c, 0);
                    $sheet->setCellValue($current_col.$k, 0);
                }
                $sheet->getStyle($current_col.$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($current_col.$c)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($current_col.$k)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $col_start++;
            }

            $current_row += 2;
        }

        $current_col = $this->getLetter($col_start);
        $sheet->getStyle("A2:".$current_col.$current_row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        /*$sheet->getStyle("A2:".$current_col.$current_row)->applyFromArray(
            array(
                'borders' => array(
                    'allborders' => array(
                        'style' => Border::BORDER_THIN,
                        'color' => array('rgb' => 'DDDDDD')
                    )
                )
            )
        );*/

        $writer = new Xlsx($spreadsheet);
        $filename = "webcont_report_for_".date('d.m.Y_H_i_s_').".xlsx";

        // Подготовка папок для сохранение картинки
        $dir = '/temp/';

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    public function getLetter($number){
        $arr = [
            1 => 'A',2 => 'B',3 => 'C',4 => 'D',5 => 'E',6 => 'F',7 => 'G',8 => 'H',9 => 'I',10 => 'J',
            11 => 'K',12 => 'L',13 => 'M',14 => 'N',15 => 'O',16 => 'P',17 => 'Q',18 => 'R',19 => 'S',
            20 => 'T',21 => 'U',22 => 'V',23 => 'W',24 => 'X',25 => 'Y',26 => 'Z',27 => 'AA',28 => 'AB',
            29 => 'AC',30 => 'AD',31 => 'AE',32 => 'AF',33 => 'AG',34 => 'AH',35 => 'AI',36 => 'AJ',
            37 => 'AK',38 => 'AL',39 => 'AM',40 => 'AN',41 => 'AO',42 => 'AP',43 => 'AQ',44 => 'AR',
            45 => 'AS',46 => 'AT',47 => 'AU',48 => 'AV',49 => 'AW',50 => 'AX',51 => 'AY',52 => 'AZ',
            53 => 'BA',54 => 'BB',55 => 'BC',56 => 'BD',57 => 'BE',58 => 'BF',59 => 'BG',60 => 'BH',
            61 => 'BI',62 => 'BJ',63 => 'BK',64 => 'BL',65 => 'BM',66 => 'BN',67 => 'BO',68 => 'BP',
            69 => 'BQ',70 => 'BR',71 => 'BS',72 => 'BT',73 => 'BU',74 => 'BV',75 => 'BW',76 => 'BX',
            77 => 'BY',78 => 'BZ',79 => 'CA',80 => 'CB',81 => 'CC',82 => 'CD',83 => 'CE',84 => 'CF',
            85 => 'CG',86 => 'CH',87 => 'CI',88 => 'CJ',89 => 'CK',90 => 'CL',91 => 'CM',92 => 'CN',
            93 => 'CO',94 => 'CP',95 => 'CQ',96 => 'CR',97 => 'CS',98 => 'CT',99 => 'CU',100 => 'CV',
            101 => 'CW',102 => 'CY',103 => 'CZ'
        ];
        if(array_key_exists($number,$arr)){
            return $arr[$number];
        }
    }
}
