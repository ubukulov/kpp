<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Classes\RusGUARD;

class RusGuardServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('ckud', RusGUARD::class);
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
