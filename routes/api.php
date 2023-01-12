<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/order-permit-by-driver', 'DriverController@orderPermitByDriver');

Route::group(['namespace' => 'API'], function(){
    Route::get('/get-driver-info-by-phone/{phone}', 'ApiController@getDriverInfoByPhone');
    Route::get('/get-companies-info', 'ApiController@getCompaniesInfo');
    Route::post('/sync-permit-status-with-other-platform', 'ApiController@changePermitStatus');
    Route::post('/authentication', 'ApiController@authentication');
});
