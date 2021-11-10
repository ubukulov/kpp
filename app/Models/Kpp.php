<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Kpp extends Model
{
    use Sluggable;

    protected $table = 'kpp';

    protected $fillable = [
        'title', 'name'
    ];

    protected $dates = ['created_at', 'updated_at'];

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'name' => [
                'source' => 'title'
            ]
        ];
    }
}
