<?php


namespace App\Traits;

use App\Models\Kpp;

trait HasKppOnCompany
{
    /**
     * @return mixed
     */
    public function kpps()
    {
        return $this->belongsToMany(Kpp::class, 'companies_kpp');
    }

    /**
     * @param mixed ...$kpp
     * @return bool
     */
    public function hasKpp(... $kpp ) {
        foreach ($kpp as $k) {
            if ($this->kpps->contains('name', $k)) {
                return true;
            }
        }
        return false;
    }
}
