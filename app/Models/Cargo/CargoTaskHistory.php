<?php

namespace App\Models\Cargo;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoTaskHistory extends Model
{
    use HasFactory;

    protected $table = 'cargo_task_history';

    protected $fillable = [
        'cargo_task_id', 'user_id', 'cargo_id', 'vin_code', 'status', 'image', 'created_at', 'updated_at'
    ];

    public function cargoTask(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CargoTask::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function exists($cargoTaskId, $cargoId): bool
    {
        return (CargoTaskHistory::where(['cargo_task_id' => $cargoTaskId, 'cargo_id' => $cargoId])->count() > 0);
    }
}
