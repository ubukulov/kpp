<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
class Parqour extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'parqour';
    }
}
