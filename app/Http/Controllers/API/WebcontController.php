<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\ContainerAddress;
use App\Models\ContainerStock;
use App\Models\Technique;
use Illuminate\Http\Request;

class WebcontController extends BaseApiController
{
    public function getContainersZones()
    {
        $zones = ContainerAddress::select(['zone', 'title'])
            ->whereIn('kind', ['r', 'k', 'pole', 'cia', 'rich'])
            ->orderBy('title', 'ASC')
            ->groupBy('zone')->get();
        return response()->json($zones);
    }

    public function getTechniques()
    {
        return response()->json(Technique::orderBy('name', 'ASC')->get());
    }

    public function getContainerInfo(Request $request)
    {
        $container_number = $request->input('container_number');
        $container = Container::where('number', 'like', '%'.$container_number)->first();
        if ($container) {
            $container_stock = ContainerStock::where(['container_id' => $container->id])->first();
            if ($container_stock) {
                $container_address = $container_stock->container_address;
                $isCustoms = ($container_stock->customs == 'yes') ? 'Да' : 'Нет';
                if ($container_address->name == 'damu_in' && $container_stock->status == 'incoming') {
                    return response([
                        'text1' => "Контейнер: ",
                        'text2' => $container->number ."(" .$container->company, $container_stock->state, $container->container_type, $isCustoms.")",
                        'text3' => "необходимо",
                        'text4' => "РАЗМЕСТИТЬ.",
                        'event' => 1,
                        'container_id' => $container->id,
                        'container_number' => $container->number,
                        'current_container_address' => $container_address->name,
                        'isCustoms' => $container_stock->customs
                    ], 200);
                }
                if ($container_address->name == 'damu_in' && $container_stock->status == 'cancel') {
                    return response("Контейнер №".$container->number." найдено. Контейнер находится в процессе отмены", 403);
                }
                if($container_address->name == 'damu_out' && $container_stock->status == 'shipped') {
                    return response("Контейнер №".$container->number." найдено. Контейнер находится в зоне выдачи", 403);
                }
                if ($container_address->name != 'damu_in' && $container_stock->status == 'cancel') {
                    return response("Контейнер №".$container->number." найдено. Контейнер находится в процессе отмены", 403);
                }
                if ($container_address->name == 'damu_in' && $container_stock->status == 'edit') {
                    return response("Контейнер №".$container->number." найдено. Контейнер находиться в процессе редактирование", 403);
                }
                if($container_stock->status == 'received') {
                    return response([
                        'text1' => "Контейнер: ",
                        'text2' => $container->number." ($container->company, $container_stock->state, $container->container_type, $isCustoms)",
                        'text3' => "Адрес: $container_address->name",
                        'text4' => '.',
                        'event' => 3,
                        'container_id' => $container->id,
                        'container_number' => $container->number,
                        'current_container_address' => $container_address->name,
                        'isCustoms' => $container_stock->customs
                    ], 200);
                }
                if($container_stock->status == 'in_order') {
                    return response([
                        'text1' => "Контейнер: ",
                        'text2' => $container->number."($container->company, $container_stock->state, $container->container_type, $isCustoms)",
                        'text3' => "Адрес: $container_address->name",
                        'text4' => '.',
                        'event' => 2,
                        'container_id' => $container->id,
                        'container_number' => $container->number,
                        'current_container_address' => $container_address->name,
                        'isCustoms' => $container_stock->customs
                    ], 200);
                }
            } else {
                return response("Контейнер №".$container_number." отсутствует в остатке", 404);
            }
        } else {
            return response("Контейнер №".$container_number." не найден", 404);
        }
    }

    public function getFreeRows(Request $request)
    {
        $zone = $request->input('zone');
        $container_id = $request->input('container_id');

        $container = Container::findOrFail($container_id);
        $container_address = ContainerAddress::whereZone($zone)
            ->leftJoin('container_stocks', 'container_stocks.container_address_id', '=', 'container_address.id')
            ->leftJoin('containers', 'containers.id', '=', 'container_stocks.container_id')
            ->get();

        if ($zone == 'SPR' || $zone == 'SPK') {
            $arr = [];
            foreach($container_address as $address) {
                if (isset($_SESSION['not_place']) && $_SESSION['not_place'] == $address->row.$address->place) {
                    continue;
                }

                if (is_null($address->container_id)) {
                    if (!in_array($address->row, $arr)) {
                        $arr[$address->row][$address->place] = $address->place;
                    }
                }

                if ($container->container_type == '40' || $container->container_type == '45') {
                    if ($address->container_type == '20') {
                        $_SESSION['not_place'] = $address->row.$address->place;
                    }
                }

                if ($container->container_type == '20') {
                    if ($address->container_type == '40' || $container->container_type == '45') {
                        $_SESSION['not_place'] = $address->row.$address->place;
                    }
                }
            }

            if ($zone == 'POLE') {
                return [0 => 1];
            }

            return array_keys($arr);
        } else {
            $container_address = ContainerAddress::whereZone($zone)
                ->select('row')
                ->leftJoin('container_stocks', 'container_stocks.container_address_id', '=', 'container_address.id')
                ->leftJoin('containers', 'containers.id', '=', 'container_stocks.container_id')
                ->get();
            return $container_address;
        }
    }
}
