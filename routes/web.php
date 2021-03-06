<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/login', 'AuthController@loginForm')->name('login');
Route::post('/login', 'AuthController@authenticate')->name('authenticate');
Route::get('/logout', 'AuthController@logout')->name('logout');

Route::group(['middleware' => ['auth']], function() {
    Route::get('/', 'IndexController@welcome')->name('home');

    Route::group(['middleware' => 'role:kpp-operator'], function(){
        Route::get('/security-kpp', 'IndexController@securityKpp')->name('security.kpp');
        Route::post('/order-permit-by-kpp', 'IndexController@orderPermitByKpp');
        Route::get('/get-user-info/{id}', 'IndexController@getUserInfo');
        Route::get('/get-car-info/{nm}', 'IndexController@getCarInfo');
        Route::get('/get-driver-info/{nm}', 'IndexController@getDriverInfo');
        Route::get('/command/print/{id}/{company_id?}', 'IndexController@start_print');
        Route::post('/search/permit', 'IndexController@searchPermit')->name('search.permit');
        Route::get('/get-permits-list', 'IndexController@getPermits');
        Route::get('/get-prev-permits-for-today', 'IndexController@getPrevPermitsForToday');
        Route::post('/fix-date-out-for-current-permit', 'IndexController@fixDateOutForCurrentPermit');
        Route::post('/scan-go-checking', 'ScangoController@scanGoChecking');
        Route::get('/get-last-5-logs-from-sql-server', 'ScangoController@getLast5Logs');
        Route::get('/get-permit-by-id/{id}', 'IndexController@getPermitById');
        Route::get('/personal-control', 'PersonalController@index');
        Route::post('/scanning-personal-control', 'PersonalController@scanningPersonalWithBarcode');
        Route::post('/fix-date-time-for-current-user', 'PersonalController@fixDateTimeForCurrentUser');
    });
});

Route::get('/driver', 'DriverController@index');
Route::post('/check-driver', 'DriverController@check_driver');
Route::post('/order-permit-by-driver', 'DriverController@orderPermitByDriver');
