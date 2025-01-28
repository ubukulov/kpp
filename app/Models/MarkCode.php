<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarkCode extends Model
{
    use HasFactory;

    protected $table = 'marking_codes';
    protected $fillable = [
        'marking_details_id', 'scan_user_id', 'status', 'marking_code', 'box_number'
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    public function details()
    {
        return $this->belongsTo(MarkDetail::class);
    }
}
