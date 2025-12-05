<?php

namespace App\Models\Wagon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Wagons\WagonStateTrait;

class WagonTaskItem extends Model
{
    use HasFactory, WagonStateTrait;

    protected $table = 'wagon_task_items';

    protected $fillable = [
        'wagon_task_id',
        'wagon_id',
        'wagon_track_id',
        'wagon_track_destination_id',
        'user_ids',
        'wagon_number',
        'cargo_name',
        'owner_name',
        'reception_datetime',
        'country_code',
        'state',
        'ordinal_number',
        'destination_station',
        'direction',
        'shipper_name',
        'status',
        'operation_type',
        'consignee_name',
    ];

    protected $appends = [
        'wagon_state_label',
        'wagon_containers_number'
    ];

    public function wagon_task(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WagonTask::class);
    }

    public function wagon_track(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WagonTrack::class);
    }

    public function wagon_track_destination(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WagonTrack::class);
    }

    public function wagon(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Wagon::class);
    }

    public function containers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WagonTaskItemContainer::class);
    }
}
