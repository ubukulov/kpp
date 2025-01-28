<?php


namespace App\Repositories;

use App\Models\Direction;
use App\Repositories\Interfaces\IDirectionRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class DirectionRepository implements IDirectionRepository
{
    public function all(): Collection
    {
        return Direction::all();
    }

    public function getById(int $id): Model
    {
        return Direction::find($id);
    }
}
