<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhiteCarLog extends Model
{
    protected $table = 'white_car_logs';

    protected $fillable = [
        'wcl_id', 'user_id', 'company_id', 'gov_number', 'status'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function wcl()
    {
        return $this->belongsTo(WhiteCarList::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
