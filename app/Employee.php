<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Employee extends Authenticatable
{
    use Notifiable;

    protected $table = 'employees';

    protected $fillable = [
        'company_id', 'position_id', 'full_name', 'phone', 'email', 'password',
        'created_at', 'updated_at'
    ];

    public function company()
    {
        return $this->belongsTo('App\Company');
    }

    public function position()
    {
        return $this->belongsTo('App\Position');
    }
}
