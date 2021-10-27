<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhiteCarList extends Model
{
    protected $table = 'white_car_lists';

    protected $fillable = [
        'company_id', 'gov_number', 'status', 'kpp_name'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function getLogs()
    {
        return $this->hasMany(WhiteCarLog::class, 'wcl_id');
    }

    public function getStatusText()
    {
        return ($this->status == 'ok') ? 'В списке' : 'Не в списке';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ok');
    }
}
