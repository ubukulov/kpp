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

    # КПП Оператор
    Route::group(['middleware' => 'role:kpp-operator'], function(){
        Route::get('/security-kpp', 'IndexController@securityKpp')->name('security.kpp');
        Route::post('/order-permit-by-kpp', 'IndexController@orderPermitByKpp');
        Route::get('/get-user-info/{id}', 'IndexController@getUserInfo');
        Route::get('/get-car-info/{nm}', 'IndexController@getCarInfo');
        Route::get('/get-driver-info/{nm}', 'IndexController@getDriverInfo');
        Route::get('/command/print/{id}/{company_id?}/{foreign_car?}', 'IndexController@start_print');
        Route::post('/search/permit', 'IndexController@searchPermit')->name('search.permit');
        Route::get('/get-permits-list', 'IndexController@getPermits');
        Route::get('/get-prev-permits-for-today', 'IndexController@getPrevPermitsForToday');
        Route::post('/fix-date-out-for-current-permit', 'IndexController@fixDateOutForCurrentPermit');
        Route::post('/scan-go-checking', 'ScangoController@scanGoChecking');
        Route::get('/get-last-5-logs-from-sql-server', 'ScangoController@getLast5Logs');
        Route::get('/get-permit-by-id/{id}', 'IndexController@getPermitById');
        Route::get('/get-not-completed-permits-for-week', 'IndexController@getNotCompletedPermitsForWeek');
    });

    # Контроль персонала
    Route::group(['middleware' => 'role:personal-control'], function(){
        Route::get('/personal-control', 'PersonalController@index')->name('personal.control');
        Route::post('/scanning-personal-control', 'PersonalController@scanningPersonalWithBarcode');
        Route::post('/fix-date-time-for-current-user', 'PersonalController@fixDateTimeForCurrentUser');
    });

    # Контейнерный терминал - оператор
    Route::group(['prefix' => 'container-terminals','middleware' => 'role:kt-operator'], function(){
        Route::get('/', 'KTController@operator')->name('kt.kt_operator');
        Route::get('/create-task', 'KTController@createTask')->name('o.create.task');
        Route::post('/container/receive-container-by-operator', 'ContainerController@receiveContainerByOperator')->name('receive.container');
        Route::get('/task/{id}/import-logs', 'KTController@showTaskImportLogs')->name('show.task-import-logs');
        Route::get('/task/{id}/container-logs', 'KTController@showTaskContainerLogs')->name('show.task-container-logs');
        Route::get('/task/{id}/edit', 'KTController@editTask')->name('edit.task');
        Route::post('/container/{id}/update-container-task', 'ContainerController@updateContainerTask')->name('update.container-task');
        Route::get('/task/{id}/completed', 'KTController@completeTask')->name('completed.task');
    });

    # Контейнерный терминал - крановщик (стропольщик)
    Route::group(['prefix' => 'container-crane', 'middleware' => 'role:kt-crane'], function(){
        Route::get('/', 'KTController@crane')->name('kt.kt_crane');
        Route::get('/get-zones', 'ContainerController@getZones');
        Route::get('/get-containers', 'ContainerController@getContainers');
        Route::get('/get-techniques', 'ContainerController@getTechniques');
        Route::post('/container/receive', 'ContainerController@receiveContainer');
        Route::post('/container/search-and-getting-free-rows', 'ContainerController@getFreeRows');
        Route::post('/container/search-and-getting-free-places', 'ContainerController@getFreePlaces');
        Route::post('/container/search-and-getting-free-floors', 'ContainerController@getFreeFloors');
        Route::post('/container/receive-container-change', 'ContainerController@receiveContainerChange');
        Route::post('/container/moving-container-change', 'ContainerController@movingContainerChange');
        Route::post('/get-info-for-container', 'ContainerController@getInfoForContainer');
        Route::post('/checking-the-container-for-movement', 'ContainerController@checkingTheContainerForMovement');
        Route::post('/checking-the-container-for-dispensing', 'ContainerController@checkingTheContainerForDispensing');
        Route::post('/container/shipping-container-change', 'ContainerController@shippingContainerChange');
    });
});

Route::get('/driver', 'DriverController@index');
Route::post('/check-driver', 'DriverController@check_driver');
Route::post('/order-permit-by-driver', 'DriverController@orderPermitByDriver');

# Форма где можно посмотреть детально все информацию по пропускам
Route::get('/view-detail-info-permit', 'ViewController@index');
Route::get('/view-detail-info-permit/get-car-info/{nm}', 'ViewController@getCarInfo');
Route::get('/view-detail-info-permit/get-driver-info/{nm}', 'ViewController@getDriverInfo');
Route::post('/view-detail-info-permit/download-permits-for-selected-time', 'ViewController@getPermitsForSelectedTime')->name('download.permits');
