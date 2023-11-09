<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WclLog extends Model
{
    use HasFactory;

    protected $table = 'wcl_logs';

    protected $fillable = [
        'wcl_com_id', 'gov_number', 'company'
    ];
}
