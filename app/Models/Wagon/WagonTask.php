<?php

namespace App\Models\Wagon;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WagonTask extends Model
{
    use HasFactory;

    protected $table = 'wagon_tasks';

    protected $fillable = [
        'uuid', 'user_id', 'status', 'type', 'date_time', 'upload_file', 'comments'
    ];

    protected $dates = [
        'created_at', 'updated_at', 'date_time'
    ];

    protected $appends = ['number', 'is_closing'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WagonTaskItem::class);
    }

    public function stocks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WagonStock::class);
    }

    public function getNumberAttribute(): string
    {
        switch ($this->type) {
            case 'submission':
                return 'IN_' . $this->id;
            case 'cleaning':
                return 'OUT_' . $this->id;
            default:
                return 'MOV_' . $this->id;
        }
    }

    public function getIsClosingAttribute(): bool
    {
        switch ($this->type) {
            case 'submission':
                return !$this->stocks->contains('status', 'submission');
            case 'cleaning':
                return !$this->stocks->contains('status', 'cleaning_request');
            default:
                return !$this->stocks->contains('status', 'moving_request');
        }
    }
}
