<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertItem extends Model
{
    use HasFactory;

    protected $table = 'alert_items';

    protected $fillable = [
        'alert_id', 'sms', 'voice', 'whatsapp', 'interval', 'message_sms', 'message_voice', 'message_whatsapp'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }

    /**
     * @return mixed
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'alert_users');
    }

    /**
     * @param mixed ...$alert
     * @return bool
     */
    public function hasUser(... $alert ) {
        foreach ($alert as $k) {
            if ($this->users->contains('id', $k)) {
                return true;
            }
        }
        return false;
    }
}
