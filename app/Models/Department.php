<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'title', 'company_id', 'created_at', 'updated_at'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
