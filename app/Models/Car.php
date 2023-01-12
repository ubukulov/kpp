<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $table = 'cars';

    /**
     * Описание к полям
     * tex_number - номер тех.паспорта
     * mark_car - марка машины
     * gov_number - номер машины
     * body_type_id -
     * cat_tc_id -
     * pr_number - номер приципа
     * lc_id -
     * bt_id - Тип кузова, связан с таблицей body_type
     * from_company - Собственник по техпаспорту/частник
     * foreign_car : 0 - Не указано, 1 - Казахстанская, 2 - Иностранная
     * ours - значение, yes - машина собственность Даму, not - не собственность Даму
     */
    protected $fillable = [
        'tex_number', 'mark_car', 'gov_number', 'body_type_id', 'cat_tc_id',
        'pr_number', 'lc_id', 'bt_id', 'from_company', 'created_at', 'updated_at',
        'foreign_car', 'ours'
    ];

    public static function exists($tex_number)
    {
        $tex_number = trim(strtoupper($tex_number));
        $record = Car::where(['tex_number' => $tex_number])->first();
        return ($record) ? true : false;
    }
}
