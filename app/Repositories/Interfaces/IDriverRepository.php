<?php


namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface IDriverRepository
{
    public function exists(string $ud_number): bool;
    public function create(array $attributes): Model;
}
