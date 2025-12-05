<?php

namespace App\Models\Wagon;

use App\Models\Container;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Wagons\WagonStateTrait;

class WagonStock extends Model
{
    use HasFactory, WagonStateTrait;

    protected $table = 'wagon_stocks';

    protected $fillable = [
        'user_id', 'wagon_id', 'wagon_task_id', 'wagon_track_id', 'wagon_track_destination_id',
        'reception_datetime', 'ordinal_number', 'country_code', 'owner_name', 'cargo_name', 'state',
        'shipper_name', 'operation_type', 'status', 'destination_station', 'direction', 'consignee_name',
        'gap'
    ];

    protected $dates = [
        'reception_datetime', 'created_at', 'updated_at'
    ];

    protected $appends = [
        'wagon_state_label',
        'wagon_containers_number'
    ];

    public function wagon(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Wagon::class);
    }

    public function wagon_task(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WagonTask::class);
    }

    public function wagon_track(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WagonTrack::class, 'wagon_track_id');
    }

    public function wagon_track_destination(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WagonTrack::class, 'wagon_track_destination_id');
    }

    public function containers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WagonStockContainer::class);
    }
}
