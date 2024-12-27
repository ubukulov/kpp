<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
class ANT extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ant';
    }
}
