<?php

namespace App\Providers;

use App\Classes\Astel;
use Illuminate\Support\ServiceProvider;
class AstelServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('astel', Astel::class);
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
