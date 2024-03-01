<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechniqueTask extends Model
{
    use HasFactory;

    protected $table = 'technique_tasks';

    protected $fillable = [
        'user_id', 'task_type', 'trans_type', 'status', 'upload_file', 'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function stocks()
    {
        return $this->hasMany(TechniqueStock::class);
    }

    // Метод формирует номер заявку
    public function getNumber()
    {
        return ($this->task_type == 'receive') ? 'IN_'.$this->id : 'OUT_'.$this->id;
    }

    // Метод возвращает тип транс заявку
    public function getTransType()
    {
        return ($this->trans_type == 'auto') ? __('words.auto') : __('words.train');
    }

    // Метод возвращает тип заявку
    public function getType()
    {
        return ($this->task_type == 'receive') ? __('words.receive') : __('words.ship');
    }

    public function canClose()
    {
        $countOfUnClosedPositions = 0;
        foreach($this->stocks as $stock) {
            if($stock->status != 'shipped') {
                $countOfUnClosedPositions++;
            }
        }

        return ($countOfUnClosedPositions == 0) ? true : false;
    }

    public function completeTask()
    {
        $count = 0;
        foreach ($this->stocks as $technique_stock) {
            if ($this->task_type == 'receive') {
                if ($technique_stock->status != 'received') {
                    $count++;
                }
            }

            /*if ($this->task_type == 'ship') {
                if ($container_stock->status != 'shipped') {
                    $count++;
                }
            }*/
        }

        return ($count == 0) ? true : false;
    }

    public function technique_stocks()
    {
        return $this->hasMany(TechniqueStock::class);
    }
}
