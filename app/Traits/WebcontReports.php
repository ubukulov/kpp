<?php


namespace App\Traits;

use App\Models\ContainerLog;
use App\Models\TechniqueLog;
use App\Models\TechniqueStock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

trait WebcontReports
{
    public function getReports(Request $request)
    {
        $report_id = $request->input('report_id');
        switch ($report_id) {
            case 0:
                $path_file = $this->getReportsForCrane($request->all());
                break;
            case 1:
                $path_file = $this->getReportsForSlinger($request->all());
                break;
        }

        return $path_file;
    }

    public function getStatsToday(Request $request)
    {
        $data = $request->all();
        switch ($data['report_id']) {
            case 0:
                $path_file = $this->getStatsTodayForCrane($data);
                break;
            case 1:
                $path_file = $this->getStatsTodayForSlinger($data);
                break;
        }

        return $path_file;
    }

    public function getReportsForSlinger($data)
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];

        $result = ContainerLog::whereDate('container_logs.created_at', '>=', $from_date)->whereDate('container_logs.created_at', '<=', $to_date)
            ->selectRaw('users.id as user_id,users.full_name,container_logs.*')
            ->join('users', 'users.id', '=', 'container_logs.user_id')
            ->whereIn('container_logs.action_type', ['put', 'pick', 'move_another_zone', 'move'])
            ->whereNotNull('container_logs.slinger_ids')
            ->orderBy('container_logs.created_at')
            ->get();

        $slingers = [];

        foreach($result as $item) {
            $arr = explode(',', $item->slinger_ids);
            if(count($arr) == 1) {
                if($item->action_type == 'put') {
                    $address = $item->address_to;
                } elseif($item->action_type == 'pick') {
                    $address = $item->address_from;
                } elseif($item->action_type == 'move') {
                    $address = $item->address_to;
                } else {
                    if($item->address_from != 'buffer' && $item->address_to == 'BUFFER | buffer') {
                        $address = $item->address_from;
                    } else {
                        $address = $item->address_to;
                    }
                }

                $add = explode('-', $address);
                $zone = $add[0];

                $slingers[] = [
                    'user_id' => $arr[0],
                    'isOne' => ($zone == 'POPOLE' || $zone == '30R') ? true : false,
                    'address_from' => $item->address_from,
                    'address_to' => $item->address_to,
                    'action_type' => $item->action_type,
                    'created_at' => $item->created_at->format('Y-m-d H:i:s')
                ];
            } else {
                foreach ($arr as $s_id) {
                    $slingers[] = [
                        'user_id' => $s_id,
                        'isOne' => false,
                        'address_from' => $item->address_from,
                        'address_to' => $item->address_to,
                        'action_type' => $item->action_type,
                        'created_at' => $item->created_at->format('Y-m-d H:i:s')
                    ];
                }
            }
        }

        $stats = [];

        $from_d = (int) date('d', strtotime($from_date));
        $to_d   = (int) date('d', strtotime($to_date));

        foreach($slingers as $slinger) {
            $date = (int) date('d', strtotime($slinger['created_at']));

            if($slinger['action_type'] == 'put' && $slinger['isOne'] == true) {
                $r = 1.5;
            } elseif($slinger['action_type'] == 'put' && $slinger['isOne'] == false) {
                $r = 1;
            } else {
                $r = 0;
            }

            if($slinger['action_type'] == 'pick' && $slinger['isOne'] == true) {
                $v = 1.5;
            } elseif($slinger['action_type'] == 'pick' && $slinger['isOne'] == false) {
                $v = 1;
            } else {
                $v = 0;
            }

            if($slinger['action_type'] == 'move_another_zone' && $slinger['address_from'] != 'buffer' && $slinger['address_to'] == 'BUFFER | buffer' && $slinger['isOne'] == true) {
                $vp = 1.5;
            } elseif ($slinger['action_type'] == 'move_another_zone' && $slinger['address_from'] != 'buffer' && $slinger['address_to'] == 'BUFFER | buffer' && $slinger['isOne'] == false) {
                $vp = 1;
            } else {
                $vp = 0;
            }

            if($slinger['action_type'] == 'move' && $slinger['address_from'] == 'buffer' && $slinger['address_to'] != 'BUFFER | buffer' && $slinger['isOne'] == true) {
                $pp = 1.5;
            } elseif($slinger['action_type'] == 'move' && $slinger['address_from'] == 'buffer' && $slinger['address_to'] != 'BUFFER | buffer' && $slinger['isOne'] == false) {
                $pp = 1;
            } else {
                $pp = 0;
            }

            if(array_key_exists($slinger['user_id'], $stats)) {
                if(array_key_exists($date, $stats[$slinger['user_id']]['items'])) {
                    $stats[$slinger['user_id']]['items'][$date]['r'] += $r;
                    $stats[$slinger['user_id']]['items'][$date]['v'] += $v;
                    $stats[$slinger['user_id']]['items'][$date]['vp'] += $vp;
                    $stats[$slinger['user_id']]['items'][$date]['pp'] += $pp;
                } else {
                    $stats[$slinger['user_id']]['items'][$date]['r'] = $r;
                    $stats[$slinger['user_id']]['items'][$date]['v'] = $v;
                    $stats[$slinger['user_id']]['items'][$date]['vp'] = $vp;
                    $stats[$slinger['user_id']]['items'][$date]['pp'] = $vp;
                }
            } else {
                $stats[$slinger['user_id']]['user_id'] = $slinger['user_id'];
                $stats[$slinger['user_id']]['items'][$date]['r'] = $r;
                $stats[$slinger['user_id']]['items'][$date]['v'] = $v;
                $stats[$slinger['user_id']]['items'][$date]['vp'] = $vp;
                $stats[$slinger['user_id']]['items'][$date]['pp'] = $pp;
            }
        }

        $users = User::whereIn('id', array_keys($stats))->get();

        foreach($users as $user) {
            if(array_key_exists($user->id, $stats)) {
                $stats[$user->id]['full_name'] = $user->full_name;
            }
        }

        $stats = array_values($stats);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Отчет по крановым операциям');

        $str = "Стропальщики: Отчет по крановым операциям за период: ".$from_date." - ".$to_date;
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

        for($d = $from_d; $d <= $to_d; $d++){
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
            $sheet->setCellValue('C'. $k, 'Выдача Перемещение');

            $g = $k + 1;

            $sheet->setCellValue('C'. $g, 'Прием Перемещение');

            $sheet->mergeCells('A'.$b.':A'.$g);
            $sheet->setCellValue('A'.$b,$key+1);

            $sheet->mergeCells('B'.$b.':B'.$g);
            $sheet->setCellValue('B'.$b, $item['full_name']);

            // Выравниваем по левому краю
            $sheet->getStyle('A'.$b)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle('A'.$b)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('B'.$b)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $col_start = 4;

            for($d = $from_d; $d <= $to_d; $d++){
                $current_col = $this->getLetter($col_start);
                if(array_key_exists($d, $item['items'])) {
                    $sheet->setCellValue($current_col.$current_row, $item['items'][$d]['r']);
                    $sheet->setCellValue($current_col.$c, $item['items'][$d]['v']);
                    $sheet->setCellValue($current_col.$k, $item['items'][$d]['vp']);
                    $sheet->setCellValue($current_col.$g, $item['items'][$d]['pp']);
                } else {
                    $sheet->setCellValue($current_col.$current_row, 0);
                    $sheet->setCellValue($current_col.$c, 0);
                    $sheet->setCellValue($current_col.$k, 0);
                    $sheet->setCellValue($current_col.$g, 0);
                }
                $sheet->getStyle($current_col.$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($current_col.$c)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($current_col.$k)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($current_col.$g)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $col_start++;
            }

            $current_row += 3;
        }

        $current_col = $this->getLetter($col_start);
        $sheet->getStyle("A2:".$current_col.$current_row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $writer = new Xlsx($spreadsheet);
        $filename = "webcont_report_for_slinger_".date('d.m.Y_H_i_s_').".xlsx";

        // Подготовка папок для сохранение картинки
        $dir = '/temp/';

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    public function getReportsForCrane($data)
    {
        $from_date = $data['from_date'];
        $to_date = $data['to_date'];

        $getStatsByUserAndTechniques = ContainerLog::whereDate('container_logs.created_at', '>=', $from_date)->whereDate('container_logs.created_at', '<=', $to_date)
            ->selectRaw('users.id as user_id,users.full_name,container_logs.*')
            ->join('users', 'users.id', '=', 'container_logs.user_id')
            ->whereIn('container_logs.action_type', ['put', 'pick', 'move_another_zone', 'move'])
            ->orderBy('container_logs.created_at')
            ->get();

        $stats = [];

        $from_d = (int) date('d', strtotime($from_date));
        $to_d   = (int) date('d', strtotime($to_date));

        foreach($getStatsByUserAndTechniques as $item) {
            $date = (int) date('d', strtotime($item->created_at));
            $r = ($item->action_type == 'put')  ? 1 : 0;
            $v = ($item->action_type == 'pick') ? 1 : 0;

            /*if($item->action_type == 'move') {
                $address_from = ContainerAddress::whereName($item->address_from)->first();
                $address_to   = ContainerAddress::whereName($item->address_to)->first();

                // Перемещение между зонами
                $p = ($address_from->zone != $address_to->zone) ? 1 : 0;
            } else {
                $p = 0;
            }*/

            if($item->action_type == 'move_another_zone' && $item->address_to == 'BUFFER | buffer') {
                $vp = 1;
            } else {
                $vp = 0;
            }

            if($item->action_type == 'move' && $item->address_from == 'buffer') {
                $pp = 1;
            } else {
                $pp = 0;
            }


            if(array_key_exists($item->user_id, $stats)) {
                if(array_key_exists($date, $stats[$item->user_id]['items'])) {
                    $stats[$item->user_id]['items'][$date]['r'] += $r;
                    $stats[$item->user_id]['items'][$date]['v'] += $v;
                    $stats[$item->user_id]['items'][$date]['vp'] += $vp;
                    $stats[$item->user_id]['items'][$date]['pp'] += $pp;
                } else {
                    $stats[$item->user_id]['items'][$date]['r'] = $r;
                    $stats[$item->user_id]['items'][$date]['v'] = $v;
                    $stats[$item->user_id]['items'][$date]['vp'] = $vp;
                    $stats[$item->user_id]['items'][$date]['pp'] = $vp;
                }
            } else {
                $stats[$item->user_id]['full_name'] = $item->full_name;
                $stats[$item->user_id]['items'][$date]['r'] = $r;
                $stats[$item->user_id]['items'][$date]['v'] = $v;
                $stats[$item->user_id]['items'][$date]['vp'] = $vp;
                $stats[$item->user_id]['items'][$date]['pp'] = $pp;
            }
        }

        $stats = array_values($stats);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Отчет по крановым операциям');

        $str = "Крановщики: Отчет по крановым операциям за период: ".$from_date." - ".$to_date;
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

        for($d = $from_d; $d <= $to_d; $d++){
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
            $sheet->setCellValue('C'. $k, 'Выдача Перемещение');

            $g = $k + 1;

            $sheet->setCellValue('C'. $g, 'Прием Перемещение');

            $sheet->mergeCells('A'.$b.':A'.$g);
            $sheet->setCellValue('A'.$b,$key+1);

            $sheet->mergeCells('B'.$b.':B'.$g);
            $sheet->setCellValue('B'.$b, $item['full_name']);

            // Выравниваем по левому краю
            $sheet->getStyle('A'.$b)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->getStyle('A'.$b)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle('B'.$b)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $col_start = 4;

            for($d = $from_d; $d <= $to_d; $d++){
                $current_col = $this->getLetter($col_start);
                if(array_key_exists($d, $item['items'])) {
                    $sheet->setCellValue($current_col.$current_row, $item['items'][$d]['r']);
                    $sheet->setCellValue($current_col.$c, $item['items'][$d]['v']);
                    $sheet->setCellValue($current_col.$k, $item['items'][$d]['vp']);
                    $sheet->setCellValue($current_col.$g, $item['items'][$d]['pp']);
                } else {
                    $sheet->setCellValue($current_col.$current_row, 0);
                    $sheet->setCellValue($current_col.$c, 0);
                    $sheet->setCellValue($current_col.$k, 0);
                    $sheet->setCellValue($current_col.$g, 0);
                }
                $sheet->getStyle($current_col.$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($current_col.$c)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($current_col.$k)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($current_col.$g)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $col_start++;
            }

            $current_row += 3;
        }

        $current_col = $this->getLetter($col_start);
        $sheet->getStyle("A2:".$current_col.$current_row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $writer = new Xlsx($spreadsheet);
        $filename = "webcont_report_for_crane_".date('d.m.Y_H_i_s_').".xlsx";

        // Подготовка папок для сохранение картинки
        $dir = '/temp/';

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    public function getDetail(Request $request)
    {
        $data = $request->all();
        $user = User::findOrFail($data['user_id']);

        if($user->hasRole('kt-crane')) {
            // крановщики
            $container_logs = ContainerLog::where(['container_logs.user_id' => $user->id])
                ->selectRaw('container_logs.*')
                ->join('users', 'users.id', 'container_logs.user_id')
                ->whereDate('container_logs.created_at', $data['from_date'])
                ->whereIn('container_logs.action_type', ['put', 'pick', 'move', 'move_another_zone'])
                ->whereNotNull('container_logs.slinger_ids')
                ->get();

            $arr = [];

            foreach($container_logs as $log) {
                $user_arr = explode(',', $log->slinger_ids);
                if(count($user_arr) == 2) {
                    $slingers = [];
                    $result = User::whereIn('id', explode(',', $log->slinger_ids))->get();
                    foreach($result as $item) {
                        $slingers[] = $item->full_name;
                    }
                    $log['slingers'] = implode(',', $slingers);
                    $arr[] = $log;
                } else {
                    $sling = User::findOrFail($log->slinger_ids);
                    $log['slingers'] = $sling->full_name;
                    $arr[] = $log;
                }
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Детализация. Крановые операция');

            $sheet->mergeCells('A1:H1');
            $sheet->setCellValue('A1', "Крановщик: ".$user->full_name);
            $sheet->getStyle("A1")->getFont()->setSize(12)->setBold(true);
            $sheet->getStyle("A1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A2:H2');
            $sheet->setCellValue('A2', "Детализация: Отчет по крановым операциям за: ". $data['from_date']);
            $sheet->getStyle("A2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('G3:H3');
            $sheet->setCellValue('G3', "Транзакция");
            $sheet->getStyle("G3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G3")->getFont()->setSize(12)->setBold(true);

            $sheet->setCellValue('A4', 'Номер операции');
            $sheet->setCellValue('B4', 'Номер контейнера');
            $sheet->setCellValue('C4', 'Откуда');
            $sheet->setCellValue('D4', 'Куда');
            $sheet->setCellValue('E4', 'Стропальщики');
            $sheet->setCellValue('F4', 'Тип операции');
            $sheet->setCellValue('G4', 'Начало');
            $sheet->setCellValue('H4', 'Конец');
            $sheet->getStyle("A4:H4")->getFont()->setSize(12)->setBold(true);


            $sheet->getStyle("A2")->getFont()->setSize(12)->setBold(true);
            $sheet->getStyle("B2")->getFont()->setSize(12)->setBold(true);
            $sheet->getStyle("C2")->getFont()->setSize(12)->setBold(true);

            // Вставляем авто размер для колонок
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setAutoSize(true);

            $row_start = 4;
            $current_row = $row_start;
            foreach($arr as $log){
                $current_row++;

                $sheet->setCellValue('A'.$current_row, $log->id);
                $sheet->setCellValue('B'.$current_row, $log->container_number);
                $sheet->setCellValue('C'.$current_row, $log->address_from);
                $sheet->setCellValue('D'.$current_row, $log->address_to);
                $sheet->setCellValue('E'.$current_row, $log->slingers);
                $sheet->setCellValue('F'.$current_row, $this->getMoveTypeName($log));
                $sheet->setCellValue('G'.$current_row, date('H:i:s', strtotime($log->start_date)));
                $sheet->setCellValue('H'.$current_row, date('H:i:s', strtotime($log->transaction_date)));

                $sheet->getStyle("A".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            $sheet->getStyle("A1:H".$current_row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        } else {
            // стропальщики
            $container_logs = ContainerLog::whereDate('container_logs.created_at', $data['from_date'])
                ->selectRaw('container_logs.*')
                ->join('users', 'users.id', 'container_logs.user_id')
                ->whereIn('container_logs.action_type', ['put', 'pick', 'move', 'move_another_zone'])
                ->whereNotNull('container_logs.slinger_ids')
                ->where('container_logs.slinger_ids', 'like', '%' . $user->id . '%')
                ->get();

            $arr = [];

            foreach($container_logs as $log) {
                $user_arr = explode(',', $log->slinger_ids);
                if(count($user_arr) == 2) {
                    $slingers = [];
                    $result = User::whereIn('id', explode(',', $log->slinger_ids))->get();
                    foreach($result as $item) {
                        if($item->id == $user->id) {
                            $slingers[] = $item->full_name;
                        }
                    }
                    $log['slingers'] = $user->full_name;
                    $log['isOne'] = false;
                    $arr[] = $log;
                } else {
                    $sling = User::findOrFail($log->slinger_ids);
                    $log['slingers'] = $sling->full_name;
                    $log['isOne'] = true;
                    $arr[] = $log;
                }
                $crane = User::findOrFail($log->user_id);
                $log['crane'] = $crane->full_name;
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Детализация. Крановые операция');

            $sheet->mergeCells('A1:I1');
            $sheet->setCellValue('A1', "Стропальщик: ".$user->full_name);
            $sheet->getStyle("A1")->getFont()->setSize(12)->setBold(true);
            $sheet->getStyle("A1")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A2:I2');
            $sheet->setCellValue('A2', "Детализация: Отчет по крановым операциям за: ". $data['from_date']);
            $sheet->getStyle("A2")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('H3:I3');
            $sheet->setCellValue('H3', "Транзакция");
            $sheet->getStyle("H3")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H3")->getFont()->setSize(12)->setBold(true);

            $sheet->setCellValue('A4', 'Номер операции');
            $sheet->setCellValue('B4', 'Номер контейнера');
            $sheet->setCellValue('C4', 'Откуда');
            $sheet->setCellValue('D4', 'Куда');
            $sheet->setCellValue('E4', 'Крановщик');
            $sheet->setCellValue('F4', 'Коэффициент');
            $sheet->setCellValue('G4', 'Тип операции');
            $sheet->setCellValue('H4', 'Начало');
            $sheet->setCellValue('I4', 'Конец');
            $sheet->getStyle("A4:I4")->getFont()->setSize(12)->setBold(true);

            $sheet->getStyle("A2")->getFont()->setSize(12)->setBold(true);
            $sheet->getStyle("B2")->getFont()->setSize(12)->setBold(true);
            $sheet->getStyle("C2")->getFont()->setSize(12)->setBold(true);

            // Вставляем авто размер для колонок
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setAutoSize(true);
            $sheet->getColumnDimension('I')->setAutoSize(true);

            $row_start = 4;
            $current_row = $row_start;
            foreach($arr as $log){
                $current_row++;

                $sheet->setCellValue('A'.$current_row, $log->id);
                $sheet->setCellValue('B'.$current_row, $log->container_number);
                $sheet->setCellValue('C'.$current_row, $log->address_from);
                $sheet->setCellValue('D'.$current_row, $log->address_to);
                $sheet->setCellValue('E'.$current_row, $log->crane);
                if($log->isOne) {
                    $sheet->setCellValue('F'.$current_row, 1.5);
                } else {
                    $sheet->setCellValue('F'.$current_row, 1);
                }

                $sheet->setCellValue('G'.$current_row, $this->getMoveTypeName($log));
                $sheet->setCellValue('H'.$current_row, date('H:i:s', strtotime($log->start_date)));
                $sheet->setCellValue('I'.$current_row, date('H:i:s', strtotime($log->transaction_date)));

                $sheet->getStyle("A".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            $sheet->getStyle("A1:I".$current_row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }


        $writer = new Xlsx($spreadsheet);
        $filename = "webcont_report_for__".date('d.m.Y_H_i_s_').".xlsx";

        // Подготовка папок для сохранение картинки
        $dir = '/temp/';

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    public function getStatsTodayForCrane($data)
    {
        $from_date = $data['from_date'];

        $getStatsByUserAndTechniques = ContainerLog::whereDate('container_logs.created_at', $data['from_date'])
            ->selectRaw('users.id as user_id,users.full_name,container_logs.*')
            ->join('users', 'users.id', '=', 'container_logs.user_id')
            ->whereIn('container_logs.action_type', ['put', 'pick', 'move_another_zone', 'move'])
            ->orderBy('container_logs.created_at')
            ->get();

        $stats = [];

        foreach($getStatsByUserAndTechniques as $item) {
            $r = ($item->action_type == 'put')  ? 1 : 0;
            $v = ($item->action_type == 'pick') ? 1 : 0;

            if($item->action_type == 'move_another_zone' && $item->address_to == 'BUFFER | buffer') {
                $vp = 1;
            } else {
                $vp = 0;
            }

            if($item->action_type == 'move' && $item->address_from == 'buffer') {
                $pp = 1;
            } else {
                $pp = 0;
            }


            if(array_key_exists($item->user_id, $stats)) {
                $stats[$item->user_id]['r'] += $r;
                $stats[$item->user_id]['v'] += $v;
                $stats[$item->user_id]['vp'] += $vp;
                $stats[$item->user_id]['pp'] += $pp;
            } else {
                $stats[$item->user_id]['full_name'] = $item->full_name;
                $stats[$item->user_id]['r'] = $r;
                $stats[$item->user_id]['v'] = $v;
                $stats[$item->user_id]['vp'] = $vp;
                $stats[$item->user_id]['pp'] = $pp;
            }
        }

        $stats = array_values($stats);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Отчет по крановым операциям');

        $str = "Отчет по контейнерам крановщиков и автокрановщиков за ".$from_date;
        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', $str);
        $sheet->getStyle("A3")->getFont()->setSize(12)->setBold(true);

        $sheet->mergeCells('A5:A6');
        $sheet->setCellValue('A5', "№пп");

        $sheet->mergeCells('B5:B6');
        $sheet->setCellValue('B5', "ФИО");

        $sheet->mergeCells('C5:G5');
        $sheet->setCellValue('C5', "Количество контейнеров");
        $sheet->getStyle("C5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('H5:H6');
        $sheet->setCellValue('H5', "Подпись");
        $sheet->getColumnDimension('H')->setWidth(350.0);

        $sheet->setCellValue('C6', 'Размещение');
        $sheet->setCellValue('D6', 'Выдача');
        $sheet->setCellValue('E6', 'Выдача Перем');
        $sheet->setCellValue('F6', 'Прием Перем');
        $sheet->setCellValue('G6', 'ИТОГО');

        // Вставляем авто размер для колонок
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);

        $row_start = 6;
        $current_row = $row_start;
        foreach($stats as $key=>$item){
            $current_row++;
            $sheet->setCellValue('A'.$current_row, $key+1);
            $sheet->setCellValue('B'.$current_row, $item['full_name']);
            $sheet->setCellValue('C'.$current_row, $item['r']);
            $sheet->setCellValue('D'.$current_row, $item['v']);
            $sheet->setCellValue('E'.$current_row, $item['vp']);
            $sheet->setCellValue('F'.$current_row, $item['pp']);
            $sum = $item['r'] + $item['v'] + $item['vp'] + $item['pp'];
            $sheet->setCellValue('G'.$current_row, $sum);

            $sheet->getStyle("A".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getStyle("A5:H".$current_row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $writer = new Xlsx($spreadsheet);
        $filename = "webcont_report_for_crane_today_".date('d.m.Y_H_i_s_').".xlsx";

        // Подготовка папок для сохранение картинки
        $dir = '/temp/';

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    public function getStatsTodayForSlinger($data)
    {
        $from_date = $data['from_date'];
        $result = ContainerLog::whereDate('container_logs.created_at', $data['from_date'])
            ->selectRaw('users.id as user_id,users.full_name,container_logs.*')
            ->join('users', 'users.id', '=', 'container_logs.user_id')
            ->whereIn('container_logs.action_type', ['put', 'pick', 'move_another_zone', 'move'])
            ->whereNotNull('container_logs.slinger_ids')
            ->orderBy('container_logs.created_at')
            ->get();

        $slingers = [];

        foreach($result as $item) {
            $arr = explode(',', $item->slinger_ids);
            if(count($arr) == 1) {
                if($item->action_type == 'put') {
                    $address = $item->address_to;
                } elseif($item->action_type == 'pick') {
                    $address = $item->address_from;
                } elseif($item->action_type == 'move') {
                    $address = $item->address_to;
                } else {
                    if($item->address_from != 'buffer' && $item->address_to == 'BUFFER | buffer') {
                        $address = $item->address_from;
                    } else {
                        $address = $item->address_to;
                    }
                }

                $add = explode('-', $address);
                $zone = $add[0];

                $slingers[] = [
                    'user_id' => $arr[0],
                    'isOne' => ($zone == 'POPOLE' || $zone == '30R') ? true : false,
                    'address_from' => $item->address_from,
                    'address_to' => $item->address_to,
                    'action_type' => $item->action_type,
                    'created_at' => $item->created_at->format('Y-m-d H:i:s')
                ];
            } else {
                foreach ($arr as $s_id) {
                    $slingers[] = [
                        'user_id' => $s_id,
                        'isOne' => false,
                        'address_from' => $item->address_from,
                        'address_to' => $item->address_to,
                        'action_type' => $item->action_type,
                        'created_at' => $item->created_at->format('Y-m-d H:i:s')
                    ];
                }
            }
        }

        $stats = [];

        foreach($slingers as $slinger) {
            if($slinger['action_type'] == 'put' && $slinger['isOne'] == true) {
                $r = 1.5;
            } elseif($slinger['action_type'] == 'put' && $slinger['isOne'] == false) {
                $r = 1;
            } else {
                $r = 0;
            }

            if($slinger['action_type'] == 'pick' && $slinger['isOne'] == true) {
                $v = 1.5;
            } elseif($slinger['action_type'] == 'pick' && $slinger['isOne'] == false) {
                $v = 1;
            } else {
                $v = 0;
            }

            if($slinger['action_type'] == 'move_another_zone' && $slinger['address_from'] != 'buffer' && $slinger['address_to'] == 'BUFFER | buffer' && $slinger['isOne'] == true) {
                $vp = 1.5;
            } elseif ($slinger['action_type'] == 'move_another_zone' && $slinger['address_from'] != 'buffer' && $slinger['address_to'] == 'BUFFER | buffer' && $slinger['isOne'] == false) {
                $vp = 1;
            } else {
                $vp = 0;
            }

            if($slinger['action_type'] == 'move' && $slinger['address_from'] == 'buffer' && $slinger['address_to'] != 'BUFFER | buffer' && $slinger['isOne'] == true) {
                $pp = 1.5;
            } elseif($slinger['action_type'] == 'move' && $slinger['address_from'] == 'buffer' && $slinger['address_to'] != 'BUFFER | buffer' && $slinger['isOne'] == false) {
                $pp = 1;
            } else {
                $pp = 0;
            }

            if(array_key_exists($slinger['user_id'], $stats)) {
                $stats[$slinger['user_id']]['r'] += $r;
                $stats[$slinger['user_id']]['v'] += $v;
                $stats[$slinger['user_id']]['vp'] += $vp;
                $stats[$slinger['user_id']]['pp'] += $pp;
            } else {
                $stats[$slinger['user_id']]['user_id'] = $slinger['user_id'];
                $stats[$slinger['user_id']]['r'] = $r;
                $stats[$slinger['user_id']]['v'] = $v;
                $stats[$slinger['user_id']]['vp'] = $vp;
                $stats[$slinger['user_id']]['pp'] = $pp;
            }
        }

        $users = User::whereIn('id', array_keys($stats))->get();

        foreach($users as $user) {
            if(array_key_exists($user->id, $stats)) {
                $stats[$user->id]['full_name'] = $user->full_name;
            }
        }

        $stats = array_values($stats);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Отчет по крановым операциям');

        $str = "Отчет по контейнерам стропальщиков за ".$from_date;
        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', $str);
        $sheet->getStyle("A3")->getFont()->setSize(12)->setBold(true);

        $sheet->mergeCells('A5:A6');
        $sheet->setCellValue('A5', "№пп");

        $sheet->mergeCells('B5:B6');
        $sheet->setCellValue('B5', "ФИО");

        $sheet->mergeCells('C5:G5');
        $sheet->setCellValue('C5', "Количество контейнеров");
        $sheet->getStyle("C5")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('H5:H6');
        $sheet->setCellValue('H5', "Подпись");
        $sheet->getColumnDimension('H')->setWidth(350.0);

        $sheet->setCellValue('C6', 'Размещение');
        $sheet->setCellValue('D6', 'Выдача');
        $sheet->setCellValue('E6', 'Выдача Перем');
        $sheet->setCellValue('F6', 'Прием Перем');
        $sheet->setCellValue('G6', 'ИТОГО');

        // Вставляем авто размер для колонок
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
        $sheet->getColumnDimension('C')->setAutoSize(true);
        $sheet->getColumnDimension('D')->setAutoSize(true);
        $sheet->getColumnDimension('E')->setAutoSize(true);
        $sheet->getColumnDimension('F')->setAutoSize(true);
        $sheet->getColumnDimension('G')->setAutoSize(true);
        $sheet->getColumnDimension('H')->setAutoSize(true);

        $row_start = 6;
        $current_row = $row_start;
        foreach($stats as $key=>$item){
            $current_row++;
            $sheet->setCellValue('A'.$current_row, $key+1);
            $sheet->setCellValue('B'.$current_row, $item['full_name']);
            $sheet->setCellValue('C'.$current_row, $item['r']);
            $sheet->setCellValue('D'.$current_row, $item['v']);
            $sheet->setCellValue('E'.$current_row, $item['vp']);
            $sheet->setCellValue('F'.$current_row, $item['pp']);
            $sum = $item['r'] + $item['v'] + $item['vp'] + $item['pp'];
            $sheet->setCellValue('G'.$current_row, $sum);

            $sheet->getStyle("A".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getStyle("A5:H".$current_row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        $writer = new Xlsx($spreadsheet);
        $filename = "webcont_report_for_slinger_today_".date('d.m.Y_H_i_s_').".xlsx";

        // Подготовка папок для сохранение картинки
        $dir = '/temp/';

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }

    public function getReportsCar(Request $request)
    {
        $data = $request->all();
        $from_date = $data['from_date'];
        if($data['report_id'] == 0) {
            $technique_stocks = TechniqueStock::where('technique_stocks.status', '!=', 'exit_pass')->where('technique_stocks.status', '!=', 'incoming')/*whereDate('technique_stocks.created_at', '>=', $from_date)*/
                ->selectRaw('technique_stocks.*, companies.short_en_name, technique_places.name as technique_place_name')
                ->join('companies', 'companies.id', '=', 'technique_stocks.company_id')
                ->join('technique_places', 'technique_places.id', '=', 'technique_stocks.technique_place_id')
                //->where('technique_stocks.status', '!=', 'shipped')
                ->get();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Отчет по остаткам авто');

            $sheet->setCellValue('A1', '№');
            $sheet->setCellValue('B1', 'Контрагент');
            $sheet->setCellValue('C1', 'Вин Код');
            $sheet->setCellValue('D1', 'Модель');
            $sheet->setCellValue('E1', 'Цвет');
            $sheet->setCellValue('F1', 'Дата завоза');
            $sheet->setCellValue('G1', 'Зона хранения');

            // Вставляем авто размер для колонок
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);

            $row_start = 1;
            $current_row = $row_start;
            foreach($technique_stocks as $key=>$item){
                $current_row++;
                $sheet->setCellValue('A'.$current_row, $key+1);
                $sheet->setCellValue('B'.$current_row, $item->short_en_name);
                $sheet->setCellValue('C'.$current_row, $item->vin_code);
                $sheet->setCellValue('D'.$current_row, $item->mark);
                $sheet->setCellValue('E'.$current_row, $item->color);
                $sheet->setCellValue('F'.$current_row, $item->created_at);
                $sheet->setCellValue('G'.$current_row, $item->technique_place_name);

                $sheet->getStyle("A".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            $sheet->getStyle("A1:G".$current_row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $writer = new Xlsx($spreadsheet);
            $filename = "webcont_report_for_cars_today_".date('d.m.Y_H_i_s_').".xlsx";

            // Подготовка папок для сохранение картинки
            $dir = '/temp/';

            $path_to_file = $dir.$filename;
            $writer->save(public_path() . $path_to_file);

            return $path_to_file;
        } else {
            $technique_logs = TechniqueLog::whereIn('technique_logs.operation_type', ['received', 'completed'])
                ->selectRaw('technique_logs.*, spines.created_at as spineDate')
                ->leftJoin('spines', 'spines.spine_number', '=', 'technique_logs.spine_number')
                ->whereDate('technique_logs.created_at', '>=', $from_date)
                ->orderBy('technique_logs.id')
                ->get();

            $arr = [];

            foreach($technique_logs as $item){
                $log = $item->toArray();
                if(!array_key_exists($log['vin_code'], $arr)){
                    $arr[$log['vin_code']] = [
                        'vin_code' => $log['vin_code'],
                        'owner' => $log['owner'],
                        'mark' => $log['mark'],
                        'color' => $log['color'],
                        'address_from' => $log['address_from'],
                    ];
                }
                $arr[$log['vin_code']]['spine_number'] = $log['spine_number'];
                /*if($log['operation_type'] == 'received' && !array_key_exists('received', $arr[$log['vin_code']])) {
                    $arr[$log['vin_code']]['receive_date'] = date('Y-m-d H:i:s', strtotime($log['created_at']));
                }*/
                $arr[$log['vin_code']]['receive_date'][] = date('Y-m-d H:i:s', strtotime($log['created_at']));
                if(is_null($log['spineDate'])){
                    $arr[$log['vin_code']]['spine_date'] = '';
                } else {
                    $arr[$log['vin_code']]['shipped_date'] = date('Y-m-d H:i:s', strtotime($log['spineDate']));
                }
                /*if($log['operation_type'] == 'completed' && !array_key_exists('shipped', $arr[$log['vin_code']])) {
                    $arr[$log['vin_code']]['shipped_date'] = date('Y-m-d H:i:s', strtotime($log['created_at']));
                }*/
            }

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Отчет завоз и вывоз по авто');

            $sheet->setCellValue('A1', '№');
            $sheet->setCellValue('B1', 'Контрагент');
            $sheet->setCellValue('C1', 'Вин Код');
            $sheet->setCellValue('D1', 'Модель');
            $sheet->setCellValue('E1', 'Цвет');
            $sheet->setCellValue('F1', 'Дата завоза');
            $sheet->setCellValue('G1', 'Дата вывоза');
            $sheet->setCellValue('H1', 'Корешок');
            $sheet->setCellValue('I1', 'Номер автовоза / самоход');
            $sheet->setCellValue('J1', 'Зона хранения');

            // Вставляем авто размер для колонок
            $sheet->getColumnDimension('A')->setAutoSize(true);
            $sheet->getColumnDimension('B')->setAutoSize(true);
            $sheet->getColumnDimension('C')->setAutoSize(true);
            $sheet->getColumnDimension('D')->setAutoSize(true);
            $sheet->getColumnDimension('E')->setAutoSize(true);
            $sheet->getColumnDimension('F')->setAutoSize(true);
            $sheet->getColumnDimension('G')->setAutoSize(true);
            $sheet->getColumnDimension('H')->setAutoSize(true);
            $sheet->getColumnDimension('I')->setAutoSize(true);
            $sheet->getColumnDimension('J')->setAutoSize(true);

            $arr = array_values($arr);
            $row_start = 1;
            $current_row = $row_start;
            foreach($arr as $key=>$item){
                $current_row++;
                $sheet->setCellValue('A'.$current_row, $key+1);
                $sheet->setCellValue('B'.$current_row, $item['owner']);
                $sheet->setCellValue('C'.$current_row, $item['vin_code']);
                $sheet->setCellValue('D'.$current_row, $item['mark']);
                $sheet->setCellValue('E'.$current_row, $item['color']);
                $sheet->setCellValue('F'.$current_row, ($item['receive_date'][0]) ?? '');
                $sheet->setCellValue('G'.$current_row, ($item['shipped_date']) ?? '');
                $sheet->setCellValue('H'.$current_row, $item['spine_number']);
                $sheet->setCellValue('I'.$current_row, '');
                $sheet->setCellValue('J'.$current_row, $item['address_from']);

                $sheet->getStyle("A".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("E".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("G".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("H".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("J".$current_row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            $sheet->getStyle("A1:J".$current_row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $writer = new Xlsx($spreadsheet);
            $filename = "webcont_report_for_cars_today_".date('d.m.Y_H_i_s_').".xlsx";

            // Подготовка папок для сохранение картинки
            $dir = '/temp/';

            $path_to_file = $dir.$filename;
            $writer->save(public_path() . $path_to_file);

            return $path_to_file;
        }
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

    public function getMoveTypeName($data)
    {
        switch ($data->action_type) {
            case "put":
                return __('words.container.put');
                break;
            case "pick":
                return __('words.container.pick');
                break;
            case "move":
                if($data->address_from == 'buffer') {
                    return __('words.container.pp');
                } else {
                    return __('words.container.move');
                }
                break;
            case "move_another_zone":
                if($data->address_to == 'BUFFER | buffer') {
                    return __('words.container.vp');
                }
                break;
        }
    }
}
