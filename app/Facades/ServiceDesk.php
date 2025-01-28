<?php


namespace App\Facades;

use Illuminate\Support\Facades\Facade;
class ServiceDesk extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'sk';
    }
}
