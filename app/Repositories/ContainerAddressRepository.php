<?php


namespace App\Repositories;


use App\Repositories\Interfaces\IContainerAddressRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\ContainerAddress;

class ContainerAddressRepository implements IContainerAddressRepository
{
    public function all(): Collection
    {
        return ContainerAddress::all();
    }

    public function getNeeds(): Collection
    {
        return ContainerAddress::select(['zone', 'title'])
            ->whereIn('kind', ['r', 'k', 'pole', 'cia', 'rich'])
            ->orderBy('title', 'ASC')
            ->groupBy('zone')
            ->get();
    }
}
