<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasRolesAndPermissions;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, HasRolesAndPermissions, HasApiTokens;

    protected $fillable = [
        'company_id', 'position_id', 'department_id', 'full_name', 'phone', 'iin', 'email', 'password',
        'computer_name', 'printer_name', 'kpp_name', 'remember_token', 'uuid', 'image', 'badge', 'settings'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }

    public function position()
    {
        return $this->belongsTo('App\Models\Position');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function countAshanaToday()
    {
        return $this->hasMany(AshanaLog::class)->whereDate('date', Carbon::now())->count();
    }

    public function ashana()
    {
        return $this->hasMany(AshanaLog::class);
    }

    public function setUuidIfNot()
    {
        $str = $this->id."-".$this->full_name;
        $this->uuid = base64_encode($str);
        $this->save();
    }

    public function getUuid()
    {
        if(empty($this->uuid)) {
            //$this->setUuidIfNot();
        }
        return $this->uuid;
    }

    public function getWorkingStatusAll()
    {
        return $this->hasMany(UserHistory::class, 'user_id');
    }

    public function hasWorkingStatus()
    {
        return ($this->getWorkingStatusAll()->count() != 0) ? true : false;
    }

    public function createUserHistory(User $user, $data)
    {
        $data['user_id'] = $user->id;
        UserHistory::create($data);
    }

    public function getWorkingStatus()
    {
        $user_history = UserHistory::where(['user_id' => $this->id])->orderBy('id', 'DESC')->first();
        if(!$user_history){
            return UserHistory::create(['user_id' => $this->id, 'status' => 'works']);
        }

        return $user_history;
    }

    public function hasWorkPermission()
    {
        $current_status = $this->getWorkingStatus();
        if ($current_status) {
            // если есть история, то проверяем его статус
            if($current_status->status == 'fired') {
                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    public function generateUniqueRandomNumber()
    {
        $number = rand(1000000, 9999999);
        $user = User::whereUuid($number)->first();

        if($user) {
            $this->generateUniqueRandomNumber();
        }

        return $number;
    }

    public function hasItemInSettings($name, $key, $value)
    {
        $settings = json_decode($this->settings, true);
        if(!empty($settings) && isset($settings[$name][$key])) {
            $departments = explode(",", $settings[$name][$key]);
            if(in_array($value, $departments)) {
                return true;
            }
        }

        return false;
    }

    public function getCompanyIds() : array
    {
        if(is_null($this->settings)) {
            return [$this->company_id];
        }

        $settings = json_decode($this->settings, true);
        if(!empty($settings) && isset($settings['human_resources_departments'])) {

            if(!is_null($settings['human_resources_departments']['companies'])) {
                return explode(',', $settings['human_resources_departments']['companies']);
            }

        } else {
            return [];
        }
    }

    public function getDepartmentIds() : array
    {
        $settings = json_decode($this->settings, true);
        if(!empty($settings) && isset($settings['human_resources_departments'])) {

            if(!is_null($settings['human_resources_departments']['departments'])) {
                return explode(',', $settings['human_resources_departments']['departments']);
            }

        }

        return [];
    }
}
