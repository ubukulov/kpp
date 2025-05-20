<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpineCode extends Model
{
    use HasFactory;

    protected $table = 'spine_codes';

    protected $fillable = [
        'spine_id', 'task_id', 'vin_code'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function spine()
    {
        return $this->belongsTo(Spine::class);
    }

    public static function exists($task_id, $vin_code): bool
    {
        $record = SpineCode::where(['task_id' => $task_id, 'vin_code' => $vin_code])->first();
        return (bool) $record;
    }

    public static function getData($task_id, $vin_code)
    {
        return SpineCode::where(['task_id' => $task_id, 'vin_code' => $vin_code])->first();
    }
}
