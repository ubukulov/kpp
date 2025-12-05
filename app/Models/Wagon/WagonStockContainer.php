<?php

namespace App\Models\Wagon;

use App\Models\Container;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WagonStockContainer extends Model
{
    use HasFactory;

    protected $table = 'wagon_stock_containers';
    protected $fillable = [
        'wagon_stock_id', 'container_id', 'weight', 'consignee_name', 'seal_number_document', 'seal_number_fact', 'cargo_name', 'shipper_name',
        'container_number'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function wagon_stock(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WagonStock::class);
    }

    public function container(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Container::class);
    }
}
