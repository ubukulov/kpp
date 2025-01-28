<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mark extends Model
{
    use HasFactory;

    protected $table = 'marking';
    protected $fillable = [
        'user_id', 'status', 'upload_file', 'created_at', 'updated_at'
    ];

    public function details()
    {
        return $this->hasMany(MarkDetail::class, 'marking_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function free_codes()
    {
        $free_codes = MarkCode::where(['marking_codes.status' => 'not_marked', 'marking_details.marking_id' => $this->id])
                    ->join('marking_details', 'marking_details.id', 'marking_codes.marking_details_id')
                    ->get();
        return count($free_codes);
    }
}
