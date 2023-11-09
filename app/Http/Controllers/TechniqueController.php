<?php

namespace App\Http\Controllers;

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

            $technique_task = TechniqueTask::create([
                'user_id' => Auth::id(), 'task_type' => $data['task_type'], 'trans_type' => $data['trans_type'], 'status' => 'open',
                'upload_file' => $path_to_file
            ]);

            if($data['task_type'] == 'receive') {
                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify(public_path($path_to_file));
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                $spreadsheet = $reader->load(public_path($path_to_file));
                $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

                foreach ($sheetData as $key=>$arr) {
                    if ($key == 1) continue;
                    $owner = $arr['A'];
                    $technique_type = $arr['B'];
                    $mark = $arr['C'];
                    $vin_code = $arr['D'];

                    $technique_type = TechniqueType::where(['name' => $technique_type])->first();
                    if(!$technique_type) {
                        dd("Тип техники не найдено в справочнике: " . $technique_type);
                    }

                    TechniqueStock::create([
                        'technique_task_id' => $technique_task->id, 'technique_type_id' => $technique_type->id, 'owner' => $owner, 'mark' => $mark,
                        'vin_code' => $vin_code, 'status' => 'incoming'
                    ]);

                    TechniqueLog::create([
                        'user_id' => Auth::id(), 'technique_task_id' => $technique_task->id, 'technique_type' => $technique_type->name, 'owner' => $owner, 'mark' => $mark,
                        'vin_code' => $vin_code, 'operation_type' => 'incoming', 'address_from' => 'from file', 'address_to' => 'cloud'
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

                        TechniqueLog::create([
                            'user_id' => Auth::id(), 'technique_task_id' => $technique_task->id, 'technique_type' => $technique_stock->technique_type->name, 'owner' => $technique_stock->owner, 'mark' => $technique_stock->mark,
                            'vin_code' => $vin_code, 'operation_type' => 'in_order', 'address_from' => $technique_place->name, 'address_to' => $technique_place->name
                        ]);
                    }
                }
            }

            DB::commit();

            return response('success');

        } catch (\Exception $exception){
            DB::rollBack();
            dd("Error: " . $exception->getMessage());
        }
    }

    public function getTechniqueTasks()
    {
        //$technique_tasks = TechniqueTask::all();
        $technique_tasks = TechniqueTask::orderBy('id', 'DESC')->get();

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
        $stocks = $technique_task->stocks;
        return view('technique.details', compact('stocks'));
    }
}
