<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\SpineCode;
use App\Models\TechniqueLog;
use App\Models\TechniquePlace;
use App\Models\TechniqueStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use File;
use Auth;

class TechniqueController extends BaseApiController
{
    public function getTechniquePlaces()
    {
        return response()->json(TechniquePlace::all());
    }

    // АПИ для размещение авто
    public function receiveTechniqueToPlace(Request $request)
    {
        $technique_place_id = $request->input('technique_place_id');
        $vin_code = $request->input('vin_code');
        $image64 = $request->input('image64');
        $defect = $request->input('defect');
        $user = $request->user();
        DB::beginTransaction();
        try {
            $technique_stock = TechniqueStock::where(['vin_code' => $vin_code])->first();
            if($technique_stock) {
                $technique_stock->status = 'received';
                $technique_stock->technique_place_id = $technique_place_id;
                $technique_stock->save();

                $technique_task = $technique_stock->technique_task;

                if($image64) {
                    $dir = '/technique_files/'. substr(md5(microtime()), mt_rand(0, 30), 2);
                    if(!File::isDirectory(public_path(). $dir)){
                        File::makeDirectory(public_path(). $dir, 0777, true);
                    }

                    preg_match("/data:image\/(.*?);/",$image64,$image_extension); // extract the image extension
                    $image = preg_replace('/data:image\/(.*?);base64,/','',$image64); // remove the type part
                    $image = str_replace(' ', '+', $image);
                    $imageName = $technique_stock->id.'_f_'.time() . '.' . '.jpeg'; //generating unique file name;
                    File::put(public_path(). $dir.'/'.$imageName,base64_decode($image));

                    $technique_stock->defect_image = $dir.'/'.$imageName;
                    $technique_stock->save();
                }

                if($defect) {
                    $defect_note = $request->input('defect_note');
                    $technique_stock->defect = 'yes';
                    $technique_stock->defect_note = $defect_note;
                    $technique_stock->save();
                }

                $data = $technique_stock->attributesToArray();
                $company = Company::findOrFail($data['company_id']);
                $data['user_id'] = $user->id;
                $data['technique_type'] = $technique_stock->technique_type->name;
                $data['operation_type'] = 'received';
                $data['address_from'] = 'cloud';
                $data['address_to'] = $technique_stock->technique_place->name;
                $data['owner'] = $company->full_company_name;
                $data['company_id'] = $company->id;

                TechniqueLog::create($data);

                if($technique_task->completeTask()) {
                    $technique_task->status = 'closed';
                    $technique_task->save();
                }

                DB::commit();

                return response([
                    'message' => "Техника: <span style='color: red;'>".$vin_code."</span>.<br> Успешно размещено!",
                ]);
            }

            return response('Technique with ' . $vin_code . ' not found.', 403);

        } catch (\Exception $exception) {
            DB::rollBack();
            return response("Error: " . $exception->getMessage(), 500);
        }
    }

