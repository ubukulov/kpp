<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $table = 'import_logs';

    protected $fillable = [
        'container_task_id', 'user_id', 'container_number', 'comments', 'status', 'import_date', 'ip'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function container_task()
    {
        return $this->belongsTo(ContainerTask::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
