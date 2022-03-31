<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WclCompany extends Pivot
{
    protected $table = 'wcl_companies';

    protected $fillable = [
        'wcl_id', 'company_id', 'status'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function wcl()
    {
        return $this->belongsTo(WhiteCarList::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public static function exists($wcl_id, $company_id)
    {
        $wcl_company = WclCompany::where(['wcl_id' => $wcl_id, 'company_id' => $company_id])
            ->first();
        return ($wcl_company) ? true : false;
    }
}
