<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $table = 'statuses';

    protected $fillable = [
        'name', 'bitrix24'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public static function getStatusName($stateId)
    {
        return Status::where('bitrix24', $stateId)->value('name') ?? $stateId;
    }

    public static function getStatusId($stateId)
    {
        return Status::where('bitrix24', $stateId)->value('id');
    }
}
