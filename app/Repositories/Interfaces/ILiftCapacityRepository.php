<?php


namespace App\Repositories\Interfaces;


use Illuminate\Database\Eloquent\Collection;

interface ILiftCapacityRepository
{
    public function all(): Collection;
}
