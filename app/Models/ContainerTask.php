<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContainerTask extends Model
{
    protected $table = 'container_tasks';

    protected $fillable = [
        'user_id', 'title', 'task_type', 'trans_type', 'status', 'document_base', 'upload_file', 'container_ids'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function import_logs()
    {
        return $this->hasMany(ImportLog::class);
    }

    public function isSuccessImport()
    {
        $import_logs = $this->import_logs;
        $count = 0;
        foreach($import_logs as $import_log) {
            if ($import_log->status == 'not') {
                $count++;
            }
        }
        return ($count==0) ? true : false;
    }

    public function deleteImportLogs()
    {
        $this->import_logs()->delete();
    }

    public function allowCloseThisTask($container_stocks)
    {
        $count = 0;
        foreach ($container_stocks as $container_stock) {
            if ($this->task_type == 'receive') {
                if ($container_stock->status != 'received') {
                    $count++;
                }
            }

            if ($this->task_type == 'ship') {
                if ($container_stock->status != 'shipped') {
                    $count++;
                }
            }
        }
        return ($count == 0) ? true : false;
    }
}
