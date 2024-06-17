<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechniqueStock extends Model
{
    use HasFactory;

    protected $table = 'technique_stocks';

    protected $fillable = [
        'technique_task_id', 'technique_type_id', 'technique_place_id', 'company_id', 'color', 'mark', 'vin_code', 'status', 'image',
        'defect', 'defect_note', 'defect_image'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function technique_task()
    {
        return $this->belongsTo(TechniqueTask::class);
    }

    public function technique_type()
    {
        return $this->belongsTo(TechniqueType::class);
    }

    public function technique_place()
    {
        return $this->belongsTo(TechniquePlace::class);
    }
}
