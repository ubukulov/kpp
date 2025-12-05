<?php

namespace App\Models\Wagon;

use App\Models\Container;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WagonTaskItemContainer extends Model
{
    use HasFactory;

    protected $table = 'wagon_task_item_containers';
    protected $fillable = [
        'wagon_task_item_id', 'container_id', 'container_number', 'weight', 'consignee_name', 'seal_number_document',
        'seal_number_fact', 'cargo_name', 'shipper_name'
    ];

    public function wagon_task_item(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WagonTaskItem::class);
    }

    public function container(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Container::class);
    }
}
