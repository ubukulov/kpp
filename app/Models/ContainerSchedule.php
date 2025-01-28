<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContainerSchedule extends Model
{
    use HasFactory;

    protected $table = 'container_schedule';

    protected $fillable = [
        'user_id', 'crane_id', 'ids', 'technique_id'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function technique(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Technique::class);
    }

    public static function ids(): array
    {
        $ids = [];
        foreach(ContainerSchedule::whereNotNull('ids')->get() as $schedule){
            $explode = explode(',', $schedule->ids);
            if(count($explode) == 2){
                $ids[] = $explode[0];
                $ids[] = $explode[1];
            } else {
                $ids[] = $schedule->ids;
            }
        }
        return $ids;
    }
}
