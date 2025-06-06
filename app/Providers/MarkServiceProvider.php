<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Classes\MarkProduct;

class MarkServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('mark', MarkProduct::class);
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
