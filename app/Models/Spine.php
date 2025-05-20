<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spine extends Model
{
    use HasFactory;

    protected $fillable = [
        'spine_number', 'company_id', 'company', 'task_number', 'type', 'name', 'container_number', 'car_number',
        'driver_name', 'user_name', 'kind'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function codes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SpineCode::class);
    }

    public static function generateUniqueNumber($type)
    {
        $prefix = ($type == 'receive') ? 'Z' : 'V';
        $lastItem = Spine::orderBy('id', 'DESC')->first();
        if($lastItem) {
            $number = (int) substr($lastItem->spine_number,1);
            $number++;
            switch (strlen($number)) {
                case 1:
                    $number = "0000000" . $number;
                    break;
                case 2:
                    $number = "000000" . $number;
                    break;
                case 3:
                    $number = "00000" . $number;
                    break;
                case 4:
                    $number = "0000" . $number;
                    break;
                case 5:
                    $number = "000" . $number;
                    break;
                case 6:
                    $number = "00" . $number;
                    break;
                case 7:
                    $number = "0" . $number;
                    break;
            }

            return $prefix . $number;
        } else {
            return $prefix . "00000001";
        }
    }
}
