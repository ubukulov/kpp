<?php


namespace App\Repositories\Interfaces;


use Illuminate\Database\Eloquent\Collection;

interface IBTRepository
{
    public function all(): Collection;
}
