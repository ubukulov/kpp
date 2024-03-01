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
        $marking = Mark::whereIn('status', ['new', 'processing'])->orderBy('id', 'DESC')->get();
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

            $marking->type = ($spreadsheet->getSheetCount() > 1) ? 1 : 2;
            $marking->save();

            if($spreadsheet->getSheetCount() > 1) {
                // Adidas
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

                    $mark_detail = MarkDetail::where(['gtin' => $gtin])->first();
                    if($mark_detail) {
                        break;
                    } else {
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

                        // second sheet
                        $spreadsheet->setActiveSheetIndex(1);
                        $sheetData2 = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
                        foreach ($sheetData2 as $key2=>$arr2) {
                            if ($key2 == 1) continue;
                            $marking_code = $arr2['C'];
                            MarkCode::create([
                                'marking_details_id' => $mark_detail->id, 'status' => 'not_marked', 'marking_code' => $marking_code
                            ]);
                        }
                    }
                }
            } else {
                // Albert Trading
                $spreadsheet->setActiveSheetIndex(0);
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                // First sheet
                foreach ($sheetData as $key=>$arr) {
                    if ($key <= 2) continue;
                    $container_number = $arr['A'];
                    $gtin = $arr['B'];
                    $line1 = (empty($arr['C'])) ? null : $arr['C'];
                    $line2 = (empty($arr['D'])) ? null : $arr['D'];
                    $line3 = (empty($arr['E'])) ? null : $arr['E'];
                    $line4 = (empty($arr['F'])) ? null : $arr['F'];
                    $line5 = (empty($arr['G'])) ? null : $arr['G'];
                    $line6 = (empty($arr['H'])) ? null : $arr['H'];
                    $line7 = (empty($arr['I'])) ? null : $arr['I'];
                    $line8 = (empty($arr['J'])) ? null : $arr['J'];
                    $line9 = (empty($arr['K'])) ? null : $arr['K'];
                    $line10 = (empty($arr['L'])) ? null : $arr['L'];
                    $line11 = (empty($arr['M'])) ? null : $arr['M'];
                    $eac = 'Y';//$arr['N'];
                    $mc = 'Y';//$arr['O'];

                    $mark_detail = MarkDetail::where(['marking_id' => $marking->id, 'gtin' => $gtin])->first();

                    if(!$mark_detail) {
                        $mark_detail = new MarkDetail();
                        $mark_detail->marking_id = $marking->id;
                        $mark_detail->container_number = $container_number;
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

                    MarkCode::create([
                        'marking_details_id' => $mark_detail->id, 'status' => 'not_marked', 'marking_code' => $line11, 'box_number' => $line10
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

    public function printByUsingCodes($printer_id)
    {
        $codes = [
            "0105021466516770213sqlo>GHTDmYd 91KZF092bhOyYR3bICIrKYo6+hR/Hblaepg=bhOyYR3bICIrKYo6+hR/Hblaepg=bhOyYR3bICIrKYo6+hR/Hblaepg=bhOy",
            "0105021466516770213OOXiaqjTuNj3 91KZF092IxlW2H+napIFTMXgyBohZ5NnkEA=IxlW2H+napIFTMXgyBohZ5NnkEA=IxlW2H+napIFTMXgyBohZ5NnkEA=IxlW",
            "0105021466516770213*F.,IitN2HmD 91KZF092KNoEgR+zMdUaOmDKjrZmuUJ9lyg=KNoEgR+zMdUaOmDKjrZmuUJ9lyg=KNoEgR+zMdUaOmDKjrZmuUJ9lyg=KNoE",
            "0105021466516770213z6<I<tS+Jlej 91KZF092sbiLbxcQ/6kEMSxH9QW0DO4aMy0=sbiLbxcQ/6kEMSxH9QW0DO4aMy0=sbiLbxcQ/6kEMSxH9QW0DO4aMy0=sbiL",
            "0105021466516770213tj%KMo)W_q: 91KZF092wW6ZGeZbFe3PmQLBbfIZHKG0Snw=wW6ZGeZbFe3PmQLBbfIZHKG0Snw=wW6ZGeZbFe3PmQLBbfIZHKG0Snw=wW6Z",
            "0105021466520326213yYvfqdiQtotF 91KZF092WoMtYjjcRdlIOFZP1hDzgm9iXQ4=WoMtYjjcRdlIOFZP1hDzgm9iXQ4=WoMtYjjcRdlIOFZP1hDzgm9iXQ4=WoMt",
            "01050214665203262134VP_t1t'9QaL 91KZF092YoUMWbl8Bpy6vql0jZKijWGYGxc=YoUMWbl8Bpy6vql0jZKijWGYGxc=YoUMWbl8Bpy6vql0jZKijWGYGxc=YoUM",
            "0105021466520852213d:VvY6BUXrdg 91KZF092kG+olNNesc7mjb/zn5QVP4lKAvM=kG+olNNesc7mjb/zn5QVP4lKAvM=kG+olNNesc7mjb/zn5QVP4lKAvM=kG+o"
        ];

        foreach($codes as $index=>$code) {
            $index++;
            $mark_code = MarkCode::where(['marking_code' => $code, 'status' => 'not_marked'])->first();
            if($mark_code) {
                $mark_detail = MarkDetail::find($mark_code->marking_details_id);
                $mark = Mark::findOrFail($mark_detail->marking_id);
                $print = Printer::findOrFail($printer_id);

                $line1 = Str::limit($mark_detail->line1, 45, '');
                $line2 = Str::limit($mark_detail->line2, 45, '');
                $line3 = Str::limit($mark_detail->line3, 45, '');
                $line4 = Str::limit($mark_detail->line4, 45, '');
                $line5 = Str::limit($mark_detail->line5, 45, '');
                $line6 = Str::limit($mark_detail->line6, 45, '');
                $line7 = Str::limit($mark_detail->line7, 45, '');
                $line8 = Str::limit($mark_detail->line8, 45, '');
                $line9 = Str::limit($mark_detail->line9, 45, '');
                if($mark->type == 1) {
                    $line10 = Str::limit($mark_detail->line10, 45, '');
                    $line11 = Str::limit($mark_detail->line11, 45, '');
                    $marking_code = $mark_code->marking_code;
                } else {
                    $line10 = $mark_code->box_number;
                    $line11 = Str::limit($mark_detail->line11, 45, '');
                    $marking_code = $mark_code->marking_code;
                }

                $barcode = $mark_detail->gtin;

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
                    //$eac = "^FT477,87^AZN,48,47,ARIALMT^FH\^FDEAC^FS";
                    $eac = "^FT430,87^AZN,48,47,ARIALMT^FH\^CI17^F8^FDEAC^FS^CI0";
                } else {
                    $eac = "";
                }

                if($mark_detail->mc == 'Y') {
                    $mc = "^BY20,20^FT400,270^BXN,4,200,0,0,1,~^FH\^FD$marking_code^FS";
                    $mc .= ($mark->type == 2) ? "^FT20,295^AZN,17,17^FH\^CI28^FD$line10^FS^CI27^FT415,295^AZN,17,17^FH\^CI28^FD$barcode^FS^CI27" : "";
                } else {
                    $mc = '';
                }

                if($mark->type == 1) {
                    $data = <<<HERE
^XA^LRN^CI0^XZ
^XA^CWZ,E:ARIALMT^FS^XZ
^XA
^MMT
^PW559
^LL0320
^LS0
^FT20,35^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line1^FS^CI0
^FT20,58^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line2^FS^CI0
^FT20,81^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line3^FS^CI0
^FT20,102^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line4^FS^CI0
^FT20,128^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line5^FS^CI0
^FT20,152^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line6^FS^CI0
^FT20,179^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line7^FS^CI0
^FT20,205^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line8^FS^CI0
^FT20,231^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line9^FS^CI0
^FT20,257^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line10^FS^CI0
^FT20,281^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line11^FS^CI0
$eac
$mc
^PQ1,0,1,Y^XZ
HERE;
                } else {
                    $data = <<<HERE
^XA
^XA^CWA,E:ARIALKZ^FS^XZ
^XA
^MMT
^PW559
^LL0320
^LS0
^FT20,35^AZN,17,16,ARIALKZ^FH\^CI28^FDModel Name: $line1^FS
^FT20,58^AZN,17,16,ARIALKZ^FH\^CI28^F8^FDModel Code: $line2^FS
^FT20,81^AZN,17,16,ARIALKZ^FH\^CI28^F8^FDSize: $line3^FS
HERE;
                    $arr = [];
                    $txt = "Main Composition: ";
                    $arrTxt = explode(" ", $line4);
                    foreach ($arrTxt as $item) {
                        if($txt == '') {
                            $txt = $item;
                        } else {
                            $all = mb_strlen($txt);
                            $sizeItem = mb_strlen($item);
                            $sum = $all + $sizeItem;
                            if($all < 48 && $sum < 48) {
                                $txt .= " ".$item;
                            } else {
                                $arr[] = $txt;
                                $txt = $item;
                            }
                        }
                    }

                    if($txt != '') {
                        $arr[] = $txt;
                    }

                    $val = 102;
                    foreach($arr as $key=>$item) {
                        if($key == 0) {
                            $data .= <<<HERE
               ^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI28^F8^FD$item^FS
HERE;
                        } else {
                            $data .= <<<HERE
               ^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI28^F8^FD$item^FS
HERE;
                        }

                        $val += 20;
                    }

                    $data .= <<<HERE
^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI28^F8^FDLiner Composition: $line5^FS
HERE;
                    $val += 20;

                    $data .= <<<HERE
^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI17^F8^FDSole Composition: $line6^FS
HERE;
                    $val += 20;

                    $data .= <<<HERE
^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI17^F8^FDBrand: $line7^FS
HERE;

                    $val += 20;

                    $data .= <<<HERE
^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI17^F8^FDCountry Of Manufacture: $line8^FS
HERE;
                    $val += 20;

                    $arr = [];
                    $txt = "Importer: ";
                    $arrTxt = explode(" ", $line9);
                    foreach ($arrTxt as $item) {
                        if($txt == '') {
                            $txt = $item;
                        } else {
                            $all = mb_strlen($txt);
                            $sizeItem = mb_strlen($item);
                            $sum = $all + $sizeItem;
                            if($all < 48 && $sum < 48) {
                                $txt .= " ".$item;
                            } else {
                                $arr[] = $txt;
                                $txt = $item;
                            }
                        }
                    }

                    if($txt != '') {
                        $arr[] = $txt;
                    }

                    foreach($arr as $key=>$item) {
                        if($key == 0) {
                            $data .= <<<HERE
               ^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI17^F8^FD$item^FS
HERE;
                        } else {
                            $data .= <<<HERE
               ^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI17^F8^FD$item^FS
HERE;
                        }

                        $val += 20;
                    }

                    $data .= <<<HERE
$eac
$mc
^PQ1,0,1,Y^XZ
HERE;

                }

                if (!fwrite($fp,$data)){
                    return response(['data' => 'writing failed'], 403);
                }

                $mark_code->status = 'marked';
                $mark_code->scan_user_id = Auth::user()->id;
                $mark_code->updated_at = Carbon::now();
                $mark_code->save();

                if($mark->free_codes() > 0) {
                    $mark->status = 'processing';
                    $mark->save();
                } else {
                    $mark->status = 'completed';
                    $mark->save();
                }

                echo $index.") ".$code." <span style='font-size: 30px; color: green;'>распечатано.</span><br>";
            } else {
                echo $index.") ".$code." <span style='font-size: 30px; color: red;'>не распечатано.</span><br>";
            }
        }
    }

    public function commandPrint(Request $request)
    {
        $data = $request->all();
        $marking = Mark::findOrFail($data['mark_id']);
        //$mark_detail = MarkDetail::where(['marking_id' => $data['mark_id'], 'gtin' => $data['gtin_number']])->first();
        if($data['mark_id'] >= 161 AND $data['mark_id'] <= 164) {
            $mark_detail = MarkDetail::where(['gtin' => $data['gtin_number']])->whereIn('marking_id', [161,162,163,164])->first();
        } else {
            $mark_detail = MarkDetail::where(['marking_id' => $data['mark_id'], 'gtin' => $data['gtin_number']])->first();
        }

//        $mark_detail = MarkDetail::where(['marking_id' => $data['mark_id'], 'gtin' => $data['gtin_number']])->first();

        //$mark_detail = MarkDetail::find(3992);
        if($mark_detail) {
            $mark_code = MarkCode::where(['marking_details_id' => $mark_detail->id, 'status' => 'not_marked'])->first();
            if($mark_code) {
                $mark_code->status = 'marked';
                $mark_code->scan_user_id = Auth::user()->id;
                if($marking->type == 1) {
                    $mark_code->box_number = $data['box_number'];
                }

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


                $line1 = Str::limit($mark_detail->line1, 45, '');
                $line2 = Str::limit($mark_detail->line2, 45, '');
                $line3 = Str::limit($mark_detail->line3, 45, '');
                $line4 = Str::limit($mark_detail->line4, 45, '');
                $line5 = Str::limit($mark_detail->line5, 45, '');
                $line6 = Str::limit($mark_detail->line6, 45, '');
                $line7 = Str::limit($mark_detail->line7, 45, '');
                $line8 = Str::limit($mark_detail->line8, 45, '');
                //$line9 = Str::limit($mark_detail->line9, 45, '');
                $line9 = Str::limit($mark_detail->line9, 110, '');
                if($marking->type == 1) {
                    $line10 = Str::limit($mark_detail->line10, 45, '');
                    $line11 = Str::limit($mark_detail->line11, 45, '');
                    $marking_code = $mark_code->marking_code;
                } else {
                    $line10 = $mark_code->box_number;
                    $line11 = Str::limit($mark_detail->line11, 45, '');
                    $marking_code = $mark_code->marking_code;
                }

                $barcode = $mark_detail->gtin;

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
                    //$eac = "^FT477,87^AZN,48,47,ARIALMT^FH\^FDEAC^FS";
                    $eac = "^FT430,87^AZN,48,47,ARIALMT^FH\^CI17^F8^FDEAC^FS^CI0";
                } else {
                    $eac = "";
                }

                if($mark_detail->mc == 'Y') {
                    $mc = "^BY20,20^FT380,270^BXN,4,200,0,0,1,~^FH\^FD$marking_code^FS";
                    $mc .= ($marking->type == 2) ? "^FT20,295^AZN,17,17^FH\^CI28^FD$line10^FS^CI27^FT415,295^AZN,17,17^FH\^CI28^FD$barcode^FS^CI27" : "";
                } else {
                    $mc = '';
                }

                if($marking->type == 1) {
                    $data = <<<HERE
^XA^LRN^CI0^XZ
^XA^CWZ,E:ARIALMT^FS^XZ
^XA
^MMT
^PW559
^LL0320
^LS0
^FT20,35^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line1^FS^CI0
^FT20,58^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line2^FS^CI0
^FT20,81^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line3^FS^CI0
^FT20,102^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line4^FS^CI0
^FT20,128^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line5^FS^CI0
^FT20,152^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line6^FS^CI0
^FT20,179^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line7^FS^CI0
^FT20,205^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line8^FS^CI0
^FT20,231^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line9^FS^CI0
^FT20,257^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line10^FS^CI0
^FT20,281^AZN,17,16,ARIALMT^FH\^CI17^F8^FD$line11^FS^CI0
$eac
$mc
^PQ1,0,1,Y^XZ
HERE;
                } else {
                    $data = <<<HERE
^XA
^XA^CWA,E:ARIALKZ^FS^XZ
^XA
^MMT
^PW559
^LL0320
^LS0
^FT20,35^AZN,17,16,ARIALKZ^FH\^CI28^FDModel Name: $line1^FS
^FT20,58^AZN,17,16,ARIALKZ^FH\^CI28^F8^FDModel Code: $line2^FS
^FT20,81^AZN,17,16,ARIALKZ^FH\^CI28^F8^FDSize: $line3^FS
HERE;
                    $arr = [];
                    $txt = "Main Composition: ";
                    $arrTxt = explode(" ", $line4);
                    foreach ($arrTxt as $item) {
                        if($txt == '') {
                            $txt = $item;
                        } else {
                            $all = mb_strlen($txt);
                            $sizeItem = mb_strlen($item);
                            $sum = $all + $sizeItem;
                            if($all < 48 && $sum < 48) {
                                $txt .= " ".$item;
                            } else {
                                $arr[] = $txt;
                                $txt = $item;
                            }
                        }
                    }

                    if($txt != '') {
                        $arr[] = $txt;
                    }

                    $val = 102;
                    foreach($arr as $key=>$item) {
                        if($key == 0) {
                            $data .= <<<HERE
               ^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI28^F8^FD$item^FS
HERE;
                        } else {
                            $data .= <<<HERE
               ^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI28^F8^FD$item^FS
HERE;
                        }

                        $val += 20;
                    }

                    $data .= <<<HERE
^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI28^F8^FDLiner Composition: $line5^FS
HERE;
                    $val += 20;

                    $data .= <<<HERE
^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI17^F8^FDSole Composition: $line6^FS
HERE;
                    $val += 20;

                    if(($mark->id >= 154 AND $mark->id <= 158) || ($mark->id >= 161 AND $mark->id <= 164)) {
                        $data .= <<<HERE
^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI17^F8^FDBrand: $line7^FS
HERE;
                    } else {
                        $data .= <<<HERE
^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI17^F8^FDProduction Date: $line7^FS
HERE;
                    }

                    $val += 20;

                    $data .= <<<HERE
^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI17^F8^FDCountry Of Manufacture: $line8^FS
HERE;
                    $val += 20;

                    $arr = [];
                    $txt = "Importer: ";
                    $arrTxt = explode(" ", $line9);
                    foreach ($arrTxt as $item) {
                        if($txt == '') {
                            $txt = $item;
                        } else {
                            $all = mb_strlen($txt);
                            $sizeItem = mb_strlen($item);
                            $sum = $all + $sizeItem;
                            if($all < 45 && $sum < 45) {
                                $txt .= " ".$item;
                            } else {
                                $arr[] = $txt;
                                $txt = $item;
                            }
                        }
                    }

                    if($txt != '') {
                        $arr[] = $txt;
                    }

                    foreach($arr as $key=>$item) {
                        if($key == 0) {
                            $data .= <<<HERE
               ^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI17^F8^FD$item^FS
HERE;
                        } else {
                            $data .= <<<HERE
               ^FT20,$val^AZN,17,16,ARIALKZ^FH\^CI17^F8^FD$item^FS
HERE;
                        }

                        $val += 20;
                    }

                    $data .= <<<HERE
$eac
$mc
^PQ1,0,1,Y^XZ
HERE;

                    //return $data;

                }



                /*$data = <<<HERE
^XA^LRN^CI0^XZ
^XA^CWZ,E:tt0003m_^FS^XZ
^XA
^MMT
^PW559
^LL0320
^LS0
^FT20,35^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line1^FS^CI0
^FT20,58^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line1^FS^CI0
^FT20,81^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line3^FS^CI0
^FT20,102^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line4^FS^CI0
^FT20,128^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line5^FS^CI0
^FT20,152^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line6^FS^CI0
^FT20,179^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line7^FS^CI0
^FT20,205^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line8^FS^CI0
^FT20,231^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line9^FS^CI0
^FT20,257^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line10^FS^CI0
^FT20,281^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line11^FS^CI0
$eac
$mc
^PQ1,0,1,Y^XZ
HERE;*/

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
