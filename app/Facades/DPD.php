<?php


namespace App\Facades;

use Illuminate\Support\Facades\Facade;
class DPD extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'DPD';
    }
}
