<?php

namespace App\Http\Controllers\Admin;

use App\Models\Company;
use App\Http\Controllers\Controller;
use App\Models\Permit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use File;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportController extends Controller
{
    public function index()
    {
        $companies = Company::all();
        return view('admin.report.index', compact('companies'));
    }

    public function getPermits(Request $request)
    {
        $from_date = $request->input('from_date');
        $to_date = $request->input('to_date');
        $company_id = $request->input('company_id');
        if($company_id == 0) {
            $permits = Permit::where(['status' => 'printed'])->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])->get();
        } else {
            $permits = Permit::where(['company_id' => $company_id, 'status' => 'printed'])->whereRaw("(date_in >= ? AND date_in <= ?)", [$from_date." 00:00", $to_date." 23:59"])->get();
        }
        return json_encode($permits);
    }

    public function statistics()
    {
        $cur_month = DB::select("SELECT COUNT(*) as cnt FROM permits
                         WHERE date_in IS NOT NULL AND status='printed'
                         AND MONTH(date_in)=MONTH(CURDATE()) AND YEAR(date_in)=YEAR(CURDATE())");

        $pre_month = DB::select("SELECT COUNT(*) as cnt FROM permits
                         WHERE date_in IS NOT NULL AND status='printed'
                         AND MONTH(date_in)=MONTH(DATE_ADD(NOW(), INTERVAL -1 MONTH)) AND YEAR(date_in)=YEAR(NOW())");

        return view('admin.report.statistics', compact('cur_month', 'pre_month'));
    }

    public function downloadUser()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Список сотрудников');

        $users = User::orderBy('id', 'DESC')
            ->selectRaw('users.*, companies.short_ru_name, positions.title as position_name, departments.title as department_name')
            ->selectRaw('(SELECT users_histories.status FROM users_histories WHERE users_histories.user_id=users.id ORDER BY users_histories.id DESC LIMIT 1) as status')
            ->join('companies', 'companies.id', 'users.company_id')
            ->join('positions', 'positions.id', 'users.position_id')
            ->leftJoin('departments', 'departments.id', 'users.department_id')
            ->get();

        $str = "Отчет по сотрудникам за " . date("d.m.Y H:i:s");
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', $str);
        $sheet->getStyle("A1")->getFont()->setSize(16)->setBold(true);

        $this->setCellValue($sheet, array('A3', 'B3', 'C3', 'D3', 'E3', 'F3', 'G3', 'H3', 'I3', 'J3'), array('ID','Компания', 'Должность', 'Отдел', 'ФИО', 'Телефон', 'ИИН', 'Email', 'UUID', 'Статус'));

        // Заголовки сделаем жирными
        $this->setBoldFontForColumns($sheet, true, 'A3', 'B3', 'C3', 'D3', 'E3', 'F3', 'G3', 'H3', 'I3', 'J3');

        // Вставляем авто размер для колонок
        $this->setAutoSizeColumn($sheet, true, 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');

        $row_start = 3;
        $current_row = $row_start;
        foreach($users as $key=>$user){
            $current_row++;
            $sheet->insertNewRowBefore($row_start + $key+1, 1);
            $sheet->setCellValue('A'.$current_row,$user->id);
            $sheet->setCellValue('B'.$current_row,$user->short_ru_name);
            $sheet->setCellValue('C'.$current_row,$user->position_name);
            $sheet->setCellValue('D'.$current_row,$user->department_name);
            $sheet->setCellValue('E'.$current_row,$user->full_name);
            $sheet->setCellValue('F'.$current_row,$user->phone);
            $sheet->setCellValue('G'.$current_row,$user->iin);
            $sheet->setCellValue('H'.$current_row,$user->email);
            $sheet->setCellValue('I'.$current_row,$user->uuid);
            $sheet->setCellValue('J'.$current_row,trans("words.".$user->status));
        }

        // Выравниваем по левому краю
        $this->setHorizontal($sheet, Alignment::HORIZONTAL_LEFT, 'A');

        $writer = new Xlsx($spreadsheet);
        $filename = date('d.m.Y_H_i_s_') . ".xlsx";

        // Подготовка папок для сохранение картинки
        $dir = '/reports/admins/' . date('Y-m').'/';
        if(!File::isDirectory(public_path(). $dir)){
            File::makeDirectory(public_path(). $dir, 0777, true);
        }

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($filename));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        readfile($filename);
        exit;
    }
}
