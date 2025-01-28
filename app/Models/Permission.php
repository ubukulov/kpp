<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Role;
use Cviebrock\EloquentSluggable\Sluggable;

class Permission extends Model
{
    use Sluggable;

    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ]
        ];
    }

    protected $fillable = [
        'name', 'slug', 'created_at', 'updated_at'
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class,'roles_permissions');
    }
}
