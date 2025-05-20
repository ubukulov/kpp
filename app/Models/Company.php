<?php

namespace App\Models;

use App\Traits\HasKppOnCompany;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasKppOnCompany;

    protected $table = 'companies';

    /**
     * Описание к полям таблицы
     * full_company_name - полное наименование компании
     * short_ru_name - короткое название компании на русском языке
     * short_en_name - короткое название компании на английском языке
     * address - Адрес на территории ИЛЦ ДАМУ
     * kind_of_activity - Вид деятельности
     * type_company - Тип клиента
     * ashana - принимает два значение: 0 - разрешен питаться в столовое, 1 - запрещен
     * status - если ok то работает
     * ckud_group_id - uuid CKUD ИД группы
     */

    protected $fillable = [
        'full_company_name', 'short_ru_name', 'short_en_name', 'address', 'kind_of_activity',
        'type_company', 'ashana', 'bin', 'status', 'ckud_group_id', 'created_at', 'updated_at'
    ];

    public function getTypeCompany()
    {
        if($this->type_company == 'undefined') return 'Не определено';
        if($this->type_company == 'outsourcing') return 'Аутсорсинг';
        if($this->type_company == 'rent') return 'Аренда';
        if($this->type_company == 'resident') return 'Резидент';
        if($this->type_company == 'technique') return 'Авто-техника';
        if($this->type_company == 'cargo') return 'Грузы';
        if($this->type_company == 'damu_group') return 'Damu Group';
    }

    public function wcl_cars()
    {
        return $this->belongsToMany(WclCompany::class, 'wcl_companies');
    }
}
