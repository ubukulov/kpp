<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    public $fillable = [
        'id', 'user_id', 'ip_address', 'user_agent', 'payload', 'last_activity', 'ids', 'technique_ids', 'zone_id'
    ];

    public $timestamps = false;

    public static function getIds()
    {
        $ids = [];
        $sessions = Session::whereNotNull('user_id')->get();
        foreach($sessions as $session) {
            $ids[] = $session->user_id;
            if(!is_null($session->ids)) {
                $arr_ids = explode(',', $session->ids);
                $ids = array_merge($ids, $arr_ids);
            }
        }
        return $ids;
    }

    public static function getTechniqueIds()
    {
        $sessions = Session::whereNotNull('technique_ids')->get();
        $ids = [];
        foreach($sessions as $session) {
            $arr_ids = explode(',', $session->technique_ids);
            $ids = array_merge($ids, $arr_ids);
        }
        return $ids;
    }
}
