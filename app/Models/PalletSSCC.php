<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PalletSSCC extends Model
{
    use HasFactory;
    protected $table = 'pallet_sscc';
    protected $fillable = [
        'user_id', 'code', 'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
