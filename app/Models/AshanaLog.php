<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AshanaLog extends Model
{
    use HasFactory;

    protected $table = 'ashana_logs';

    /**
     * Описание
     * cashier_id - ИД компании столовое, берется из таблицы users
     * user_id - ид пользователя который кушает
     * company_id - ид компании в котором числяться сотрудник
     * din_type - возможное значение, 1 - комплек обед, 2 - булочки
     * date - дата и время когда кушал сотрудник
     */

    protected $fillable = [
        'cashier_id', 'user_id', 'company_id', 'din_type', 'date'
    ];
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'user_id', 'cashier_id');
    }
}