    // АПИ для проверки статуса авто
    public function getInformationByQRCode(Request $request)
    {
        $vin_code = $request->input('vin_code');
        $user = $request->user();
//        $technique_stock = TechniqueStock::where(['vin_code' => $vin_code])->first();
        $technique_stock = TechniqueStock::where('vin_code', 'like', '%'.$vin_code.'%')->first();
        if($technique_stock) {
            $vin_code = $technique_stock->vin_code;
            $color = $technique_stock->color;
            $mark = $technique_stock->mark;
            if($technique_stock->status == 'incoming') {
                /*if(SpineCode::exists($technique_stock->technique_task_id, $vin_code)) {
                    return response([
                        'message' => "Техника: <span style='color: red;'>".$vin_code."<br> </span> необходимо <span style='color: green;'>РАЗМЕСТИТЬ.</span>",
                        'event' => 1,
                        'vin_code' => $technique_stock->vin_code
                    ]);
                } else {
                    return response([
                        'message' => "Техника: <span style='color: red;'>".$vin_code.".<br> </span> <p style='color: black; font-size: 18px; margin-top: 20px;'>ДЛЯ РАЗМЕЩЕНИЕ НУЖНО ПОЛУЧИТЬ КОРЕШОК</p>",
                    ], 404);
                }*/
                return response([
                    'message' => "Техника: <span style='color: red;'>".$vin_code."<br> </span> необходимо <span style='color: green;'>РАЗМЕСТИТЬ.</span><br></br>ЦВЕТ: <span style='color: red;'>$color</span><br>МАРКА: <span style='color: red; font-size: 12px;'>$mark</span>",
                    'event' => 1,
                    'vin_code' => $technique_stock->vin_code
                ]);
            }

            if($technique_stock->status == 'received') {
                return response([
                    'message' => "Техника: <span style='color: red;'>".$vin_code."</span>.<br> Адрес: " . $technique_stock->technique_place->name . "<br>ЦВЕТ: <span style='color: red;'>$color</span><br>МАРКА: <span style='color: red; font-size: 12px;'>$mark</span>",
                    'event' => 2,
                    'technique_stock' => $technique_stock,
                    'vin_code' => $technique_stock->vin_code
                ]);
            }

            if($technique_stock->status == 'in_order') {
                return response([
                    'message' => "Техника: <span style='color: red;'>".$vin_code."</span>.<br> Адрес: " . $technique_stock->technique_place->name . "<br>ЦВЕТ: <span style='color: red;'>$color</span><br>МАРКА: <span style='color: red; font-size: 14px;'>$mark</span>",
                    'event' => 3,
                    'technique_stock' => $technique_stock,
                    'vin_code' => $technique_stock->vin_code
                ]);
            }
        }

        /*DB::beginTransaction();
        try {
            $technique_stock = TechniqueStock::create([
                'vin_code' => $vin_code, 'technique_type_id' => 1, 'status' => 'incoming'
            ]);

            $data = $technique_stock->attributesToArray();
            $data['user_id'] = $user->id;
            $data['technique_type'] = $technique_stock->technique_type->name;
            $data['operation_type'] = 'received';
            $data['address_from'] = 'инвент';
            $data['address_to'] = 'incoming';

            TechniqueLog::create($data);

            DB::commit();
            $date = date("d.m.Y H:i:s");
            return response([
                'message' => "<span style='color: red;'>".$vin_code . "</span> было не найдено. НО принять по инвенту($date) в сток. Необходимо <span style='color: green;'>РАЗМЕСТИТЬ.</span>",
                'event' => 1,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response([
                'message' => "Технику с " . $vin_code . " не получилось принять в остатку. Причина: ".$e->getMessage()
            ], 403);
        }*/

        return response([
            'message' => "Техника с " . $vin_code . " не найден."
        ], 403);
    }

    public function moveTechniqueToOtherPlace(Request $request)
    {
        //$technique_place_now_id = $request->input('technique_place_current_id');
        $technique_place_id = $request->input('technique_place_id');
        $vin_code = $request->input('vin_code');
        $user = $request->user();
        DB::beginTransaction();
        try {
            $technique_stock = TechniqueStock::where(['vin_code' => $vin_code])->first();
            if($technique_stock) {
                $technique_place = TechniquePlace::findOrFail($technique_stock->technique_place_id);

                $technique_stock->technique_place_id = $technique_place_id;
                $technique_stock->save();

                $company = Company::findOrFail($technique_stock->company_id);

                TechniqueLog::create([
                    'user_id' => $user->id, 'technique_task_id' => $technique_stock->technique_task_id, 'owner' => $company->full_company_name,
                    'technique_type' => $technique_stock->technique_type->name, 'mark' => $technique_stock->mark, 'vin_code' => $technique_stock->vin_code,
                    'operation_type' => 'moved', 'address_from' => $technique_place->name, 'address_to' => $technique_stock->technique_place->name,
                    'company_id' => $company->id
                ]);

                DB::commit();

                return response('success');
            }

            return response('Technique with ' . $vin_code . ' not found.', 403);

        } catch (\Exception $exception) {
            DB::rollBack();
            return response("Error: " . $exception->getMessage(), 500);
        }
    }

