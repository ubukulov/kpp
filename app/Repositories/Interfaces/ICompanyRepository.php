<?php


namespace App\Repositories\Interfaces;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ICompanyRepository
{
    public function all(): Collection;
    public function getCompaniesOrderBy(string $column): Collection;
    public function getById(int $id): Model;
}
