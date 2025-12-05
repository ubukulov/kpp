<?php

namespace App\Models\Wagon;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WagonShift extends Model
{
    use HasFactory;

    protected $table = 'wagon_shifts';
    protected $fillable = [
        'wagon_log_id', 'user_id'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function wagonLog(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(WagonLog::class, 'wagon_log_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
