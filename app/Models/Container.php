<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    protected $fillable = [
        'number', 'container_type', 'company'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function container_stock()
    {
        return $this->hasOne(ContainerStock::class);
    }

    public function logs()
    {
        return $this->hasMany(ContainerLog::class)->orderBy('id', 'DESC');
    }
}
