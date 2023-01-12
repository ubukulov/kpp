<?php

namespace App\Http\Controllers;

use App\Models\Mark;
use App\Models\MarkCode;
use App\Models\MarkDetail;
use App\Models\Printer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;
use File;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MarkController extends BaseController
{
    public function index()
    {
        $marking = Mark::orderBy('id', 'DESC')->get();
        return view('mark.index', compact('marking'));
    }

    public function create()
    {
        return view('mark.create');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            // Подготовка папок для сохранение картинки
            $dir = '/marking_files/'. substr(md5(microtime()), mt_rand(0, 30), 2);
            if(!File::isDirectory(public_path(). $dir)){
                File::makeDirectory(public_path(). $dir, 0777, true);
            }

            $upload_file = $request->file('upload_file');
            $filename = substr(md5(time()), 0, 5) . "_" . $upload_file->getClientOriginalName();
            $path_to_file = $dir.'/'.$filename;
            $upload_file->move(public_path() . $dir, $filename);
            $marking = Mark::create([
                'user_id' => Auth::user()->id, 'status' => 'new', 'upload_file' => $path_to_file
            ]);

            $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(public_path($path_to_file));
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
            $spreadsheet = $reader->load(public_path($path_to_file));
            $spreadsheet->setActiveSheetIndex(0);
            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            // First sheet
            foreach ($sheetData as $key=>$arr) {
                if ($key == 1) continue;
                $container_number = $arr['A'];
                $invoice_number = $arr['B'];
                $gtin = $arr['C'];
                $line1 = (empty($arr['D'])) ? null : $arr['D'];
                $line2 = (empty($arr['E'])) ? null : $arr['E'];
                $line3 = (empty($arr['F'])) ? null : $arr['F'];
                $line4 = (empty($arr['G'])) ? null : $arr['G'];
                $line5 = (empty($arr['H'])) ? null : $arr['H'];
                $line6 = (empty($arr['I'])) ? null : $arr['I'];
                $line7 = (empty($arr['J'])) ? null : $arr['J'];
                $line8 = (empty($arr['K'])) ? null : $arr['K'];
                $line9 = (empty($arr['L'])) ? null : $arr['L'];
                $line10 = (empty($arr['M'])) ? null : $arr['M'];
                $line11 = (empty($arr['N'])) ? null : $arr['N'];
                $eac = $arr['O'];
                $mc = $arr['P'];

                $mark_detail = new MarkDetail();
                $mark_detail->marking_id = $marking->id;
                $mark_detail->container_number = $container_number;
                $mark_detail->invoice_number = $invoice_number;
                $mark_detail->gtin = $gtin;
                $mark_detail->line1 = $line1;
                $mark_detail->line2 = $line2;
                $mark_detail->line3 = $line3;
                $mark_detail->line4 = $line4;
                $mark_detail->line5 = $line5;
                $mark_detail->line6 = $line6;
                $mark_detail->line7 = $line7;
                $mark_detail->line8 = $line8;
                $mark_detail->line9 = $line9;
                $mark_detail->line10 = $line10;
                $mark_detail->line11 = $line11;
                $mark_detail->eac = $eac;
                $mark_detail->mc = $mc;
                $mark_detail->save();
            }

            // second sheet
            $spreadsheet->setActiveSheetIndex(1);
            $sheetData2 = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
            foreach ($sheetData2 as $key=>$arr) {
                if ($key == 1) continue;
                $gtin = $arr['B'];
                $marking_code = $arr['C'];
                $mark_detail2 = MarkDetail::where(['gtin' => $gtin])->first();
                if($mark_detail2) {
                    MarkCode::create([
                        'marking_details_id' => $mark_detail2->id, 'status' => 'not_marked', 'marking_code' => $marking_code
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('mark.index');

        } catch (\Exception $exception) {
            DB::rollBack();
            dd($exception);
        }
    }

    public function show($mark_id)
    {
        $mark = Mark::findOrFail($mark_id);
        $marking_codes = MarkCode::where(['marking_details.marking_id' => $mark_id])
                        ->selectRaw('marking_codes.*, marking_details.container_number, marking_details.gtin')
                        ->join('marking_details', 'marking_details.id', 'marking_codes.marking_details_id')
                        ->get();
        return view('mark.show', compact('mark', 'marking_codes'));
    }

    public function markManager()
    {
        return view('mark.manager');
    }

    public function getMarkings()
    {
        return response()->json(Mark::selectRaw('id, CONCAT("№", id) as number')->where(['status' => 'new'])->orWhere(['status' => 'processing'])->get());
    }

    public function getContainers($mark_id)
    {
        $containers = MarkDetail::where(['marking_id' => $mark_id])
                    ->selectRaw('id, container_number')
                    ->groupBy('container_number')
                    ->get();
        return response()->json($containers);
    }

    public function getPrinters()
    {
        return response()->json(Printer::all());
    }

    public function commandPrint(Request $request)
    {
        $data = $request->all();
        $mark_detail = MarkDetail::where(['marking_id' => $data['mark_id'], 'gtin' => $data['gtin_number']])->first();
        if($mark_detail) {
            $mark_code = MarkCode::where(['marking_details_id' => $mark_detail->id, 'status' => 'not_marked'])->first();
            if($mark_code) {
                $mark_code->status = 'marked';
                $mark_code->scan_user_id = Auth::user()->id;
                $mark_code->box_number = $data['box_number'];
                $mark_code->updated_at = Carbon::now();
                $mark_code->save();

                $mark = Mark::findOrFail($data['mark_id']);
                if($mark->free_codes() > 0) {
                    $mark->status = 'processing';
                    $mark->save();
                } else {
                    $mark->status = 'completed';
                    $mark->save();
                }

                $print = Printer::findOrFail($data['print_id']);

                $marking_code = $mark_code->marking_code;
                $line1 = Str::limit($mark_detail->line1, 45, '');
                $line2 = Str::limit($mark_detail->line2, 45, '');
                $line3 = Str::limit($mark_detail->line3, 45, '');
                $line4 = Str::limit($mark_detail->line4, 45, '');
                $line5 = Str::limit($mark_detail->line5, 45, '');
                $line6 = Str::limit($mark_detail->line6, 45, '');
                $line7 = Str::limit($mark_detail->line7, 45, '');
                $line8 = Str::limit($mark_detail->line8, 45, '');
                $line9 = Str::limit($mark_detail->line9, 45, '');
                $line10 = Str::limit($mark_detail->line10, 45, '');
                $line11 = Str::limit($mark_detail->line11, 45, '');

                //$user = Auth::user();
                $computer_name = $print->computer_name;
                $printer_name = $print->printer_name;

                //$printer = "\\\\".$computer_name.$printer_name;
                $printer = "\\\\".$computer_name.$printer_name;
                // Open connection to the thermal printer
                $fp = fopen($printer, "w");
                if (!$fp){
                    return response([
                        'data' => [
                            'message' => 'no connection to printer'
                        ]
                    ], 401);
                }

                if($mark_detail->eac == 'Y') {
                    $eac = "^FT477,87^A0N,68,67^FH\^FDEAC^FS";
                } else {
                    $eac = '';
                }

                if($mark_detail->mc == 'Y') {
                    $mc = "^BY160,160^FT477,261^BXN,4,200,0,0,1,~^FH\^FD$marking_code^FS";
                } else {
                    $mc = '';
                }
                $data = <<<HERE
^XA^LRN^CI0^XZ
^XA^CWZ,E:tt0003m_^FS^XZ
^XA
^MMT
^PW559
^LL0320
^LS0
^FT90,35^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line1^FS^CI0
^FT90,58^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line1^FS^CI0
^FT90,81^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line3^FS^CI0
^FT90,102^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line4^FS^CI0
^FT90,128^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line5^FS^CI0
^FT90,152^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line6^FS^CI0
^FT90,179^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line7^FS^CI0
^FT90,205^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line8^FS^CI0
^FT90,231^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line9^FS^CI0
^FT90,257^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line10^FS^CI0
^FT90,281^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line11^FS^CI0
$eac
$mc
^PQ1,0,1,Y^XZ
HERE;

                if (!fwrite($fp,$data)){
                    return response(['data' => 'writing failed'], 403);
                }

                return response('Success', 200);
            }
        }
    }

    public function generateExcel(Request $request)
    {
        $mark_id = $request->input('mark_id');
        $marking_codes = MarkCode::where(['marking_details.marking_id' => $mark_id])
            ->selectRaw('marking_codes.*, marking_details.container_number, marking_details.gtin,marking_details.mc')
            ->join('marking_details', 'marking_details.id', 'marking_codes.marking_details_id')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Result');

        $sheet->setCellValue('A1', "Номер контейнера");
        $sheet->setCellValue('B1', "Номер короба");
        $sheet->setCellValue('C1', "GTIN");
        $sheet->setCellValue('D1', "Код маркировки");



        $row_start = 1;
        $current_row = $row_start;
        foreach($marking_codes as $key=>$marking_code) {
            $current_row++;
            $sheet->insertNewRowBefore($row_start + $key + 1, 1);
            $sheet->setCellValue('A' . $current_row, $marking_code->container_number);
            $sheet->setCellValue('B' . $current_row, $marking_code->box_number);
            $sheet->setCellValue('C' . $current_row, $marking_code->gtin);
            if($marking_code->mc == "Y") {
                $sheet->setCellValue('D' . $current_row, $marking_code->marking_code);
            } else {
                $sheet->setCellValue('D' . $current_row, '-');
            }

        }

        $writer = new Xlsx($spreadsheet);
        $filename = "adidas-".$marking_codes[0]->container_number.'-R-'.date('Ymd').'-'.date('hms').'.xlsx';

        // Подготовка папок для сохранение картинки
        $dir = '/marking_files/temp/'. $mark_id . '/' . date('Y-m').'/';
        if(!File::isDirectory(public_path(). $dir)){
            File::makeDirectory(public_path(). $dir, 0777, true);
        }

        $path_to_file = $dir.$filename;
        $writer->save(public_path() . $path_to_file);

        return $path_to_file;
    }
}
