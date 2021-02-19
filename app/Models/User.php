<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasRolesAndPermissions;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable, HasRolesAndPermissions;

    protected $fillable = [
        'company_id', 'position_id', 'full_name', 'phone', 'email', 'password',
        'computer_name', 'printer_name',
        'remember_token', 'uuid', 'created_at', 'updated_at'
    ];

    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }

    public function position()
    {
        return $this->belongsTo('App\Models\Position');
    }
}
