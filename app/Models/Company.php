<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';

    protected $fillable = [
        'full_company_name', 'short_ru_name', 'short_en_name', 'address', 'kind_of_activity',
        'type_company', 'created_at', 'updated_at'
    ];

    public function getTypeCompany()
    {
        if($this->type_company == 'undefined') return 'Не определено';
        if($this->type_company == 'outsourcing') return 'Аутсорсинг';
        if($this->type_company == 'rent') return 'Аренда';
        if($this->type_company == 'resident') return 'Резидент';
    }
}
