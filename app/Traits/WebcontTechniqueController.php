<?php

namespace App\Traits;

use App\Models\TechniqueStock;
use GuzzleHttp\Psr7\Request;

trait WebcontTechniqueController
{
    public function techniqueController()
    {
        return view('technique.technique_controller');
    }

    public function getInformationByQRCode(Request $request)
    {
        $vin_code = $request->input('vin_code');
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

        return response([
            'message' => "Техника с " . $vin_code . " не найден."
        ], 403);
    }
}
