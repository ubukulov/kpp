<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'price', 'parent_id', 'bitrix24_id', 'description'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function contractors()
    {
        return $this->belongsToMany(ServiceContractor::class, 'service_contractor_services', 'service_id', 'service_contractor_id');
    }
}
