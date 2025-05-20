<?php

namespace App\Http\Controllers;

use App\Models\Cargo\Cargo;
use App\Models\Cargo\CargoArea;
use App\Models\Cargo\CargoLog;
use App\Models\Cargo\CargoStock;
use App\Models\Cargo\CargoTask;
use App\Models\Cargo\CargoTaskHistory;
use App\Models\Company;
use App\Models\Spine;
use App\Models\SpineCode;
use App\Models\Technique;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use File;
use App\Models\Cargo\CargoService;

class CargoController extends BaseController
{
    public function getCargoTasks(): \Illuminate\Http\JsonResponse
    {
        $cargoTasks  = CargoTask::with('user', 'company', 'history')
            ->selectRaw('cargo_tasks.*, companies.short_en_name')
            ->join('companies', 'companies.id', '=', 'cargo_tasks.company_id')
            ->orderBy('id', 'DESC')
            ->get();

        foreach($cargoTasks as $cargoTask){
            if($cargoTask->canClose()) {
                $cargoTask->isClose = true;
            } else {
                $cargoTask->isClose = false;
            }
        }
        return response()->json($cargoTasks);
    }

    public function getCargoCompanies(): \Illuminate\Http\JsonResponse
    {
        return response()->json(Company::where('type_company', 'cargo')->get());
    }

    public function getCargoNames(): \Illuminate\Http\JsonResponse
    {
        return response()->json(Cargo::orderBy('name', 'ASC')->get());
    }

