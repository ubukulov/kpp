<?php

namespace App\Http\Controllers;

use App\Models\Mark;
use App\Models\MarkAggregation;
use App\Models\MarkCode;
use App\Models\MarkDetail;
use App\Models\PalletSSCC;
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
    public $i = 1;

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
                    if(empty($gtin)) break;
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
                    //$eac = "^FT477,87^A0N,48,47,TT0003M_^FH\^FDEAC^FS";
                    $eac = "^FT430,87^A0N,48,47,TT0003M_^FH\^CI17^F8^FDEAC^FS^CI0";
                } else {
                    $eac = "";
                }

                if($mark_detail->mc == 'Y') {
                    $mc = "^BY20,20^FT400,270^BXN,4,200,0,0,1,~^FH\^FD$marking_code^FS";
                    $mc .= ($mark->type == 2) ? "^FT20,295^A0N,17,17^FH\^CI28^FD$line10^FS^CI27^FT415,295^A0N,17,17^FH\^CI28^FD$barcode^FS^CI27" : "";
                } else {
                    $mc = '';
                }

                if($mark->type == 1) {
                    $data = <<<HERE
^XA^LRN^CI0^XZ
^XA^CWZ,E:TT0003M_^FS^XZ
^XA
^MMT
^PW559
^LL0320
^LS0
^FT20,35^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line1^FS^CI0
^FT20,58^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line2^FS^CI0
^FT20,81^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line3^FS^CI0
^FT20,102^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line4^FS^CI0
^FT20,128^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line5^FS^CI0
^FT20,152^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line6^FS^CI0
^FT20,179^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line7^FS^CI0
^FT20,205^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line8^FS^CI0
^FT20,231^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line9^FS^CI0
^FT20,257^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line10^FS^CI0
^FT20,281^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line11^FS^CI0
$eac
$mc
^PQ1,0,1,Y^XZ
HERE;
                } else {
                    $data = <<<HERE
^XA
^XA^CWA,E:TT0003M_^FS^XZ
^XA
^MMT
^PW559
^LL0320
^LS0
^FT20,35^A0N,17,16,TT0003M_^FH\^CI28^FDModel Name: $line1^FS
^FT20,58^A0N,17,16,TT0003M_^FH\^CI28^F8^FDModel Code: $line2^FS
^FT20,81^A0N,17,16,TT0003M_^FH\^CI28^F8^FDSize: $line3^FS
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
               ^FT20,$val^A0N,17,16,TT0003M_^FH\^CI28^F8^FD$item^FS
HERE;
                        } else {
                            $data .= <<<HERE
               ^FT20,$val^A0N,17,16,TT0003M_^FH\^CI28^F8^FD$item^FS
HERE;
                        }

                        $val += 20;
                    }

                    $data .= <<<HERE
^FT20,$val^A0N,17,16,TT0003M_^FH\^CI28^F8^FDLiner Composition: $line5^FS
HERE;
                    $val += 20;

                    $data .= <<<HERE
^FT20,$val^A0N,17,16,TT0003M_^FH\^CI17^F8^FDSole Composition: $line6^FS
HERE;
                    $val += 20;

                    $data .= <<<HERE
^FT20,$val^A0N,17,16,TT0003M_^FH\^CI17^F8^FDBrand: $line7^FS
HERE;

                    $val += 20;

                    $data .= <<<HERE
^FT20,$val^A0N,17,16,TT0003M_^FH\^CI17^F8^FDCountry Of Manufacture: $line8^FS
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
               ^FT20,$val^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$item^FS
