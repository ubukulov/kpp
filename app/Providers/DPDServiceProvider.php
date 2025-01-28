<?php

namespace App\Providers;

use App\Classes\DPD;
use Illuminate\Support\ServiceProvider;

class DPDServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('DPD', DPD::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
