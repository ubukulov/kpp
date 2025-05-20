<?php

namespace App\Models\Cargo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoStock extends Model
{
    use HasFactory;

    protected $table = 'cargo_stocks';

    protected $fillable = [
        'cargo_id', 'cargo_task_id', 'cargo_area_id', 'status', 'vin_code', 'quantity', 'quantity_reserved', 'weight', 'driver_name', 'car_number', 'image', 'cargo_type',
        'cargo_information'
    ];

    /*
     * cargo_type
     * 1 - разобранная
     * 2 - самоход
     * */

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function cargo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Cargo::class);
    }

    public function cargoTask(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CargoTask::class);
    }

    public function cargoArea(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CargoArea::class);
    }
}
