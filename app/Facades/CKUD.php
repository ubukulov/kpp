<?php


namespace App\Facades;

use Illuminate\Support\Facades\Facade;
class CKUD extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ckud';
    }
}
