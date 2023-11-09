<?php


namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TechniqueLog;
use App\Models\TechniquePlace;
use App\Models\TechniqueStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use File;

class TechniqueController extends BaseApiController
{
    public function getTechniquePlaces()
    {
        return response()->json(TechniquePlace::all());
    }

    public function receiveTechniqueToPlace(Request $request)
    {
        $technique_place_id = $request->input('technique_place_id');
        $vin_code = $request->input('vin_code');
        $image64 = $request->input('image64');
        $user = $request->user();
        DB::beginTransaction();
        try {
            $technique_stock = TechniqueStock::where(['vin_code' => $vin_code])->first();
            if($technique_stock) {
                $technique_stock->status = 'received';
                $technique_stock->technique_place_id = $technique_place_id;
                $technique_stock->save();

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

                    $technique_stock->image = $dir.'/'.$imageName;
                    $technique_stock->save();
                }

                $data = $technique_stock->attributesToArray();
                $data['user_id'] = $user->id;
                $data['technique_type'] = $technique_stock->technique_type->name;
                $data['operation_type'] = 'received';
                $data['address_from'] = 'cloud';
                $data['address_to'] = $technique_stock->technique_place->name;

                TechniqueLog::create($data);

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

    public function getInformationByQRCode(Request $request)
    {
        $vin_code = $request->input('vin_code');
        $technique_stock = TechniqueStock::where(['vin_code' => $vin_code])->first();
        if($technique_stock) {
            if($technique_stock->status == 'incoming') {
                return response([
                    'message' => "Техника: <span style='color: red;'>".$vin_code."<br> </span> необходимо <span style='color: green;'>РАЗМЕСТИТЬ.</span>",
                    'event' => 1,
                ]);
            }

            if($technique_stock->status == 'received') {
                return response([
                    'message' => "Техника: <span style='color: red;'>".$vin_code."</span>.<br> Адрес: " . $technique_stock->technique_place->name,
                    'event' => 2,
                    'technique_stock' => $technique_stock
                ]);
            }

            if($technique_stock->status == 'in_order') {
                return response([
                    'message' => "Техника: <span style='color: red;'>".$vin_code."</span>.<br> Адрес: " . $technique_stock->technique_place->name,
                    'event' => 3,
                    'technique_stock' => $technique_stock
                ]);
            }
        }

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

                TechniqueLog::create([
                    'user_id' => $user->id, 'technique_task_id' => $technique_stock->technique_task_id, 'owner' => $technique_stock->owner,
                    'technique_type' => $technique_stock->technique_type->name, 'mark' => $technique_stock->mark, 'vin_code' => $technique_stock->vin_code,
                    'operation_type' => 'moved', 'address_from' => $technique_place->name, 'address_to' => $technique_stock->technique_place->name
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
        DB::beginTransaction();
        try {
            $technique_stock = TechniqueStock::where(['vin_code' => $vin_code])->first();
            if($technique_stock) {
                $technique_task = $technique_stock->technique_task;
                $technique_stock->status = 'shipped';
                $technique_stock->save();

                $technique_place = $technique_stock->technique_place;

                TechniqueLog::create([
                    'user_id' => $user->id, 'technique_task_id' => $technique_stock->technique_task_id, 'owner' => $technique_stock->owner,
                    'technique_type' => $technique_stock->technique_type->name, 'mark' => $technique_stock->mark, 'vin_code' => $technique_stock->vin_code,
                    'operation_type' => 'shipped', 'address_from' => $technique_place->name, 'address_to' => $technique_place->name
                ]);

                if($technique_task->canClose()) {
                    TechniqueLog::create([
                        'user_id' => $user->id, 'technique_task_id' => $technique_stock->technique_task_id, 'owner' => $technique_stock->owner,
                        'technique_type' => $technique_stock->technique_type->name, 'mark' => $technique_stock->mark, 'vin_code' => $technique_stock->vin_code,
                        'operation_type' => 'completed', 'address_from' => $technique_place->name, 'address_to' => 'completed'
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
