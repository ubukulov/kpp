<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CKUDLogs extends Model
{
    use HasFactory;

    protected $table = 'ckud_logs';

    protected $fillable = [
        'LogMessageSubType', 'LogMessageType', 'Message', 'ckud_id', 'DateTime', 'Details', 'DriverID', 'DriverName',
        'EmployeeFirstName', 'EmployeeLastName', 'EmployeeSecondName', 'EmployeeTableNumber', 'EmployeeID',
        'EmployeeGroupFullName', 'EmployeeGroupId', 'EmployeeGroupName'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];
}
