<?php


namespace App\Repositories;

use App\Models\Driver;
use App\Repositories\Interfaces\IDriverRepository;
use Illuminate\Database\Eloquent\Model;

class DriverRepository implements IDriverRepository
{
    public function exists(string $ud_number): bool
    {
        $record = Driver::where(['ud_number' => trim(strtoupper($ud_number))])->first();
        return ($record) ? true : false;
    }

    public function create(array $attributes): Model
    {
        return Driver::create($attributes);
    }
}
