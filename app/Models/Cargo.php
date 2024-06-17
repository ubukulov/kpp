<?php

namespace App\Models;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    use HasFactory;

    protected $table = 'cargo';

    protected $fillable = [
        'user_id', 'company_id', 'type', 'tonnage', 'mode', 'date_time'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function cargo_items()
    {
        return $this->hasMany(CargoItem::class);
    }

    public function getType()
    {
        return ($this->type == 'receive') ? __('words.receive') : __('words.ship');
    }

    public function canClose()
    {
        $i = 0;
        foreach($this->cargo_items() as $item) {
            if($item->status != 'completed') $i++;
        }
        return ($i == 0) ? true : false;
    }
}
