<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Classes\Parqour;
class ParqourServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('parqour', Parqour::class);
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
