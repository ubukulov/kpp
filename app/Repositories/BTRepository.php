<?php


namespace App\Repositories;

use App\Models\BT;
use App\Repositories\Interfaces\IBTRepository;
use Illuminate\Database\Eloquent\Collection;

class BTRepository implements IBTRepository
{
    public function all(): Collection
    {
        return BT::all();
    }
}
