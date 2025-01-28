<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpineCode extends Model
{
    use HasFactory;

    protected $table = 'spine_codes';

    protected $fillable = [
        'spine_id', 'technique_task_id', 'vin_code'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function spine()
    {
        return $this->belongsTo(Spine::class);
    }

    public static function exists($technique_task_id, $vin_code): bool
    {
        $record = SpineCode::where(['technique_task_id' => $technique_task_id, 'vin_code' => $vin_code])->first();
        return (bool) $record;
    }

    public static function getData($technique_task_id, $vin_code)
    {
        return SpineCode::where(['technique_task_id' => $technique_task_id, 'vin_code' => $vin_code])->first();
    }
}
