<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sms;

class Driver extends Model
{
    public $fillable = [
        'fio', 'phone', 'ud_number', 'want_to_order', 'last_time', 'created_at', 'updated_at'
    ];

    public static function exists($ud_number)
    {
        $ud_number = trim(strtoupper($ud_number));
        $record = Driver::where(['ud_number' => $ud_number])->first();
        return ($record) ? true : false;
    }

    public static function send_sms($phone)
    {
        if (strlen($phone) == 11) {
            $str = "На территории ИЛЦ \"DAMU\", фото-видео съемка запрещена.
К нарушителям будут применятся меры предусмотренные Законом РК.";

            $driver = Driver::wherePhone($phone)->first();
            if ($driver) {
                $balance = Sms::get_balance();
                $now = Carbon::now();
                if (empty($driver->last_time)) {
                    if ((int) $balance > 50) {
                        Sms::send_sms($phone, $str);
                        $driver->last_time = $now;
                        $driver->save();
                    }
                } else {
                    $last_time = new Carbon($driver->last_time);
                    if ($last_time->diff($now)->days > 30) {
                        $phone = "7".substr($phone,1);
                        if ((int) $balance > 50) {
                            Sms::send_sms($phone, $str);
                            $driver->last_time = $now;
                            $driver->save();
                        }
                    }
                }
            }
        }
    }
}
