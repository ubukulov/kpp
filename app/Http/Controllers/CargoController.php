<?php

namespace App\Http\Controllers;

use App\Models\CargoArea;
use App\Models\CargoItem;
use App\Models\CargoLog;
use App\Models\CargoStock;
use App\Models\CargoTonnage;
use App\Models\CargoWork;
use App\Models\Company;
use App\Models\Cargo;
use App\Models\Technique;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Auth;

class CargoController extends BaseController
{
    public function getCargos(): \Illuminate\Http\JsonResponse
    {
        $cargo  = Cargo::orderBy('id', 'DESC')
            ->selectRaw('cargo.*, companies.short_en_name')
            ->join('companies', 'companies.id', '=', 'cargo.company_id')
            ->get();
        return response()->json($cargo);
    }

    public function getCargoCompanies(): \Illuminate\Http\JsonResponse
    {
        return response()->json(Company::where('type_company', 'cargo')->get());
    }

    public function getCargoNames(): \Illuminate\Http\JsonResponse
    {
        return response()->json(CargoTonnage::orderBy('name', 'ASC')->get());
    }

    public function create()
    {
        $companies = Company::where(['type_company' => 'cargo'])->get();
        $tonnages = CargoTonnage::all();
        return view('cargo.create', compact('companies', 'tonnages'));
    }

    public function show($id)
    {
        return view('cargo.show', compact('id'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        DB::beginTransaction();
        try {

            $cargo = Cargo::create([
                'user_id' => Auth::id(), 'company_id' => $data['company_id'], 'type' => $data['type'], 'tonnage' => $data['tonnage'],
                'mode' => $data['mode']
            ]);



            foreach($data['cargo_items']['vincode'] as $key=>$vin) {
                $cargoItem = CargoItem::create([
                    'cargo_id' => $cargo->id, 'vincode' => $vin, 'status' => 'waiting',
                    'cargo_tonnage_type_id' => $data['cargo_items']['type'][$key],
                    'car_number' => $data['cargo_items']['car'][$key]
                ]);

                CargoStock::create([
                    'cargo_id' => $cargo->id, 'cargo_item_id' => $cargoItem->id, 'status' => 'incoming'
                ]);

                CargoLog::create([
                    'user_id' => Auth::id(), 'cargo_id' => $cargo->id, 'cargo_item_id' => $cargoItem->id, 'action_type' => 'incoming'
                ]);
            }

            DB::commit();

            return redirect()->route('cargo.index');
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            DB::rollBack();
        }
    }

    public function cargoItems($id)
    {
        $cargo = Cargo::findOrFail($id);
        $cargo_tonnage = CargoTonnage::all();
        return view('cargo.controller.cargo_items', compact('cargo', 'cargo_tonnage'));
    }

    public function cargoItemStepTwo($id)
    {
        $cargo_item = CargoItem::findOrFail($id);
        $techniques = Technique::all();
        $employees = User::where(['company_id' => 31])->get();
        $cargo_areas = CargoArea::all();
        return view('cargo.controller.cargo_item_two', compact('cargo_item', 'techniques', 'employees', 'cargo_areas'));
    }

    public function cargoItemStepTwoStore(Request $request, $cargoItemId)
    {
        $data = $request->all();
        DB::beginTransaction();
        try {
            $data['technique_ids'] = implode(',', $data['techniques']);
            $data['employee_ids'] = implode(',', $data['employees']);
            $data['status'] = 'processing';
            $cargoItem = CargoItem::findOrFail($cargoItemId);
            $cargoItem->update($data);

            $cargoStock = CargoStock::where(['cargo_item_id' => $cargoItemId, 'cargo_id' => $cargoItem->cargo_id, 'status' => 'incoming'])->first();
            if($cargoStock) {
                $cargoStock->status = 'received';
                $cargoStock->save();
            }

            CargoLog::create([
                'user_id' => Auth::id(), 'cargo_id' => $cargoItem->cargo_id, 'cargo_item_id' => $cargoItemId, 'action_type' => 'received'
            ]);

            DB::commit();

            $cargo = Cargo::findOrFail($cargoItem->cargo_id);
            if($cargo) {
                $cargo->status = 'processing';
                $cargo->save();
            }

            return redirect()->route('cargo.controller.cargoItemStepThree', ['cargoItem' => $cargoItemId]);
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            DB::rollBack();
        }
    }

    public function cargoItemStepThree($cargoItemId)
    {
        $cargo_item = CargoItem::findOrFail($cargoItemId);
        $cargo_work_types = CargoWork::all();
        return view('cargo.controller.cargo_item_three', compact('cargo_item', 'cargo_work_types'));
    }

    public function cargoItemStepThreeStore(Request $request, $cargoItemId)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $cargoItem = CargoItem::findOrFail($cargoItemId);
            $data['cargo_work_type_ids'] = implode(',', $data['cargo_work_types']);
            $data['status'] = 'completed';
            $cargoItem->update($data);

            CargoLog::create([
                'user_id' => Auth::id(), 'cargo_id' => $cargoItem->cargo_id, 'cargo_item_id' => $cargoItemId, 'action_type' => 'received'
            ]);

            DB::commit();

            $cargo = Cargo::findOrFail($cargoItem->cargo_id);
            if($cargo->canClose()) {
                $cargo->status = 'completed';
                $cargo->save();
            }

            return redirect()->route('cargo.controller.cargoItems', ['id' => $cargoItem->cargo->id]);
        } catch (\Exception $exception) {
            echo $exception->getMessage();
            DB::rollBack();
        }
    }

    public function startPage($id, $pageId)
    {
        switch ($pageId) {
            case 2:
                return view('cargo.controller.start.2');
            break;

            case 3:
                return view('cargo.controller.start.3');
                break;

            default:
                return view('cargo.controller.start.2');

        }
    }

    public function controller()
    {
        $cargo = Cargo::orderBy('id', 'DESC')->get();
        return view('cargo.controller.index', compact('cargo'));
    }

    public function controllerShow($id)
    {
        $cargo = Cargo::findOrFail($id);
        return view('cargo.controller.show', compact('cargo'));
    }

    public function qr($id)
    {
        $cargo = Cargo::findOrFail($id);
        return view('cargo.qr', compact('cargo'));
    }
}
