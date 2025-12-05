<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\BaseController;
use App\Models\Cargo\Cargo;
use App\Models\Cargo\CargoArea;
use App\Models\Cargo\CargoLog;
use App\Models\Cargo\CargoService;
use App\Models\Cargo\CargoStock;
use App\Models\Cargo\CargoTask;
use App\Models\Cargo\CargoTaskHistory;
use App\Models\Company;
use App\Models\Spine;
use App\Models\SpineCode;
use App\Models\Technique;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class CargoKeeperController extends BaseController
{
    public function controller()
    {
        return view('cargo.controller.orders');
    }

    public function getCargoTasks(Request $request): \Illuminate\Http\JsonResponse
    {
        $query  = CargoTask::with('user', 'company', 'history')
            ->selectRaw('cargo_tasks.*, companies.short_en_name')
            ->join('companies', 'companies.id', '=', 'cargo_tasks.company_id')
            ->orderBy('id', 'DESC');

        if ($request->has('status') && !empty($request->status)) {
            $status = $request->status;
            $query->where(function ($q) use ($status) {
                $q->where('cargo_tasks.status', '=', $status);
            });
        }

        $cargoTasks = $query->get();

        foreach($cargoTasks as $cargoTask){
            if($cargoTask->canClose()) {
                $cargoTask->isClose = true;
            } else {
                $cargoTask->isClose = false;
            }

            $cargoTask->spine = $cargoTask->getSpine();

            $cargoTask->number = $cargoTask->getNumber();
        }

        return response()->json($cargoTasks);
    }

    public function showCargoTask($cargoTaskId)
    {
        $cargoTask = CargoTask::with('services')->findOrFail($cargoTaskId);
        $cargoStocks = CargoStock::where(['cargo_stocks.cargo_task_id' => $cargoTaskId])
            ->selectRaw('cargo_stocks.*, cargo.name as cargo_name')
            ->join('cargo', 'cargo.id', '=', 'cargo_stocks.cargo_id')
            ->get();

        return view('cargo.controller.order-show', compact('cargoTask', 'cargoStocks'));
    }

    public function startCargoTask($cargoTaskId, $cargoStockId)
    {
        $cargoTask = CargoTask::findOrFail($cargoTaskId);
        $cargoStock = CargoStock::with('cargo')->findOrFail($cargoStockId);

        return view('cargo.controller.order-position', compact('cargoTask', 'cargoStock'));
    }

    public function editCargoTask($cargoTaskId, $cargoStockId)
    {
        $cargoTask = CargoTask::findOrFail($cargoTaskId);
        $cargoStock = CargoStock::with('cargo')->findOrFail($cargoStockId);
        $cargoLog = CargoLog::where(['cargo_id' => $cargoStock->cargo_id, 'cargo_task_id' => $cargoStock->cargo_task_id])
            ->whereIn('action_type', ['received', 'edited'])
            ->orderBy('created_at', 'DESC')
            ->first();

        return view('cargo.controller.order-edit', compact('cargoTask', 'cargoStock', 'cargoLog'));
    }

    public function getTechniques(): \Illuminate\Http\JsonResponse
    {
        $techniques = Technique::orderBy('name')->get();
        return response()->json($techniques);
    }

    public function getEmployees(): \Illuminate\Http\JsonResponse
    {
        $users = User::where(['company_id' => Auth::user()->company_id])
            ->orderBy('full_name', 'asc')
            ->get();
        return response()->json($users);
    }

    public function getAreas(): \Illuminate\Http\JsonResponse
    {
        return response()->json(CargoArea::all());
    }

    public function fixOperations(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->all();
        //dd($data, json_decode($data['services'], true));
        DB::beginTransaction();
        //dd($data);
        try {
            $cargoTask = CargoTask::findOrFail($data['cargoTaskId']);
            $cargoStock = CargoStock::findOrFail($data['cargoStockId']);

            $cargoData = $cargoStock->attributesToArray();
            $services = json_decode($data['services'], true);

            $cargoData['user_id'] = Auth::user()->id;
            $cargoData['user_name'] = Auth::user()->full_name;

            $cargoData['square'] = $data['square'];

            $technique_ids = "";
            $user_ids = "";
            $count_operations = "";

            if($cargoTask->type == 'receive') {
                // прием / завоз
                $cargoStock->cargo_area_id = $data['cargoAreaId'];
                $cargoStock->status = 'received';
                $cargoStock->save();

                if(CargoTaskHistory::exists($cargoTask->id, $cargoStock->cargo_id)) {
                    CargoTaskHistory::where(['cargo_task_id' => $cargoTask->id, 'cargo_id' => $cargoStock->cargo_id])
                        ->update(['status' => 'received']);
                }

                if($cargoTask && $cargoTask->status == 'new') {
                    $cargoTask->status = 'processing';
                    $cargoTask->save();
                }



                $cargoData['cargo_name'] = Cargo::findOrFail($cargoStock->cargo_id)->name;
                $cargoData['address_from'] = 'cloud';
                $cargoData['address_to'] = CargoArea::findOrFail($data['cargoAreaId'])->name;
                $cargoData['action_type'] = 'received';

                // TODO: нужно подумать сразу закрывать заявку или пусть закрывает РО
                /*if($cargoTask->canClose()){
                    $cargoTask->status = 'completed';
                    $cargoTask->save();
                }*/
            } else {
                // выдача / вывоз
                $cargoStock->status = 'shipped';
                $cargoStock->save();

                if(CargoTaskHistory::exists($cargoTask->id, $cargoStock->cargo_id)) {
                    CargoTaskHistory::where(['cargo_task_id' => $cargoTask->id, 'cargo_id' => $cargoStock->cargo_id])
                        ->update(['status' => 'shipped']);
                }

                if($cargoTask && $cargoTask->status == 'new') {
                    $cargoTask->status = 'processing';
                    $cargoTask->save();
                }

                $cargoArea = CargoArea::findOrFail($cargoStock->cargo_area_id);

                $cargoData['user_id'] = Auth::user()->id;
                $cargoData['user_name'] = Auth::user()->full_name;
                $cargoData['cargo_name'] = Cargo::findOrFail($cargoStock->cargo_id)->name;
                $cargoData['address_from'] = $cargoArea->name;
                $cargoData['address_to'] = $cargoArea->name;
                $cargoData['action_type'] = 'shipped';

                // TODO: нужно подумать сразу закрывать заявку или пусть закрывает РО
                /*if($cargoTask->canClose()){
                    $cargoTask->status = 'completed';
                    $cargoTask->save();
                }*/

                // если заявка готово к закрытию, то автоматические создаем корешок для вывоза
                if ($cargoTask->canClose()) {
                    $dataForSpine = [];
                    $dataForSpine['user_name'] = Auth::user()->full_name;
                    $company = Company::findOrFail($cargoTask->company_id);
                    $dataForSpine['company'] = $company->short_en_name;
                    $dataForSpine['company_id'] = $cargoTask->company_id;
                    $dataForSpine['spine_number'] = Spine::generateUniqueNumber($cargoTask->type);
                    $dataForSpine['kind'] = 'cargo';
                    $dataForSpine['name'] = count($cargoTask->stocks) . " шт";
                    $dataForSpine['task_number'] = $cargoTask->getNumber();
                    $dataForSpine['type'] = $cargoTask->type;
                    $dataForSpine['car_number'] = $cargoStock->car_number;
                    $dataForSpine['driver_name'] = $cargoStock->driver_name;

                    $spine = Spine::create($dataForSpine);

                    foreach($cargoTask->stocks as $item) {
                        SpineCode::create([
                            'spine_id' => $spine->id, 'vin_code' => $item->vin_code, 'task_id' => $cargoTask->id,
                        ]);
                    }
                }
            }

            foreach($services as $service) {
                if(!empty($service['userId'])) {
                    $user_ids .= implode(",", $service['userId']);
                }

                $technique_ids .= $service['techniqueId'].',';

                if(!is_null($service['count_operations'])) {
                    $count_operations .= $service['count_operations'].',';
                }
            }

            $cargoData['technique_ids'] = trim($technique_ids, ',');
            $cargoData['user_ids'] = trim($user_ids, ',');
            $cargoData['count_operations'] = trim($count_operations, ',');

            CargoLog::create($cargoData);

            if($request->hasFile('file')) {
                $dir = '/cargo_files/'. substr(md5(microtime()), mt_rand(0, 30), 2);
                if(!File::isDirectory(public_path(). $dir)){
                    File::makeDirectory(public_path(). $dir, 0777, true);
                }

                $upload_file = $request->file('file');
                $filename = substr(md5(time()), 0, 5) . "_" . $upload_file->getClientOriginalName();
                $path_to_file = $dir.'/'.$filename;
                $upload_file->move(public_path() . $dir, $filename);
                $cargoStock->image = $path_to_file;
                $cargoStock->save();

                CargoTaskHistory::where(['cargo_task_id' => $cargoTask->id, 'cargo_id' => $cargoStock->cargo_id])
                    ->update(['image' => $path_to_file]);
            }

            DB::commit();
            return response()->json('success');
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function changeTasks(Request $request): \Illuminate\Http\JsonResponse
    {
        $cargoTaskId = $request->input('cargoTaskId');
        $comments = $request->input('comments');
        $cargoServiceIds = $request->input('cargoServiceIds');

        $cargoTask = CargoTask::findOrFail($cargoTaskId);

        $cargoTask->comments = $comments;
        $cargoTask->save();

        if(!empty($cargoServiceIds)) {
            $cargoTask->services()->detach();
            foreach(json_decode($cargoServiceIds, true) as $cargoServiceId) {
                $cargoService = CargoService::findOrFail($cargoServiceId);
                $cargoTask->services()->attach($cargoService);
            }
        }

        return response()->json('success');
    }

    public function getCargoServices(): \Illuminate\Http\JsonResponse
    {
        $cargoServices = CargoService::orderBy('name', 'ASC')->get();
        return response()->json($cargoServices);
    }
}
