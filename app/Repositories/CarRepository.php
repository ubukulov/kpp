<?php


namespace App\Repositories;

use App\Models\Car;
use App\Repositories\Interfaces\ICarRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CarRepository implements ICarRepository
{
    public function getById(int $id): Model
    {
        return Car::find($id);
    }

    public function getByWhereFirst(array $condition): Model
    {
        return Car::where($condition)->first();
    }

    public function update(int $id, array $attributes): void
    {
        $car = Car::find($id);
        $car->update($attributes);
    }

    public function create(array $attributes): Model
    {
        return Car::create($attributes);
    }

    public function exists(string $tex_number): bool
    {
        $record = Car::where(['tex_number' => trim(strtoupper($tex_number))])->first();
        return ($record) ? true : false;
    }
}
