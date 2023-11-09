<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhiteCarList extends Model
{
    protected $table = 'white_car_lists';

    protected $fillable = [
        'gov_number', 'kpp_name', 'position', 'full_name', 'mark_car',
        'pass_type', 'contractor_name'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function getLogs()
    {
        return $this->hasMany(WhiteCarLog::class, 'wcl_id');
    }

    public function scopeKpp7($query)
    {
        return $query->where('kpp_name', 'kpp7');
    }

    public function getCompanyNames()
    {
        $wcl_companies = WclCompany::where(['wcl_id' => $this->id])
            ->get();
        $companyNames = "";
        foreach($wcl_companies as $wcl_company){
            $companyNames .= $wcl_company->company->short_ru_name . ", ";
        }
        return substr($companyNames,0, -2);
    }

    public static function exists($gov_number)
    {
        $wcl = WhiteCarList::where(['gov_number' => $gov_number])->first();
        return ($wcl) ? true : false;
    }
}