    /*
     * Создание заявку на завоз или вывоз
     * */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->all();
        //dd(json_decode($data['cargoData'])->gov_number);
        DB::beginTransaction();
        try {
            $user = Auth::user();
            $cargoTask = CargoTask::create([
                'user_id' => Auth::id(), 'company_id' => $data['company_id'], 'type' => ($data['orderTypeId'] == 1) ? 'receive' : 'ship', 'status' => 'new',
            ]);

            if($data['orderTypeId'] == 1) {
                if ($data['cargoTypeId'] == 1) {
                    $cargoTask->cargo = 'disassembled';
                } else {
                    $cargoTask->cargo = 'self-propelled';
                }

                $cargoTask->save();
            }

            $cargoData = json_decode($data['cargoData']);

            if($data['orderTypeId'] == 1) {
                // заявки на прием/завоз

                if($data['cargoTypeId'] == 1) {
                    // прием груз: разобранный
                    $cargoMainName = Cargo::findOrFail($cargoData->cargoNameId)->name;
                    foreach($cargoData->rows as $cargoRow) {
                        $carNumber = ($cargoData->oneCar) ? $cargoData->car_number : $cargoRow->carNumber;
                        $cargoName = Cargo::findOrFail($cargoRow->cargoNameId)->name;
                        CargoStock::create([
                            'cargo_id' => $cargoRow->cargoNameId, 'cargo_task_id' => $cargoTask->id, 'vin_code' => $cargoData->vin_code,
                            'quantity' => $cargoRow->quantity, 'weight' => $cargoRow->weight, 'status' => 'incoming',
                            'car_number' => $carNumber,
                            'cargo_information' => implode("|", [$cargoMainName, $cargoName])
                        ]);

                        CargoTaskHistory::create([
                            'user_id' => $user->id, 'cargo_task_id' => $cargoTask->id, 'vin_code' => $cargoData->vin_code,
                            'status' => 'incoming', 'cargo_id' => $cargoRow->cargoNameId
                        ]);

                        CargoLog::create([
                            'user_id' => $user->id, 'cargo_id' => $cargoData->cargoNameId, 'cargo_task_id' => $cargoTask->id,
                            'user_name' => $user->full_name, 'cargo_name' => implode("|", [$cargoMainName, $cargoName]),
                            'vin_code' => $cargoData->vin_code, 'car_number' => $carNumber, 'address_from' => 'cloud',
                            'address_to' => 'cloud', 'action_type' => 'incoming', 'quantity' => $cargoRow->quantity, 'weight' => $cargoRow->weight,
                        ]);
                    }

                    Spine::where(['company_id' => $data['company_id'], 'car_number' => $cargoData->car_number])
                            ->whereNull('task_number')
                            ->update(['task_number' => $cargoTask->getNumber()]);
                } else {
                    // прием груз: самоходом
                    foreach($cargoData->rows as $cargoRow) {
                        $cargoName = Cargo::findOrFail($cargoRow->cargoNameId)->name;
                        CargoStock::create([
                            'cargo_id' => $cargoRow->cargoNameId, 'cargo_task_id' => $cargoTask->id, 'vin_code' => $cargoRow->vin_code,
                            'quantity' => $cargoRow->quantity, 'weight' => $cargoRow->weight, 'status' => 'incoming',
                            'cargo_information' => $cargoName, 'cargo_type' => 2
                        ]);

                        // находим в корешке
                        $spineCode = SpineCode::where(['vin_code' => $cargoRow->vin_code])
                                    ->whereNull('task_id')
                                    ->first();

                        $spineCode->task_id = $cargoTask->id;
                        $spineCode->save();

                        Spine::where(['company_id' => $data['company_id'], 'id' => $spineCode->spine_id])
                            ->whereNull('task_number')
                            ->update(['task_number' => $cargoTask->getNumber()]);

                        CargoTaskHistory::create([
                            'user_id' => $user->id, 'cargo_task_id' => $cargoTask->id, 'vin_code' => $cargoRow->vin_code,
                            'status' => 'incoming', 'cargo_id' => $cargoRow->cargoNameId
                        ]);

                        CargoLog::create([
                            'user_id' => $user->id, 'cargo_id' => $cargoRow->cargoNameId, 'cargo_task_id' => $cargoTask->id,
                            'user_name' => $user->full_name, 'cargo_name' => $cargoName,
                            'vin_code' => $cargoRow->vin_code, 'address_from' => 'cloud', 'address_to' => 'cloud',
                            'action_type' => 'incoming', 'quantity' => $cargoRow->quantity, 'weight' => $cargoRow->weight,
                        ]);
                    }
                }
            } else {
                // заявки на выдачу/вывоз
                if($request->hasFile('file')) {
                    $dir = '/cargo_files/'. substr(md5(microtime()), mt_rand(0, 30), 2);
                    if(!File::isDirectory(public_path(). $dir)){
                        File::makeDirectory(public_path(). $dir, 0777, true);
                    }

                    $upload_file = $request->file('file');
                    $filename = substr(md5(time()), 0, 5) . "_" . $upload_file->getClientOriginalName();
                    $path_to_file = $dir.'/'.$filename;
                    $upload_file->move(public_path() . $dir, $filename);
                    $cargoTask->upload_file = $path_to_file;
                    $cargoTask->save();
                }

                $cargoTask->agreement_id = $cargoData->agreement_id;
                $cargoTask->save();

                //dd($cargoData->quantities);

                foreach($cargoData->selectedCodes as $cargoRow) {
                    $requested = collect($cargoData->quantities)->firstWhere('vin_code', $cargoRow->vin_code);
                    $quantityToIssue = $requested->quantity ?? 0; // защита от null

                    $cargoStock = CargoStock::findOrFail($cargoRow->id);
                    $cargoStock->cargo_task_id = $cargoTask->id;
                    $cargoStock->status = 'in_order';
                    $cargoStock->quantity_reserved = $quantityToIssue;
                    $cargoStock->car_number = $cargoData->gov_number;
                    $cargoStock->driver_name = $cargoData->driver_name;
                    $cargoStock->save();

                    if(!CargoTaskHistory::exists($cargoTask->id, $cargoStock->cargo_id)) {
                        CargoTaskHistory::create([
                            'user_id' => $user->id, 'cargo_task_id' => $cargoTask->id, 'vin_code' => $cargoStock->vin_code,
                            'status' => 'in_order', 'cargo_id' => $cargoStock->cargo_id
                        ]);
                    }

                    $cargoArea = CargoArea::findOrFail($cargoStock->cargo_area_id);

                    $cargoLogData = $cargoStock->attributesToArray();
                    $cargoLogData['user_id'] = Auth::user()->id;
                    $cargoLogData['user_name'] = Auth::user()->full_name;
                    $cargoLogData['cargo_name'] = Cargo::findOrFail($cargoStock->cargo_id)->name;
                    $cargoLogData['address_from'] = $cargoArea->name;
                    $cargoLogData['address_to'] = $cargoArea->name;
                    $cargoLogData['car_number'] = $cargoRow->car_number;
                    $cargoLogData['action_type'] = 'in_order';

                    CargoLog::create($cargoLogData);
                }
            }

            DB::commit();
            return response()->json('success');
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function update(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $data = $request->all();
        $user = Auth::user();
        $cargoData = json_decode($data['cargoData']);
        $cargoTask = CargoTask::findOrFail($id);

        if($data['orderTypeId'] == 1) {
            // заявки на прием/завоз

            if($data['cargoTypeId'] == 1) {
                // прием груз: сборная

                foreach($cargoData->rows as $cargoRow) {

                    if(is_null($cargoRow->cargoStockId)) {
                        // при редактирование добавили новую позицию
                        CargoStock::create([
                            'cargo_id' => $cargoRow->cargoNameId, 'cargo_task_id' => $cargoTask->id, 'vin_code' => $cargoData->vin_code,
                            'quantity' => $cargoRow->quantity, 'weight' => $cargoRow->weight, 'status' => 'incoming',
                            'car_number' => $cargoRow->carNumber
                        ]);

                        CargoTaskHistory::create([
                            'user_id' => $user->id, 'cargo_task_id' => $cargoTask->id, 'vin_code' => $cargoData->vin_code,
                            'status' => 'incoming', 'cargo_id' => $cargoRow->cargoNameId
                        ]);

                        CargoLog::create([
                            'user_id' => $user->id, 'cargo_id' => $cargoData->cargoNameId, 'cargo_task_id' => $cargoTask->id,
                            'user_name' => $user->full_name, 'cargo_name' => Cargo::find($cargoRow->cargoNameId)->name,
                            'vin_code' => $cargoData->vin_code, 'car_number' => $cargoRow->carNumber, 'address_from' => 'cloud',
                            'address_to' => 'cloud', 'action_type' => 'incoming', 'quantity' => $cargoRow->quantity, 'weight' => $cargoRow->weight,
                        ]);
                    } else {
                        $cargoStock = CargoStock::findOrFail($cargoRow->cargoStockId);

                        if($cargoStock->cargo_id != $cargoData->cargoNameId) {
                            $cargoStock->cargo_id = $cargoData->cargoNameId;
                        }

                        if($cargoStock->quantity != $cargoRow->quantity) {
                            $cargoStock->quantity = $cargoRow->quantity;
                        }

                        if($cargoStock->weight != $cargoRow->weight) {
                            $cargoStock->weight = $cargoRow->weight;
                        }

                        if($cargoStock->car_number != $cargoRow->car_number) {
                            $cargoStock->car_number = $cargoRow->car_number;
                        }

                        $cargoStock->save();
                    }

                    //TODO: Реализовать изменение в логах и в историях к заявку.
                }
            } else {
                // прием груз: самоходом
                foreach($cargoData->rows as $cargoRow) {

                    if(is_null($cargoRow->cargoStockId)) {
                        // при редактирование добавили новую позицию
                        CargoStock::create([
                            'cargo_id' => $cargoRow->cargoNameId, 'cargo_task_id' => $cargoTask->id, 'vin_code' => $cargoRow->vin_code,
                            'quantity' => $cargoRow->quantity, 'weight' => $cargoRow->weight, 'status' => 'incoming'
                        ]);

                        CargoTaskHistory::create([
                            'user_id' => $user->id, 'cargo_task_id' => $cargoTask->id, 'vin_code' => $cargoRow->vin_code,
                            'status' => 'incoming', 'cargo_id' => $cargoRow->cargoNameId
                        ]);

                        CargoLog::create([
                            'user_id' => $user->id, 'cargo_id' => $cargoRow->cargoNameId, 'cargo_task_id' => $cargoTask->id,
                            'user_name' => $user->full_name, 'cargo_name' => Cargo::find($cargoRow->cargoNameId)->name,
                            'vin_code' => $cargoRow->vin_code, 'address_from' => 'cloud', 'address_to' => 'cloud',
                            'action_type' => 'incoming', 'quantity' => $cargoRow->quantity, 'weight' => $cargoRow->weight,
                        ]);
                    } else {
                        $cargoStock = CargoStock::findOrFail($cargoRow->cargoStockId);

                        if($cargoStock->cargo_id != $cargoRow->cargoNameId) {
                            $cargoStock->cargo_id = $cargoData->cargoNameId;
                        }

                        if($cargoStock->weight != $cargoRow->weight) {
                            $cargoStock->weight = $cargoRow->weight;
                        }

                        if($cargoStock->vin_code != $cargoRow->vin_code) {
                            $cargoStock->vin_code = $cargoRow->vin_code;
                        }

                        $cargoStock->save();
                    }
                }
            }
        } else {
            // заявки на выдачу/вывоз

        }

        return response()->json('success', 200);
    }

    public function getCargoStocksForShipment($companyId): \Illuminate\Http\JsonResponse
    {
        $cargoStocks = CargoStock::where(['cargo_tasks.company_id' => $companyId, 'cargo_stocks.status' => 'received'])
            ->selectRaw('cargo_stocks.*, cargo.name as cargo_name')
            ->join('cargo_tasks', 'cargo_tasks.id', '=', 'cargo_stocks.cargo_task_id')
            ->join('cargo', 'cargo.id', '=', 'cargo_stocks.cargo_id')
            ->get();
        return response()->json($cargoStocks);
    }

    public function controller()
    {
        return view('cargo.controller.orders');
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
        DB::beginTransaction();
        $data = $request->all();
        //dd($data);
        try {
            $cargoTask = CargoTask::findOrFail($data['cargoTaskId']);
            $cargoStock = CargoStock::findOrFail($data['cargoStockId']);

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

                $cargoData = $cargoStock->attributesToArray();
                $cargoData['user_id'] = Auth::user()->id;
                $cargoData['user_name'] = Auth::user()->full_name;
                $cargoData['cargo_name'] = Cargo::findOrFail($cargoStock->cargo_id)->name;
                $cargoData['address_from'] = 'cloud';
                $cargoData['address_to'] = CargoArea::findOrFail($data['cargoAreaId'])->name;
                $cargoData['action_type'] = 'received';

                if($cargoStock->car_number) {
                    // сборная
                    $cargoData['technique_ids'] = $data['techniqueIds'];
                    $cargoData['user_ids'] = $data['userIds'];
                    $cargoData['square'] = $data['square'];
                    $cargoData['count_operations'] = $data['count_operations'];
                } else {
                    // самоход
                    $cargoData['ramp'] = ($data['ramp'] == 'true') ? 'ok' : 'not';
                }

                if(isset($data['action_type']) && $data['action_type'] == 'edited') {
                    $cargoData['action_type'] = $data['action_type'];
                }

                CargoLog::create($cargoData);

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

                $cargoData = $cargoStock->attributesToArray();
                $cargoData['user_id'] = Auth::user()->id;
                $cargoData['user_name'] = Auth::user()->full_name;
                $cargoData['cargo_name'] = Cargo::findOrFail($cargoStock->cargo_id)->name;
                $cargoData['address_from'] = $cargoArea->name;
                $cargoData['address_to'] = $cargoArea->name;
                $cargoData['action_type'] = 'shipped';

                if($cargoStock->cargo_type == 1) {
                    // разобранный
                    $cargoData['technique_ids'] = $data['techniqueIds'];
                    $cargoData['user_ids'] = $data['userIds'];
                    $cargoData['count_operations'] = $data['count_operations'];
                } else {
                    // самоход
                    $cargoData['ramp'] = ($data['ramp'] == 'true') ? 'ok' : 'not';
                }

                CargoLog::create($cargoData);

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

    public function completeCargoTask(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $cargoTaskId = $request->input('cargoTaskId');
            $short_number = $request->input('short_number');
            $cargoTask = CargoTask::findOrFail($cargoTaskId);
            $cargoTask->short_number = $short_number;
            $cargoTask->status = 'completed';
            $cargoTask->save();

            // если заявка на выдачу, то при закрытие заявки мы из стока удаляем
            foreach($cargoTask->stocks as $cargoStock) {
                $cargoData = $cargoStock->attributesToArray();
                $cargoData['quantity'] = $cargoStock->quantity_reserved;

                if($cargoStock->quantity == $cargoStock->quantity_reserved) {
                    $cargoStock->delete();
                }

                if($cargoStock->quantity < $cargoStock->quantity_reserved) {
                    $cargoData['quantity'] = $cargoStock->quantity;
                    $cargoStock->delete();
                }

                if($cargoStock->quantity > $cargoStock->quantity_reserved) {
                    $cargoStock->quantity -= $cargoStock->quantity_reserved;
                    $cargoStock->status = 'received';
                    $cargoStock->quantity_reserved = null;
                    $cargoStock->save();
                }

                $cargoArea = CargoArea::findOrFail($cargoStock->cargo_area_id);

                $cargoData['user_id'] = Auth::user()->id;
                $cargoData['user_name'] = Auth::user()->full_name;
                $cargoData['cargo_name'] = Cargo::findOrFail($cargoStock->cargo_id)->name;
                $cargoData['address_from'] = $cargoArea->name;
                $cargoData['address_to'] = $cargoArea->name;
                $cargoData['action_type'] = 'completed';

                CargoLog::create($cargoData);
            }

            DB::commit();

            return response()->json('success');
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json($exception->getMessage(), 500);
        }
    }

    public function storeSpine(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->all();
        //dd($data);
        DB::beginTransaction();
        try {
            $data['user_name'] = Auth::user()->full_name;
            $company = Company::findOrFail($data['company_id']);
            $data['company'] = $company->short_en_name;
            $data['spine_number'] = Spine::generateUniqueNumber($data['type']);
            $data['kind'] = 'cargo';

            if($data['type'] == 'receive') {
                // ЗАВОЗ
                if($data['cargoTypeId'] == 1) {
                    // Завоз. Разобранная
                    $data['name'] = "Груз - " . $data['countPlaces'] . " шт";
                } else {
                    // Завоз. Самоход
                    $vinCodes = json_decode($data['vinCodes'], true);
                    $data['name'] = "Груз - " . count($vinCodes) . " шт";
                }
            } else {
                // ВЫВОЗ
                // на вывозе корешок создается автоматическое после того как все позиции выполниться со стороны кладовщика
            }

            $spine = Spine::create($data);

            if($data['type'] == 'receive' && $data['cargoTypeId'] == 2) {
                foreach($vinCodes as $item) {
                    SpineCode::create([
                        'spine_id' => $spine->id, 'vin_code' => $item['code'],
                    ]);
                }
            }

            DB::commit();

            return response()->json($spine);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json("ERROR: " . $exception->getMessage(), 500);
        }
    }

    public function spinePrintView($spine_id)
    {
        $spine = Spine::findOrFail($spine_id);
        return view('technique.spine', compact('spine'));
    }

    public function getSpines(): \Illuminate\Http\JsonResponse
    {
        $spines = Spine::where(['kind' => 'cargo'])
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json($spines);
    }

    public function getCars($companyId): \Illuminate\Http\JsonResponse
    {
        $spines = Spine::where(['company_id' => $companyId])
            ->selectRaw('id, car_number')
            ->whereNotNull('car_number')
            ->whereNull('task_number')
            ->get();
        return response()->json($spines);
    }

    public function getCodesForCar($companyId): \Illuminate\Http\JsonResponse
    {
        $spines = Spine::where(['spines.company_id' => $companyId])
            ->selectRaw('spine_codes.*')
            ->join('spine_codes', 'spine_codes.spine_id', '=', 'spines.id')
            ->whereNull('spine_codes.task_id')
            ->where(['spines.kind' => 'cargo'])
            ->get();
        return response()->json($spines);
    }
}
