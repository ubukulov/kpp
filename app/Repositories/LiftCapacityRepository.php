<?php


namespace App\Repositories;

use App\Models\LiftCapacity;
use App\Repositories\Interfaces\ILiftCapacityRepository;
use Illuminate\Database\Eloquent\Collection;

class LiftCapacityRepository implements ILiftCapacityRepository
{
    public function all(): Collection
    {
        return LiftCapacity::all();
    }
}
