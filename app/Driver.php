<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    public $fillable = [
        'fio', 'phone', 'ud_number', 'want_to_order'
    ];

    public static function exists($ud_number)
    {
        $ud_number = trim(mb_strtoupper($ud_number));
        $record = Driver::where(['ud_number' => $ud_number])->first();
        return ($record) ? true : false;
    }
}
