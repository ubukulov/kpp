<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechniqueLog extends Model
{
    use HasFactory;

    protected $table = 'technique_logs';

    protected $fillable = [
        'user_id', 'technique_task_id', 'company_id', 'owner', 'technique_type', 'mark', 'vin_code', 'operation_type', 'address_from', 'address_to', 'color',
        'defect', 'defect_note', 'defect_image', 'spine_number'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];
}
