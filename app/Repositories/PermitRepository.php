<?php


namespace App\Repositories;


use App\Repositories\Interfaces\IPermitRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Permit;
use Illuminate\Database\Eloquent\Model;

class PermitRepository implements IPermitRepository
{
    public function all(): Collection
    {
        return Permit::all();
    }

    public function getLastPermits(int $count = 20): Collection
    {
        return Permit::orderBy('id', 'DESC')
            ->take($count)
            ->get();
    }

    public function getById(int $id): Model
    {
        return Permit::find($id);
    }

    public function create(array $attributes): ?Model
    {
        return Permit::create($attributes);
    }
}
