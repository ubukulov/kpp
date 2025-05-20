<?php


namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Mark extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'mark';
    }
}
