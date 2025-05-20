<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Permit extends Model
{
    protected $fillable = [
        'mark_car', 'gov_number', 'tex_number', 'company_id', 'company', 'pr_number', 'operation_type',
        'date_in', 'date_out', 'last_name', 'first_name', 'sur_name', 'phone', 'ud_number',
        'path_docs_fac', 'path_docs_back', 'created_at', 'updated_at', 'cat_tc_id', 'body_type_id',
        'is_driver', 'status', 'lc_id', 'bt_id', 'from_company', 'to_city', 'direction_id', 'employer_name',
        'foreign_car', 'kpp_name', 'incoming_container_number', 'outgoing_container_number', 'note',
        'planned_arrival_date', 'invoice_cmr_number', 'type'
    ];

    public function doc_fac()
    {
        return url('/uploads/'.$this->path_docs_fac);
    }

    public function doc_back()
    {
        return url('/uploads/'.$this->path_docs_back);
    }

    public function getFullname()
    {
        return $this->last_name.' '.$this->first_name.' '.$this->sur_name;
    }

    public function get_statuses()
    {
        return $this->hasMany(PermitStatus::class);
    }

    public static function getCountPermitsForToday()
    {
        return Permit::where('status', '!=', 'awaiting_print')
            ->orWhere('status', '!=', 'deleted')
            ->whereDate('date_in', Carbon::today())
            ->count();
    }

    public static function getUnCompletedPermits()
    {
        /*return Permit::whereNull('date_out')
            ->selectRaw('kpp_name, COUNT(*) as cnt')
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('kpp_name')
            ->orderBy('kpp_name')
            ->get();*/

        $month = [
            '01'  => 'Январь',
            '02'  => 'Февраль',
            '03'  => 'Март',
            '04'  => 'Апрель',
            '05'  => 'Май',
            '06'  => 'Июнь',
            '07'  => 'Июль',
            '08'  => 'Август',
            '09'  => 'Сентябрь',
            '10' => 'Октябрь',
            '11' => 'Ноябрь',
            '12' => 'Декабрь'
        ];

        $permits = Permit::whereNull('date_out')
            ->whereYear('created_at', Carbon::now()->year)
            ->orderBy('created_at')
            ->get();
        $arr = [];
        foreach ($permits as $permit) {
            $m = $month[date("m",  strtotime($permit->created_at))];
            if(array_key_exists($m, $arr)){
                if(array_key_exists($permit->kpp_name, $arr[$m])){
                    $arr[$m][$permit->kpp_name] += 1;
                } else {
                    $arr[$m][$permit->kpp_name] = 1;
                }
            } else {
                $arr[$m][$permit->kpp_name] = 1;
            }
        }

        foreach ($arr as &$monthData) {
            if (isset($monthData[""])) {
                $monthData["prev"] = $monthData[""];
                unset($monthData[""]);
            }
            ksort($monthData);
        }

        unset($monthData);

        return $arr;
    }
}
