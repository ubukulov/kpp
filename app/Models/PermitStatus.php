<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermitStatus extends Model
{
    protected $table = 'permit_statuses';

    protected $fillable = [
        'permit_id', 'status', 'description', 'created_at', 'updated_at'
    ];

    public function permit()
    {
        return $this->belongsTo(Permit::class);
    }
}
