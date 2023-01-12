<?php

namespace App\Providers;

use App\Repositories\BTRepository;
use App\Repositories\CarRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\DirectionRepository;
use App\Repositories\DriverRepository;
use App\Repositories\Interfaces\IBTRepository;
use App\Repositories\Interfaces\ICarRepository;
use App\Repositories\Interfaces\ICompanyRepository;
use App\Repositories\Interfaces\IDirectionRepository;
use App\Repositories\Interfaces\IDriverRepository;
use App\Repositories\Interfaces\ILiftCapacityRepository;
use App\Repositories\Interfaces\IPermitRepository;
use App\Repositories\LiftCapacityRepository;
use App\Repositories\PermitRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(IPermitRepository::class, PermitRepository::class);
        $this->app->bind(ICompanyRepository::class, CompanyRepository::class);
        $this->app->bind(ILiftCapacityRepository::class, LiftCapacityRepository::class);
        $this->app->bind(IBTRepository::class, BTRepository::class);
        $this->app->bind(IDirectionRepository::class, DirectionRepository::class);
        $this->app->bind(ICarRepository::class, CarRepository::class);
        $this->app->bind(IDriverRepository::class, DriverRepository::class);
    }
}
