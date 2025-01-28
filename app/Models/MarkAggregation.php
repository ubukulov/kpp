<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkAggregation extends Model
{
    use HasFactory;

    protected $table = 'marking_aggregations';

    protected $fillable = [
        'sscc_pallet_id', 'sscc_box_id', 'gtin', 'article', 'km'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public static function lastBox($sscc_pallet_id)
    {
        $aggregation = MarkAggregation::where(['sscc_pallet_id' => $sscc_pallet_id])
            ->whereNotNull('sscc_box_id')
            ->orderBy('id', 'DESC')
            ->get();
        if(count($aggregation) > 0) {
            $ssccBox = PalletSSCC::findOrFail($aggregation[0]->sscc_box_id);
            return $ssccBox->code;
        }

        return null;
    }

    public static function lastProduct($pallet_id, $box_id)
    {
        $aggregation = MarkAggregation::where(['sscc_pallet_id' => $pallet_id, 'sscc_box_id' => $box_id])
            ->whereNotNull('km')
            ->orderBy('id', 'DESC')
            ->first();
        if($aggregation) {
            return $aggregation->gtin;
        }

        return null;
    }

    public static function factBox($SSCCPal)
    {
        $aggregation = MarkAggregation::where(['sscc_pallet_id' => $SSCCPal->id])
            ->whereNotNull('sscc_box_id')
            ->groupBy('sscc_box_id')
            ->get();
        return count($aggregation);
    }
}
