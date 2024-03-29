<?php


namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;

class BaseApiController extends Controller
{
    protected $user = null;

    public function __construct()
    {
        if(is_null($this->user)) {
            $this->user = auth('sanctum')->user();
        }
    }
}
