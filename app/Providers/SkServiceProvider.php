<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Classes\ServiceDesk;

class SkServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('sk', ServiceDesk::class);
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
