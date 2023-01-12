<?php


namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface IContainerAddressRepository
{
    public function all(): Collection;
    public function getNeeds(): Collection;
}
