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

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/order-permit-by-driver', 'DriverController@orderPermitByDriver');

Route::group(['namespace' => 'API'], function(){
    Route::get('/get-driver-info-by-phone/{phone}', 'ApiController@getDriverInfoByPhone');
    //Route::get('/get-companies-info', 'ApiController@getCompaniesInfo');
    Route::post('/sync-permit-status-with-other-platform', 'ApiController@changePermitStatus');
    Route::post('/authentication', 'ApiController@authentication');
});*/

Route::post('/auth/login', 'API\ApiController@loginUser');
Route::group(['namespace' => 'API', 'middleware' => 'auth:sanctum'], function(){
    Route::get('/get-companies-info', 'ApiController@getCompaniesInfo');
    Route::get('/get-roles', 'UserController@getUserRoles');
    Route::group(['prefix' => 'user'], function(){
        Route::get('/get-user-info', 'UserController@getUserInfoByToken');
    });

    # Technique
    Route::prefix('technique')->group(function(){
        Route::get('/get-technique-places', 'TechniqueController@getTechniquePlaces');
        Route::post('/get-information-by-qr-code', 'TechniqueController@getInformationByQRCode');
        Route::post('/receive-technique-to-place', 'TechniqueController@receiveTechniqueToPlace');
        Route::post('/move-technique-to-other-place', 'TechniqueController@moveTechniqueToOtherPlace');
        Route::post('/shipping-technique', 'TechniqueController@shippingTechnique');
    });

    # Ashana
    Route::prefix('ashana')->group(function(){
        Route::get('/get-items', 'KitchenController@getItems');
        Route::post('/get-items-by-filter', 'KitchenController@getItemsByFilter');
        Route::post('get-user-by-uuid', 'KitchenController@getUserByUuid');
        Route::post('fix-changes', 'KitchenController@fixChanges');
    });

    # Webcont
    Route::prefix('webcont')->group(function(){
        Route::get('get-webcont-containers-zones', 'WebcontController@getContainersZones');
        Route::get('get-webcont-techniques', 'WebcontController@getTechniques');
        Route::post('get-container-info', 'WebcontController@getContainerInfo');
        Route::get('get-free-rows', 'WebcontController@getFreeRows');
    });
});
