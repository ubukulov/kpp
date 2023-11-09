<?php


namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface IPermitRepository
{
    public function all(): Collection;
    public function getLastPermits(int $count = 20): Collection;
    public function getById(int $id): Model;
    public function create(array $attributes): ?Model;
}
