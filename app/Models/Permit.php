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
        'planned_arrival_date', 'invoice_cmr_number'
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
        return Permit::where(['status' => 'printed'])->whereDate('date_in', '=', Carbon::today()->toDateString())->get()->count();
    }
}