HERE;
                        } else {
                            $data .= <<<HERE
               ^FT20,$val^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$item^FS
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
        if($data['mark_id'] >= 180 AND $data['mark_id'] <= 181) {
            $mark_detail = MarkDetail::where(['gtin' => $data['gtin_number']])->whereIn('marking_id', [180,181])->first();
        } else {
            $mark_detail = MarkDetail::where(['marking_id' => $data['mark_id'], 'gtin' => $data['gtin_number']])->first();
        }

//        $mark_detail = MarkDetail::where(['marking_id' => $data['mark_id'], 'gtin' => $data['gtin_number']])->first();
        if($mark_detail) {
            $mark_code = MarkCode::where(['marking_details_id' => $mark_detail->id, 'status' => 'not_marked'])->first();
            if($mark_code) {
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
                $printer = '\\\\'.$computer_name.$printer_name;
                // Open connection to the thermal printer
                $fp = fopen($printer, "w");
                if (!$fp){
                    return response([
                        'data' => [
                            'message' => 'no connection to printer'
                        ]
                    ], 401);
                }

                $mark_code->status = 'marked';
                $mark_code->scan_user_id = Auth::user()->id;
                if($marking->type == 1) {
                    $mark_code->box_number = $data['box_number'];
                }

                $mark_code->updated_at = Carbon::now();
                $mark_code->save();

                if($mark_detail->eac == 'Y') {
                    //$eac = "^FT477,87^A0N,48,47,TT0003M_^FH\^FDEAC^FS";
                    $eac = "^FT430,87^A0N,48,47,TT0003M_^FH\^CI17^F8^FDEAC^FS^CI0";
                } else {
                    $eac = "";
                }

                if($mark_detail->mc == 'Y') {
                    $mc = "^BY20,20^FT380,270^BXN,4,200,0,0,1,~^FH\^FD$marking_code^FS";
                    $mc .= ($marking->type == 2) ? "^FT20,295^A0N,17,17^FH\^CI28^FD$line10^FS^CI27^FT415,295^A0N,17,17^FH\^CI28^FD$barcode^FS^CI27" : "";
                } else {
                    $mc = '';
                }

                if($marking->type == 1) {
                    $data = <<<HERE
^XA^LRN^CI0^XZ
^XA^CWZ,E:TT0003M_^FS^XZ
^XA
^MMT
^PW559
^LL0320
^LS0
^FT20,35^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line1^FS^CI0
^FT20,58^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$line2^FS^CI0
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
HERE;
                } else {
                    $data = <<<HERE
^XA
^XA^CWA,E:TT0003M_^FS^XZ
^XA
^MMT
^PW559
^LL0320
^LS0
^FT20,35^AZN,17,16,TT0003M_^FH\^CI28^FDModel Name: $line1^FS
^FT20,58^AZN,17,16,TT0003M_^FH\^CI28^F8^FDModel Code: $line2^FS
^FT20,81^AZN,17,16,TT0003M_^FH\^CI28^F8^FDSize: $line3^FS
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
               ^FT20,$val^AZN,17,16,TT0003M_^FH\^CI28^F8^FD$item^FS
HERE;
                        } else {
                            $data .= <<<HERE
               ^FT20,$val^AZN,17,16,TT0003M_^FH\^CI28^F8^FD$item^FS
HERE;
                        }

                        $val += 20;
                    }

                    $data .= <<<HERE
^FT20,$val^AZN,17,16,TT0003M_^FH\^CI28^F8^FDLiner Composition: $line5^FS
HERE;
                    $val += 20;

                    $data .= <<<HERE
^FT20,$val^AZN,17,16,TT0003M_^FH\^CI17^F8^FDSole Composition: $line6^FS
HERE;
                    $val += 20;

                    if(($mark->id >= 154 AND $mark->id <= 158) || ($mark->id >= 161 AND $mark->id <= 164)) {
                        $data .= <<<HERE
^FT20,$val^AZN,17,16,TT0003M_^FH\^CI17^F8^FDBrand: $line7^FS
HERE;
                    } else {
                        $data .= <<<HERE
^FT20,$val^AZN,17,16,TT0003M_^FH\^CI17^F8^FDProduction Date: $line7^FS
HERE;
                    }

                    $val += 20;

                    $data .= <<<HERE
^FT20,$val^AZN,17,16,TT0003M_^FH\^CI17^F8^FDCountry Of Manufacture: $line8^FS
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
               ^FT20,$val^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$item^FS
HERE;
                        } else {
                            $data .= <<<HERE
               ^FT20,$val^AZN,17,16,TT0003M_^FH\^CI17^F8^FD$item^FS
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
^FT20,35^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line1^FS^CI0
^FT20,58^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line1^FS^CI0
^FT20,81^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line3^FS^CI0
^FT20,102^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line4^FS^CI0
^FT20,128^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line5^FS^CI0
^FT20,152^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line6^FS^CI0
^FT20,179^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line7^FS^CI0
^FT20,205^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line8^FS^CI0
^FT20,231^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line9^FS^CI0
^FT20,257^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line10^FS^CI0
^FT20,281^A0N,17,16,TT0003M_^FH\^CI17^F8^FD$line11^FS^CI0
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

    public function generateSSCC(Request $request)
    {
        $data = $request->all();
        $marking = Mark::findOrFail($data['mark_id']);
        $print = Printer::findOrFail($data['printer_id']);
        $marking_detail = MarkDetail::where(['marking_id' => $marking->id, 'line3' => $data['seria']])->first();
        if($marking_detail) {
            if($data['option_id'] == 1) {
                $count_box = (count($marking_detail->codes) / $marking_detail->line6);
                $standard = $marking_detail->line6;
            } else {
                $count_box = (count($marking_detail->codes) / $marking_detail->line6);
                if($count_box < $marking_detail->line7) {
                    $count_box = 1;
                }

                $standard = $marking_detail->line7;
            }

            for($i=1; $i <= $count_box; $i++) {
                $sscc_code = PalletSSCC::generateSSCC($marking->id, $data['option_id'], $standard);
                $code = <<<HERE
^XA^LRN^CI0^XZ
^XA
~TA000
~JSN
^LT0
^MNW
^MTT
^PON
^PMN
^LH0,0
^JMA
^PR4,4
~SD28
^JUS
^LRN
^CI27
^PA0,1,1,0
^XZ
^XA
^MMT
^PW799
^LL400
^LS0
^BY2,3,91^FT100,349^BCN,,Y,N
^FH\^FD>:$sscc_code^FS
^FT40,95^A0N,39,38^FH\^CI28^FDProduct: $standard^FS^CI27
^FT40,181^A0N,37,38^FH\^CI28^FDSSCC: $sscc_code^FS^CI27
^FO551,40^GFA,821,4416,24,:Z64:eJzdl0Fu2zAQRUdRCwfpgt10TcAXyA2sHqEbo1fpwrC47K5HKNCle4dauQmXXgoFAgioIFbikNRIHCsm4BZJJpFF/zx9fY0phwJ4dlUcp/XL6aWZldPnslFWziK9snoe6Rr1x+1Qnx+HXztCXpzgtv95p29zHAnkV5Xf5U6okXfvtNfzBvXAZwi6nVD+MAdmuJNqxkOL7Rl5p3eow5zf2dcN+NM7Y8fvRt3xBfnrEMvzeEbPh+Ag7WsTeD+0yf3BMgTHDuQE8ofqQSemu3ApoZ02REt41/4JX1N+vBR7ShHz9jpc+2GjAm+ju/ZbH8fb8xeeh9Bae5xr/0TfkTHV98STMiSzZYKupryE8Vq6A9awP7rxUTGzH++AePbjHXA1/vcaq+u34geO73WYM5P8470z7a1Y4GuGX13o7z/TJX8uz6X+/yL/6YXnp/4vMf/r6f/mTP79Gd4o1j8zFcvnRrP5V6Zm8wvTsP7yoWX7b1T3nfM3UHJ5TAeFYfzLFqTh+AaE56l/WcOK8l7vvwNz1n9YeXD+CrcoP/Cf1xeqE/59v71heFrUnxbNP9EX+BOjL/lzeZb8Of6a+VP9Of4Jf4U1999ifZrnd/8r25l/xui2/07vRl0s8Br+/LRFePSfrAoJP1kVEv+G5WvP7y/2v1N3h4OK/UUl+yVDzEtd9EuM2F/qcoGP/Vecf5//Bm7W60vznxL7o9P7I9R9/9AS+8uqYPMXif0RZ/rz9uOH7fYK+ZP6kzp/0ud/zFt///x4jm9m/hDXM/3+SfJPzd98jevbFZ8XEnk4cKWY8P+n/gK6ZpRi:32FB
^PQ1,0,1,Y
^XZ
HERE;
                $computer_name = $print->computer_name;
                $printer_name = $print->printer_name;

//                $code = <<<HERE
//^XA^LRN^CI0^XZ^XA~TA000~JSN^LT0^MNW^MTT^PON^PMN^LH0,0^JMA^PR3,3~SD18^JUS^LRN^CI27^PA0,1,1,0^XZ^XA^MMT^PW759^LL120^LS0^FT17,100^BXN,2,200,0,0,1,_,1^FH\^FD0108421225626783213Ohs?--1Xo*aC\x1D91KZF0\x1D92Gy8+ShxEO5qYNZKbxs3oa/sT298=Gy8+ShxEO5qYNZKbxs3oa/sT298=Gy8+ShxEO5qYNZKbxs3oa/sT298=Gy8+^FS^FT177,100^BXN,2,200,0,0,1,_,1^FH\^FD01084212256267832136dRbVKIZxT2o\x1D91KZF0\x1D929JrGGQWm1zvfml7Em+kiu0XQwZM=9JrGGQWm1zvfml7Em+kiu0XQwZM=9JrGGQWm1zvfml7Em+kiu0XQwZM=9JrG^FS^FT337,100^BXN,2,200,0,0,1,_,1^FH\^FD0108421225626783213GrV'OrTGlIB2\x1D91KZF0\x1D92YAPGa3XD7vFaqdHPr3CsDQeZxww=YAPGa3XD7vFaqdHPr3CsDQeZxww=YAPGa3XD7vFaqdHPr3CsDQeZxww=YAPG^FS^FT497,100^BXN,2,200,0,0,1,_,1^FH\^FD0108421225626783213Ohs?--1Xo*aC91KZF092Gy8+ShxEO5qYNZKbxs3oa/sT298=Gy8+ShxEO5qYNZKbxs3oa/sT298=Gy8+ShxEO5qYNZKbxs3oa/sT298=Gy8+^FS^FT656,100^BXN,2,200,0,0,1,_,1^FH\^FD0108421225626783213Ohs?--1Xo*aC91KZF092Gy8+ShxEO5qYNZKbxs3oa/sT298=Gy8+ShxEO5qYNZKbxs3oa/sT298=Gy8+ShxEO5qYNZKbxs3oa/sT298=Gy8+^FS^PQ1,0,1,Y^XZ
//HERE;


                //$printer = "\\\\".$computer_name.$printer_name;
                $printer = '\\\\'.$computer_name.$printer_name;
                // Open connection to the thermal printer
                $fp = fopen($printer, "w");
                if (!$fp){
                    return response([
                        'data' => [
                            'message' => 'no connection to printer'
                        ]
                    ], 401);
                }

                if (!fwrite($fp,$code)){
                    return response(['data' => 'writing failed'], 403);
                }
            }

            return response('Success', 200);
        }
    }

    public function confirmGeneratedSSCC(Request $request)
    {
        $data = $request->all();
        $marking = Mark::findOrFail($data['mark_id']);
        $type = ($data['option_id'] == 1) ? 'box' : 'pallet';
        if($marking) {
            if($data['type'] == 1) {
                PalletSSCC::where(['task_id' => $marking->id, 'status' => 'waiting', 'type' => $type])
                    ->update(['status' => 'confirmed']);
            } else {
                PalletSSCC::where(['task_id' => $marking->id, 'status' => 'waiting', 'type' => $type])
                    ->update(['status' => 'failed']);
            }

            return response()->json('success');
        }
    }

    public function getSeria($marking_id)
    {
        $marking = Mark::findOrFail($marking_id);
        if($marking) {
            $marking_details = MarkDetail::where(['marking_id' => $marking_id])
                    ->selectRaw('id, line3')
                    ->groupBy('line3')
                    ->get();
            return response()->json($marking_details);
        }
    }

    public function aggregationPallet(Request $request)
    {
        $request->validate([
            'SSCCPal' => 'required'
        ]);

        $SSCCPal = PalletSSCC::whereCode($request->input('SSCCPal'))->first();
        if($SSCCPal) {
            $aggregation = MarkAggregation::where(['sscc_pallet_id' => $SSCCPal->id])->first();
            if($aggregation) {

                return response()->json([
                    'message' => 'SSCCPal successfully scanned',
                    'standard' => $SSCCPal->standard,
                    'lastBox' => MarkAggregation::lastBox($SSCCPal->id),
                    'fact' => MarkAggregation::factBox($SSCCPal),
                ]);
            }

            MarkAggregation::create(['sscc_pallet_id' => $SSCCPal->id]);

            return response()->json([
                'message' => 'SSCCPal successfully scanned',
                'standard' => $SSCCPal->standard,
                'lastBox' => MarkAggregation::lastBox($SSCCPal->id),
                'fact' => MarkAggregation::factBox($SSCCPal),
            ]);
        }

        return response()->json('SSCCPal not found', 403);
    }

    public function aggregationPalletBox(Request $request)
    {
        $request->validate([
            'SSCCPal' => 'required',
            'SSCCBox' => 'required'
        ]);

        $SSCCPal = PalletSSCC::whereCode($request->input('SSCCPal'))->first();
        $SSCCBox = PalletSSCC::whereCode($request->input('SSCCBox'))->first();

        if(!$SSCCPal) {
            return response()->json('SSCCPal not found', 403);
        }

        if(!$SSCCBox) {
            return response()->json('SSCCBox not found', 403);
        }

        $aggregationSSCCPal = MarkAggregation::where(['sscc_pallet_id' => $SSCCPal->id])->first();
        if(!$aggregationSSCCPal) {
            return response()->json('SSCCPal not found', 403);
        }

        if($SSCCBox) {
            $aggregationSSCCBox = MarkAggregation::where(['sscc_pallet_id' => $SSCCPal->id, 'sscc_box_id' => $SSCCBox->id])->first();
            if($aggregationSSCCBox) {
                //return response()->json('SSCCBox already scanned', 400);
                return response()->json([
                    'message' => 'SSCCBox successfully scanned',
                    'standardBox' => $SSCCBox->standard,
                    'standardPallet' => $SSCCPal->standard,
                    'lastBox' => $SSCCBox->code,
                    'fact' => MarkAggregation::where(['sscc_pallet_id' => $SSCCPal->id, 'sscc_box_id' => $SSCCBox->id])->whereNotNull('km')->count(),
                    'factBox' => MarkAggregation::factBox($SSCCPal),
                    'lastProduct' => MarkAggregation::lastProduct($SSCCPal->id, $SSCCBox->id)
                ]);
            }

            $aggregationSSCCBox = MarkAggregation::where(['sscc_pallet_id' => $SSCCPal->id, 'sscc_box_id' => null])->first();

            if($aggregationSSCCBox) {
                $aggregationSSCCBox->sscc_box_id = $SSCCBox->id;
                $aggregationSSCCBox->save();
            }

            return response()->json([
                'message' => 'SSCCBox successfully scanned',
                'standardBox' => $SSCCBox->standard,
                'fact' => MarkAggregation::where(['sscc_pallet_id' => $SSCCPal->id, 'sscc_box_id' => $SSCCBox->id])->whereNotNull('km')->count(),
                'lastProduct' => MarkAggregation::lastProduct($SSCCPal->id, $SSCCBox->id),

            ]);
        }
    }

    public function aggregationPalletBoxProduct(Request $request)
    {
        $request->validate([
            'SSCCPal' => 'required',
            'SSCCBox' => 'required',
            'km' => 'required'
        ]);

        $SSCCPal = PalletSSCC::whereCode($request->input('SSCCPal'))->first();
        $SSCCBox = PalletSSCC::whereCode($request->input('SSCCBox'))->first();

        if(!$SSCCPal) {
            return response()->json('SSCCPal not found', 403);
        }

        if(!$SSCCBox) {
            return response()->json('SSCCBox not found', 403);
        }

        $markAggregation = MarkAggregation::where(['sscc_pallet_id' => $SSCCPal->id, 'sscc_box_id' => $SSCCBox->id, 'km' => $request->input('km')])->first();
        if($markAggregation) {
            return response()->json('km already scanned', 400);
        }

        $markCode = MarkCode::where(['marking_code' => $request->input('km')])->first();
        if($markCode) {
            $markDetail = MarkDetail::findOrFail($markCode->marking_details_id);
            if(!$markDetail) {
                return response()->json('mark detail not found', 403);
            }

            $markAggregation = MarkAggregation::where(['sscc_pallet_id' => $SSCCPal->id, 'sscc_box_id' => $SSCCBox->id, 'km' => null])->first();
            if($markAggregation) {
                $markAggregation->km = $request->input('km');
                $markAggregation->article = $markDetail->line2;
                $markAggregation->gtin = $markDetail->gtin;
                $markAggregation->save();
            } else {
                MarkAggregation::create([
                    'sscc_pallet_id' => $SSCCPal->id, 'sscc_box_id' => $SSCCBox->id,
                    'km' => $request->input('km'), 'article' => $markDetail->line2, 'gtin' => $markDetail->gtin
                ]);
            }

            return response()->json([
                'message' => 'km successfully scanned',
                'standard' => $SSCCBox->standard,
                'fact' => MarkAggregation::where(['sscc_pallet_id' => $SSCCPal->id, 'sscc_box_id' => $SSCCBox->id])->whereNotNull('km')->count(),
                'lastProduct' => MarkAggregation::lastProduct($SSCCPal->id, $SSCCBox->id)
            ]);
        } else {
            return response()->json('km not found', 403);
        }
    }

    public function aggregationPalletBoxStats(Request $request)
    {
        $request->validate([
            'SSCCPal' => 'required',
            'SSCCBox' => 'required'
        ]);

        $SSCCPal = PalletSSCC::whereCode($request->input('SSCCPal'))->first();
        $SSCCBox = PalletSSCC::whereCode($request->input('SSCCBox'))->first();

        if(!$SSCCPal) {
            return response()->json('SSCCPal not found', 403);
        }

        if(!$SSCCBox) {
            return response()->json('SSCCBox not found', 403);
        }

        $aggregationSSCCPal = MarkAggregation::where(['sscc_pallet_id' => $SSCCPal->id])->first();
        if(!$aggregationSSCCPal) {
            return response()->json('SSCCPal not found', 403);
        }

        if($SSCCBox) {
            $aggregationSSCCBox = MarkAggregation::where(['sscc_pallet_id' => $SSCCPal->id, 'sscc_box_id' => $SSCCBox->id])->first();
            if($aggregationSSCCBox) {
                //return response()->json('SSCCBox already scanned', 400);
                return response()->json([
                    'message' => 'SSCCBox successfully scanned',
                    'standardBox' => $SSCCBox->standard,
                    'standardPallet' => $SSCCPal->standard,
                    'lastBox' => $SSCCBox->code,
                    'fact' => MarkAggregation::where(['sscc_pallet_id' => $SSCCPal->id, 'sscc_box_id' => $SSCCBox->id])->whereNotNull('km')->count(),
                    'factBox' => MarkAggregation::factBox($SSCCPal),
                    'lastProduct' => MarkAggregation::lastProduct($SSCCPal->id, $SSCCBox->id)
                ]);
            }

            $aggregationSSCCBox = MarkAggregation::where(['sscc_pallet_id' => $SSCCPal->id, 'sscc_box_id' => null])->first();

            if($aggregationSSCCBox) {
                $aggregationSSCCBox->sscc_box_id = $SSCCBox->id;
                $aggregationSSCCBox->save();
            }

            return response()->json([
                'message' => 'SSCCBox successfully scanned',
                'standard' => $SSCCBox->standard,
                'fact' => MarkAggregation::where(['sscc_pallet_id' => $SSCCPal->id, 'sscc_box_id' => $SSCCBox->id])->whereNotNull('km')->count(),
                'lastProduct' => MarkAggregation::lastProduct($SSCCPal->id, $SSCCBox->id),

            ]);
        }
    }

    public function getSSCC(Request $request)
    {
        $request->validate([
            'mark_id' => 'required',
            'option_id' => 'required',
        ]);

        $data = $request->all();
        $type = ($data['option_id'] == 1) ? 'box' : 'pallet';
        $sscc = PalletSSCC::where(['task_id' => $data['mark_id'], 'type' => $type])->get();
        return response()->json($sscc);
    }

    public function printSSCC(Request $request)
    {
        $arr = [];
        $printer = Printer::findOrFail($request->input('printer_id'));
        foreach(json_decode($request->input('sscc')) as $item) {
            $sscc = PalletSSCC::findOrFail($item->ID);
            $sscc_code = $sscc->code;
            $standard = $sscc->standard;
            $code = <<<HERE
^XA^LRN^CI0^XZ
^XA
~TA000
~JSN
^LT0
^MNW
^MTT
^PON
^PMN
^LH0,0
^JMA
^PR4,4
~SD28
^JUS
^LRN
^CI27
^PA0,1,1,0
^XZ
^XA
^MMT
^PW799
^LL400
^LS0
^BY2,3,91^FT100,349^BCN,,Y,N
^FH\^FD>:$sscc_code^FS
^FT40,95^A0N,39,38^FH\^CI28^FDProduct: $standard^FS^CI27
^FT40,181^A0N,37,38^FH\^CI28^FDSSCC: $sscc_code^FS^CI27
^FO551,40^GFA,821,4416,24,:Z64:eJzdl0Fu2zAQRUdRCwfpgt10TcAXyA2sHqEbo1fpwrC47K5HKNCle4dauQmXXgoFAgioIFbikNRIHCsm4BZJJpFF/zx9fY0phwJ4dlUcp/XL6aWZldPnslFWziK9snoe6Rr1x+1Qnx+HXztCXpzgtv95p29zHAnkV5Xf5U6okXfvtNfzBvXAZwi6nVD+MAdmuJNqxkOL7Rl5p3eow5zf2dcN+NM7Y8fvRt3xBfnrEMvzeEbPh+Ag7WsTeD+0yf3BMgTHDuQE8ofqQSemu3ApoZ02REt41/4JX1N+vBR7ShHz9jpc+2GjAm+ju/ZbH8fb8xeeh9Bae5xr/0TfkTHV98STMiSzZYKupryE8Vq6A9awP7rxUTGzH++AePbjHXA1/vcaq+u34geO73WYM5P8470z7a1Y4GuGX13o7z/TJX8uz6X+/yL/6YXnp/4vMf/r6f/mTP79Gd4o1j8zFcvnRrP5V6Zm8wvTsP7yoWX7b1T3nfM3UHJ5TAeFYfzLFqTh+AaE56l/WcOK8l7vvwNz1n9YeXD+CrcoP/Cf1xeqE/59v71heFrUnxbNP9EX+BOjL/lzeZb8Of6a+VP9Of4Jf4U1999ifZrnd/8r25l/xui2/07vRl0s8Br+/LRFePSfrAoJP1kVEv+G5WvP7y/2v1N3h4OK/UUl+yVDzEtd9EuM2F/qcoGP/Vecf5//Bm7W60vznxL7o9P7I9R9/9AS+8uqYPMXif0RZ/rz9uOH7fYK+ZP6kzp/0ud/zFt///x4jm9m/hDXM/3+SfJPzd98jevbFZ8XEnk4cKWY8P+n/gK6ZpRi:32FB
^PQ1,0,1,Y
^XZ
HERE;
            $computer_name = $printer->computer_name;
            $printer_name = $printer->printer_name;

            //$printer = "\\\\".$computer_name.$printer_name;
            $printer = '\\\\'.$computer_name.$printer_name;
            // Open connection to the thermal printer
            $fp = fopen($printer, "w");
            if (!$fp){
                return response([
                    'data' => [
                        'message' => 'no connection to printer'
                    ]
                ], 401);
            }

            if (!fwrite($fp,$code)){
                return response(['data' => 'writing failed'], 403);
            }

            $arr[] = [
                'code' => $sscc_code,
                'status' => 'ok'
            ];
        }

        return response()->json($arr);
    }

    public function printSSCCProduct(Request $request)
    {
        $marking = Mark::findOrFail($request->input('mark_id'));
        $printer = Printer::findOrFail($request->input('printer_id'));
        $marking_detail = MarkDetail::where(['marking_id' => $marking->id, 'line3' => $request->input('seria')])->first();
        $computer_name = $printer->computer_name;
        $printer_name = $printer->printer_name;
        if($marking_detail) {
            $marking_codes = MarkCode::where(['marking_details_id' => $marking_detail->id, 'status' => 'not_marked'])->get();

            $data = <<<HERE
^XA^LRN^CI0^XZ
^XA
~TA000
~JSN
^LT0
^MNW
^MTT
^PON
^PMN
^LH0,0
^JMA
^PR3,3
~SD18
^JUS
^LRN
^CI27
^PA0,1,1,0
^XZ
^XA
^MMT
^PW759
^LL120
^LS0
HERE;
            $arrCodesIds = [];
            $marking_codes = $marking_codes->toArray();
            foreach($marking_codes as $marking_code) {
                $code = $marking_code['marking_code'];
                $arrCodesIds[] = $marking_code['id'];
                switch ($this->i) {
                    case 1:
                        $data .= <<<HERE
^FT17,100^BXN,2,200,0,0,1,_,1
^FH\^FD$code^FS
HERE;
                        break;

                    case 2:
                        $data .= <<<HERE
^FT177,100^BXN,2,200,0,0,1,_,1
^FH\^FD$code^FS
HERE;
                        break;

                    case 3:
                        $data .= <<<HERE
^FT337,100^BXN,2,200,0,0,1,_,1
^FH\^FD$code^FS
HERE;
                        break;

                    case 4:
                        $data .= <<<HERE
^FT497,100^BXN,2,200,0,0,1,_,1
^FH\^FD$code^FS
HERE;
                        break;

                    case 5:
                        $data .= <<<HERE
^FT656,100^BXN,2,200,0,0,1,_,1
^FH\^FD$code^FS
HERE;
                        break;
                }

                if($marking_code == end($marking_codes)) {
                    //$r = 5 - $this->i;
                    $data .= <<<HERE

HERE;

                    $this->i = 5;
                }


                if($this->i == 5) {
                    $data .= <<<HERE
^PQ1,0,1,Y
^XZ
HERE;

                    $printer = '\\\\'.$computer_name.$printer_name;
                    // Open connection to the thermal printer
                    $fp = fopen($printer, "w");
                    if (!$fp){
                        return response([
                            'data' => [
                                'message' => 'no connection to printer'
                            ]
                        ], 401);
                    }

                    if (!fwrite($fp,$data)){
                        return response(['data' => 'writing failed'], 403);
                    }

                    $this->i = 0;

//                    MarkCode::whereIn('id', $arrCodesIds)
//                        ->update(['status' => 'marked', 'scan_user_id' => Auth::id()]);
//                    $arrCodesIds = [];
                }

                $this->i++;
            }
        }
    }
}
