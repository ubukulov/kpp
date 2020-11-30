<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CategoryTC extends Model
{
    protected $table = 'categories_tc';

    protected $fillable = [
        'title', 'created_at', 'updated_at'
    ];
}
