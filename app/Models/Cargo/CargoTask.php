<?php

namespace App\Models\Cargo;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CargoTask extends Model
{
    use HasFactory;

    protected $table = 'cargo_tasks';

    protected $fillable = [
        'user_id', 'company_id', 'type', 'date_time', 'status', 'upload_file', 'agreement_id', 'comments', 'short_number', 'cargo'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function stocks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CargoStock::class);
    }

    public function services(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(CargoService::class, 'cargo_task_services');
    }

    public function history(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CargoTaskHistory::class);
    }

    public function getType()
    {
        return ($this->type == 'receive') ? __('words.receive') : __('words.ship');
    }

    public function getNumber()
    {
        return ($this->type == 'receive') ? 'IN_'.$this->id : 'OUT_'.$this->id;
    }

    public function canClose(): bool
    {
        $count = 0;
        if($this->status == 'completed') return false;
        foreach ($this->stocks as $cargoStock) {
            if ($this->type == 'receive') {
                if ($cargoStock->status != 'received') {
                    $count++;
                }
            }

            if ($this->type == 'ship') {
                if ($cargoStock->status != 'shipped') {
                    $count++;
                }
            }
        }
        return $count == 0;
    }
}
