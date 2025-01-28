<?php

namespace App\Models;

use App\Models\Cargo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoStock extends Model
{
    use HasFactory;

    protected $table = 'cargo_stocks';

    protected $fillable = [
        'cargo_id', 'cargo_item_id', 'status'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }

    public function cargo_item()
    {
        return $this->belongsTo(CargoItem::class, 'id', 'cargo_item_id');
    }
}
