<?php


namespace App\Repositories\Interfaces;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface IDirectionRepository
{
    public function all(): Collection;
    public function getById(int $id): Model;
}
