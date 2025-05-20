<?php

namespace App\Providers;

use App\Classes\ANTTechnology;
use Illuminate\Support\ServiceProvider;

class ANTServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('ant', ANTTechnology::class);
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
