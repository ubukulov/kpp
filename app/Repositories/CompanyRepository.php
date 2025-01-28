<?php


namespace App\Repositories;


use App\Repositories\Interfaces\ICompanyRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Company;
use Illuminate\Database\Eloquent\Model;

class CompanyRepository implements ICompanyRepository
{
    public function all(): Collection
    {
        return Company::all();
    }

    public function getCompaniesOrderBy(string $column): Collection
    {
        return Company::orderBy($column)
            ->select('id', 'short_en_name')
            ->where('type_company', '!=', 'technique')
            ->where('status', '=', 'ok')
            ->get();
    }

    public function getById(int $id): Model
    {
        return Company::find($id);
    }
}
