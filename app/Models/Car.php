<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $table = 'cars';

    protected $fillable = [
        'tex_number', 'mark_car', 'gov_number', 'body_type_id', 'cat_tc_id',
        'pr_number', 'lc_id', 'bt_id', 'from_company', 'created_at', 'updated_at'
    ];

    public static function exists($tex_number)
    {
        $tex_number = trim(strtoupper($tex_number));
        $record = Car::where(['tex_number' => $tex_number])->first();
        return ($record) ? true : false;
    }
}
