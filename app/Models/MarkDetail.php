<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkDetail extends Model
{
    use HasFactory;

    protected $table = 'marking_details';
    protected $fillable = [
        'marking_id', 'container_number', 'invoice_number', 'gtin', 'line1', 'line2', 'line3', 'line4', 'line5', 'line6',
        'line7', 'line8', 'line9', 'line10', 'line11', 'eac', 'mc'
    ];
    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function mark()
    {
        return $this->belongsTo(Mark::class);
    }

    public function codes()
    {
        return $this->hasMany(MarkCode::class, 'marking_details_id');
    }

    public function free_codes()
    {
        return $this->hasMany(MarkCode::class, 'marking_details_id')->where(['status' => 'not_marked']);
    }
}
