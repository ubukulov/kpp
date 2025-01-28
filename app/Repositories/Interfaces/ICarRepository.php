<?php


namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ICarRepository
{
    public function getById(int $id): Model;
    public function getByWhereFirst(array $condition): Model;
    public function update(int $id, array $attributes): void;
    public function create(array $attributes): Model;
    public function exists(string $tex_number): bool;
}
