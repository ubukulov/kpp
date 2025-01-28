<?php

namespace App\Models;

use App\Models\Cargo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoItem extends Model
{
    use HasFactory;

    protected $table = 'cargo_items';

    protected $fillable = [
        'cargo_id', 'cargo_area_id', 'cargo_tonnage_type_id', 'cargo_tonnage_mark', 'vincode', 'technique_ids', 'car_number', 'count_place',
        'weight', 'square', 'employee_ids', 'cargo_work_type_ids', 'type', 'status'
    ];

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    public function cargo_area()
    {
        return $this->belongsTo(CargoArea::class, 'cargo_area_id', 'id');
    }

    public function cargo_tonnage()
    {
        return $this->belongsTo(CargoTonnage::class, 'cargo_tonnage_type_id', 'id');
    }
}
