<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Str;

class ContainerAddress extends Model
{
    protected $table = 'container_address';

    protected $fillable = [
        'id', 'title', 'zone', 'kind', 'row', 'place', 'floor', 'name', 'space'
    ];

    /*
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model){
            $model->name = substr(Str::slug('Спредер'), 0, 2)."-".$this->row."-".$this->place."-".$this->floor;;
        });
    }*/

    public static function getFreeRows($zone)
    {
        $container_address_ids = ContainerStock::select('container_address_id')->get();
        return ContainerAddress::whereZone($zone)
                ->select('row')
                ->whereNotIn('id', $container_address_ids)
                ->groupBy('row')
                ->get();
    }

    public static function getFreePlaces($zone, $row, $container_id)
    {
        $container = Container::findOrFail($container_id);
        $container_address = ContainerAddress::whereZone($zone)
            ->leftJoin('container_stocks', 'container_stocks.container_address_id', '=', 'container_address.id')
            ->leftJoin('containers', 'containers.id', '=', 'container_stocks.container_id')
            ->whereRow($row)
            ->get();

        if ($container->container_type == '40') {
            $not_places = [];
            $prev_place = 1;
            $arr = [];
            $i = 1;

            foreach ($container_address as $key=>$address) {
                if ($prev_place != $address->place) {
                    $i = 1;
                }

                if ($address->container_type == '20') {
                    $not_places[] = $address->place;
                }

                if ($address->container_type == '40') {
                    if ($address->place == $prev_place) {
                        $arr[$address->place] = $i;
                        $i++;
                        $prev_place = $address->place;
                    } else {
                        $arr[$address->place] = $i;
                        $i++;
                        $prev_place = $address->place;
                    }
                }
            }

            foreach($arr as $k=>$v) {
                if ($v == 4) {
                    $not_places[] = $k;
                }
            }

            return  $container_address->filter(function($value, $key) use ($not_places){
                return (!in_array($value->place, $not_places));
            })->groupBy('place')->keys();
        } else {
            $not_places = [];
            foreach ($container_address as $key=>$address) {
                if ($address->container_type == '40') {
                    $not_places[] = $address->place;
                }
            }

            return $container_address->filter(function($value, $key) use ($not_places){
                return (!in_array($value->place, $not_places));
            })->groupBy('place')->keys();
        }
    }

    public static function getFreeFloors($zone, $row, $place, $container_id)
    {
        $container = Container::findOrFail($container_id);
        $container_address = ContainerAddress::whereZone($zone)
            ->leftJoin('container_stocks', 'container_stocks.container_address_id', '=', 'container_address.id')
            ->leftJoin('containers', 'containers.id', '=', 'container_stocks.container_id')
            ->whereRow($row)
            ->wherePlace($place)
            ->get();

        if ($container->container_type == '40') {
            $arr = [];
            foreach ($container_address as $key=>$address) {
                if ($address->container_type == null) {
                    $arr[$address->floor] = 0;
                } else {
                    $arr[$address->floor] = 1;
                }
            }

            $arr1 = [];
            foreach ($arr as $k=>$v) {
                if ($v == 0) {
                    $arr1[] = $k;
                    break;
                }
            }

            return $arr1;
        } else {
            $arr = [];
            $i = 1;
            $prev_floor = 1;
            foreach ($container_address as $key=>$address) {
                if ($address->container_type == null) {
                    $arr[$address->floor] = 0;
                } else {
                    if ($prev_floor != $address->floor) {
                        $i = 1;
                    }

                    if ($prev_floor == $address->floor) {
                        $arr[$address->floor] = $i;
                        $i++;
                        $prev_floor = $address->floor;
                    } else {
                        $arr[$address->floor] = $i;
                        $i++;
                        $prev_floor = $address->floor;
                    }
                }
            }

            $arr1 = [];
            foreach ($arr as $k=>$v) {
                if ($v == 1) {
                    $arr1[] = $k;
                }
                if ($v == 0) {
                    $arr1[] = $k;
                    break;
                }
            }

            return $arr1;
        }
    }
}
