<?php

namespace App\Models\Service;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceContractor extends Model
{
    use HasFactory;

    protected $table = 'service_contractors';

    protected $fillable = [
        'name', 'bitrix24_id'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_contractor_services', 'service_contractor_id', 'service_id');
    }
}
