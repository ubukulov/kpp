<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
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
     */

    protected $fillable = [
        'full_company_name', 'short_ru_name', 'short_en_name', 'address', 'kind_of_activity',
        'type_company', 'ashana', 'created_at', 'updated_at'
    ];

    public function getTypeCompany()
    {
        if($this->type_company == 'undefined') return 'Не определено';
        if($this->type_company == 'outsourcing') return 'Аутсорсинг';
        if($this->type_company == 'rent') return 'Аренда';
        if($this->type_company == 'resident') return 'Резидент';
    }

    public function wcl_cars()
    {
        return $this->belongsToMany(WclCompany::class, 'wcl_companies');
    }
}
