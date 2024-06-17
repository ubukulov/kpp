<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Company;
use App\Models\Spine;
use App\Models\SpineCode;
use App\Models\TechniqueLog;
use App\Models\TechniqueStock;
use App\Models\TechniqueTask;
use App\Models\TechniqueType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use File;
use Auth;

class TechniqueController extends Controller
{
    public function techniqueController()
    {
        return view('technique.technique_controller');
    }

    public function taskCreate()
    {
        $technique_types = TechniqueType::all();
        return view('technique.create_task', compact('technique_types'));
    }

    public function createTaskByFile(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->except('upload_file');
            // Подготовка папок для сохранение файлов
            $dir = '/technique_files/'. substr(md5(microtime()), mt_rand(0, 30), 2);
            if(!File::isDirectory(public_path(). $dir)){
                File::makeDirectory(public_path(). $dir, 0777, true);
            }

            $upload_file = $request->file('upload_file');
            $filename = substr(md5(time()), 0, 5) . "_" . $upload_file->getClientOriginalName();
            $path_to_file = $dir.'/'.$filename;
            $upload_file->move(public_path() . $dir, $filename);

            $data['user_id'] = Auth::id();
            $data['status'] = 'open';
            $data['upload_file'] = $path_to_file;

            $technique_task = TechniqueTask::create($data);

            if($data['task_type'] == 'receive') {
                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(public_path($path_to_file));
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                $spreadsheet = $reader->load(public_path($path_to_file));
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                foreach ($sheetData as $key=>$arr) {
                    if ($key == 1) continue;
                    $color = $arr['A'];
                    $technique_type = $arr['B'];
                    $mark = $arr['C'];
                    $vin_code = trim($arr['D']);

                    $technique_type = TechniqueType::where(['name' => $technique_type])->first();
                    if(!$technique_type) {
                        $technique_type = TechniqueType::findOrFail(1);
                        //dd("Тип техники не найдено в справочнике: " . $technique_type);
                    }

                    TechniqueStock::create([
                        'technique_task_id' => $technique_task->id, 'technique_type_id' => $technique_type->id, 'color' => $color, 'mark' => $mark,
                        'vin_code' => $vin_code, 'status' => 'incoming', 'company_id' => $data['company_id']
                    ]);

                    $company = Company::findOrFail($data['company_id']);

                    TechniqueLog::create([
                        'user_id' => Auth::id(), 'technique_task_id' => $technique_task->id, 'technique_type' => $technique_type->name, 'color' => $color, 'mark' => $mark,
                        'vin_code' => $vin_code, 'operation_type' => 'incoming', 'address_from' => 'from file', 'address_to' => 'cloud', 'owner' => $company->full_company_name
                    ]);
                }
            } else {
                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(public_path($path_to_file));
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                $spreadsheet = $reader->load(public_path($path_to_file));
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                foreach ($sheetData as $key=>$arr) {
                    if ($key == 1) continue;
                    $vin_code = $arr['A'];
                    $technique_stock = TechniqueStock::where(['vin_code' => $vin_code])->first();
                    if($technique_stock) {
                        $technique_stock->status = 'in_order';
                        $technique_stock->technique_task_id = $technique_task->id;
                        $technique_stock->save();

                        $technique_place = $technique_stock->technique_place;

                        $company = Company::findOrFail($technique_stock->company_id);

                        TechniqueLog::create([
                            'user_id' => Auth::id(), 'technique_task_id' => $technique_task->id, 'technique_type' => $technique_stock->technique_type->name, 'color' => $technique_stock->color, 'mark' => $technique_stock->mark,
                            'vin_code' => $vin_code, 'operation_type' => 'in_order', 'address_from' => $technique_place->name, 'address_to' => $technique_place->name, 'owner' => $company->full_company_name
                        ]);
                    }
                }
            }

            DB::commit();

            return response('success');

        } catch (\Exception $exception){
            DB::rollBack();
            return response()->json($exception->getMessage(), 400);
        }
    }

    public function createTaskByKeyboard(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['user_id'] = Auth::id();
            $data['status'] = 'open';

            $technique_task = TechniqueTask::create($data);

            if($data['task_type'] == 'receive') {
                $technique_type = TechniqueType::findOrFail($data['technique_type_id']);

                TechniqueStock::create([
                    'technique_task_id' => $technique_task->id, 'technique_type_id' => $technique_type->id, 'color' => $data['color'], 'mark' => $data['mark'],
                    'vin_code' => $data['vin_code'], 'status' => 'incoming', 'company_id' => $data['company_id']
                ]);

                $company = Company::findOrFail($data['company_id']);

                TechniqueLog::create([
                    'user_id' => Auth::id(), 'technique_task_id' => $technique_task->id, 'technique_type' => $technique_type->name, 'color' => $data['color'], 'mark' => $data['mark'],
                    'vin_code' => $data['vin_code'], 'operation_type' => 'incoming', 'address_from' => 'from file', 'address_to' => 'cloud', 'owner' => $company->full_company_name
                ]);
            } else {
                $technique_stock = TechniqueStock::where(['vin_code' => $data['vin_code']])->first();
                if($technique_stock) {
                    $technique_stock->status = 'in_order';
                    $technique_stock->technique_task_id = $technique_task->id;
                    $technique_stock->save();

                    $technique_place = $technique_stock->technique_place;

                    $company = Company::findOrFail($technique_stock->company_id);

                    TechniqueLog::create([
                        'user_id' => Auth::id(), 'technique_task_id' => $technique_task->id, 'technique_type' => $technique_stock->technique_type->name, 'color' => $technique_stock->color, 'mark' => $technique_stock->mark,
                        'vin_code' => $data['vin_code'], 'operation_type' => 'in_order', 'address_from' => $technique_place->name, 'address_to' => $technique_place->name, 'owner' => $company->full_company_name
                    ]);
                }
            }

            DB::commit();

            return response('success');

        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json($exception->getMessage(), 400);
        }
    }

    public function getTechniqueTasks()
    {
        $technique_tasks = TechniqueTask::where('technique_tasks.status', '!=', 'canceled')->orderBy('id', 'DESC')
            ->selectRaw('technique_tasks.*, companies.short_en_name')
            ->leftJoin('companies', 'companies.id', 'technique_tasks.company_id')
            ->get();

        $tasks = [];

        foreach($technique_tasks as $task) {
            $task['number'] = $task->getNumber();
            $task['type'] = $task->getType();
            $task['trans'] = $task->getTransType();
            $task['user'] = $task->user;
            $tasks[] = $task;
        }

        return response()->json($tasks);
    }

    public function showDetails($id)
    {
        $technique_task = TechniqueTask::findOrFail($id);
        if($technique_task->status == 'closed' && $technique_task->task_type == 'ship') {
            $stocks = TechniqueLog::where(['technique_task_id' => $technique_task->id, 'operation_type' => 'shipped'])
                ->get();
        } else {
            $stocks = TechniqueStock::where(['technique_stocks.technique_task_id' => $technique_task->id])
                ->selectRaw('technique_stocks.*, companies.short_en_name, technique_types.name as technique_type')
                ->join('companies', 'companies.id', 'technique_stocks.company_id')
                ->join('technique_types', 'technique_types.id', 'technique_stocks.technique_type_id')
                ->get();
        }

        return view('technique.details', compact('stocks', 'technique_task'));
    }

    public function getTechniqueCompanies()
    {
        $companies = Company::where(['type_company' => 'technique'])->get();
        return response()->json($companies);
    }

    public function getAgreements($company_id)
    {
        $agreements = Agreement::where(['company_id' => $company_id])
                ->selectRaw('id, CONCAT(name, " на имя ", full_name) as name')
                ->get();
        return response()->json($agreements);
    }

    public function storeAgreement(Request $request, $company_id)
    {
        $data = $request->except('agreement_file');
        $data['user_id'] = Auth::id();

        $agreement = Agreement::create($data);

        if($request->hasFile('agreement_file')) {
            $dir = '/technique_files/'. substr(md5(microtime()), mt_rand(0, 30), 2);
            if(!File::isDirectory(public_path(). $dir)){
                File::makeDirectory(public_path(). $dir, 0777, true);
            }

            $upload_file = $request->file('agreement_file');
            $filename = substr(md5(time()), 0, 5) . "_" . $upload_file->getClientOriginalName();
            $path_to_file = $dir.'/'.$filename;
            $upload_file->move(public_path() . $dir, $filename);
            $agreement->agreement_file = $path_to_file;
            $agreement->save();
        }

        return response()->json($agreement);
    }

    public function deleteAgreement()
    {

    }

    public function storeSpine(Request $request)
    {
        try {
            $data = $request->all();
            $data['user_name'] = Auth::user()->full_name;
            $company = Company::findOrFail($data['company_id']);
            $data['company'] = $company->short_en_name;
            $data['spine_number'] = Spine::generateUniqueNumber($data['type']);
            $technique_task = TechniqueTask::where(['company_id' => $data['company_id'], 'task_type' => $data['type'], 'status' => 'open'])
                ->first();
            if($technique_task) {
                $data['technique_task_number'] = $technique_task->getNumber();

                $spine = Spine::create($data);

                if($data['selectedCodes']) {
                    $codes = json_decode($data['selectedCodes']);
                    foreach($codes as $item) {
                        SpineCode::create([
                            'spine_id' => $spine->id, 'technique_task_id' => $technique_task->id, 'vin_code' => $item->code,
                        ]);
                    }
                }

                return response()->json($spine);
            }

            return response()->json('Not found open tasks', 400);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json('errors', 500);
        }
    }

    public function spinePrintView($spine_id)
    {
        $spine = Spine::findOrFail($spine_id);
        return view('technique.spine', compact('spine'));
    }

    public function getSpineVincodes(Request $request)
    {
        $data = $request->all();
        $technique_tasks = TechniqueTask::where(['company_id' => $data['company_id'], 'task_type' => $data['type'], 'status' => 'open'])
                ->get();
        $codes = [];
        foreach($technique_tasks as $technique_task) {
            $stocks = $technique_task->stocks;
            foreach($stocks as $stock) {
                if($stock->status == 'shipped' && !SpineCode::exists($technique_task->id, $stock->vin_code)) {
                    $codes[] = [
                        'id' => $stock->id,
                        'code' => $stock->vin_code
                    ];
                }
            }
        }

        return response()->json($codes);
    }
}