    public function shippingTechnique(Request $request)
    {
        $vin_code = $request->input('vin_code');
        $user = $request->user();
        $user = Auth::user();
        DB::beginTransaction();
        try {
            $technique_stock = TechniqueStock::where(['vin_code' => $vin_code])->first();
            if($technique_stock) {
                $technique_stock->status = 'shipped';
                $technique_stock->save();

                $technique_place = $technique_stock->technique_place;

                $company = Company::findOrFail($technique_stock->company_id);

                TechniqueLog::create([
                    'user_id' => $user->id, 'technique_task_id' => $technique_stock->technique_task_id, 'owner' => $company->full_company_name,
                    'technique_type' => $technique_stock->technique_type->name, 'mark' => $technique_stock->mark, 'vin_code' => $technique_stock->vin_code,
                    'operation_type' => 'shipped', 'address_from' => $technique_place->name, 'address_to' => $technique_place->name,
                    'company_id' => $company->id
                ]);

                DB::commit();

                /*$technique_task = $technique_stock->technique_task;

                if($technique_task->canClose()) {

                    $technique_stocks = $technique_task->stocks;
                    foreach($technique_stocks as $stock) {
                        $tech_place = $stock->technique_place;
                        $company = Company::findOrFail($technique_stock->company_id);
                        TechniqueLog::create([
                            'user_id' => $user->id, 'technique_task_id' => $technique_task->id, 'owner' => $company->full_company_name,
                            'technique_type' => $stock->technique_type->name, 'mark' => $stock->mark, 'vin_code' => $stock->vin_code,
                            'operation_type' => 'completed', 'address_from' => $tech_place->name, 'address_to' => 'completed'
                        ]);

                        $stock->delete();
                    }

                    $technique_task->status = 'closed';
                    $technique_task->save();
                }*/
            }

            return response([
                'message' => "Техника: <span style='color: red;'>".$vin_code."</span>.<br> Успешно выдан!",
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response([
                'message' => "Error: ".$exception->getMessage()
            ], 500);
        }
    }

    public function forceAcceptToStock(Request $request)
    {
        $vin_code = $request->input('vin_code');
        $user = $request->user();
        DB::beginTransaction();
        try {
            $technique_stock = TechniqueStock::where(['vin_code' => $vin_code])->first();
            if($technique_stock) {
                $technique_task = $technique_stock->technique_task;
                $technique_stock->status = 'shipped';
                $technique_stock->save();

                $technique_place = $technique_stock->technique_place;
                $company = Company::findOrFail($technique_stock->company_id);
                TechniqueLog::create([
                    'user_id' => $user->id, 'technique_task_id' => $technique_stock->technique_task_id, 'owner' => $company->full_company_name,
                    'technique_type' => $technique_stock->technique_type->name, 'mark' => $technique_stock->mark, 'vin_code' => $technique_stock->vin_code,
                    'operation_type' => 'shipped', 'address_from' => $technique_place->name, 'address_to' => $technique_place->name,
                    'company_id' => $company->id
                ]);

                if($technique_task->canClose()) {
                    TechniqueLog::create([
                        'user_id' => $user->id, 'technique_task_id' => $technique_stock->technique_task_id, 'owner' => $company->full_company_name,
                        'technique_type' => $technique_stock->technique_type->name, 'mark' => $technique_stock->mark, 'vin_code' => $technique_stock->vin_code,
                        'operation_type' => 'completed', 'address_from' => $technique_place->name, 'address_to' => 'completed',
                        'company_id' => $company->id
                    ]);

                    $technique_stock->delete();

                    $technique_task->status = 'closed';
                    $technique_task->save();
                }
            }
            DB::commit();
            return response([
                'message' => "Техника: <span style='color: red;'>".$vin_code."</span>.<br> Успешно выдан!",
            ]);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response([
                'message' => "Error: ".$exception->getMessage()
            ], 500);
        }
    }
}
