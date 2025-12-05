<?php

namespace App\Models\Wagon;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WagonTrack extends Model
{
    use HasFactory;

    protected $table = 'wagon_tracks';

    protected $fillable = [
        'company_id', 'number', 'name', 'length', 'capacity'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function company(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
