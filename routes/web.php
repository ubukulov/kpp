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
    Route::get('/get-user-info/{id}', 'IndexController@getUserInfo');
    Route::get('/get-car-info/{nm}', 'IndexController@getCarInfo');
    Route::get('/get-driver-info/{nm}', 'IndexController@getDriverInfo');
    Route::post('/checking-container-for-application', 'IndexController@checkingContainerForApplication');
    Route::post('/order-permit-by-kpp', 'KppController@orderPermitByKpp');
    Route::post('/fix-date-out-for-current-permit', 'IndexController@fixDateOutForCurrentPermit');
    Route::get('/get-permits-list', 'IndexController@getPermits');
    Route::get('/command/print/{id}/{company_id?}/{foreign_car?}', 'KppController@start_print');
    Route::get('/get-not-completed-permits-for-week', 'IndexController@getNotCompletedPermitsForWeek');
    Route::post('/get-info-for-container', 'ContainerController@getInfoForContainer');

    # КПП Оператор
    Route::group(['middleware' => 'role:kpp-operator'], function(){
        Route::get('/security-kpp', 'KppController@index')->name('security.kpp');
        Route::post('/search/permit', 'IndexController@searchPermit')->name('search.permit');
        Route::get('/get-prev-permits-for-today', 'IndexController@getPrevPermitsForToday');
        Route::post('/scan-go-checking', 'ScangoController@scanGoChecking');
        Route::get('/get-last-5-logs-from-sql-server', 'ScangoController@getLast5Logs');
        Route::get('/get-permit-by-id/{id}', 'IndexController@getPermitById');

        Route::post('/permit/{id}/put-to-archive', 'IndexController@putToArchive');
        Route::get('/permit/{id}/checking-for-print', 'IndexController@checkingPermitForPrint');
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
        Route::post('/container/receive-container-by-keyboard', 'ContainerController@receiveContainerByKeyboard');
        Route::get('/get-container-tasks/{filter_id}', 'KTController@getContainerTasks');
        Route::get('/task/{id}/print', 'KTController@printTask');
        Route::post('task/position/cancel', 'KTController@taskPositionCancel')->name('task.position.cancel');
        Route::post('/task/position/edit', 'KTController@taskPositionEdit')->name('task.position.edit');
        Route::get('/container/{number}/get-logs', 'KTController@getContainerLogs');

        # Technique - учет техники
        Route::get('/technique-task-create', 'TechniqueController@taskCreate');
        Route::post('/technique/create-task-by-file', 'TechniqueController@createTaskByFile');
        Route::get('/get-technique-tasks', 'TechniqueController@getTechniqueTasks');
        Route::get('/technique_task/{id}/show-details', 'TechniqueController@showDetails');
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
        Route::get('/get-container-ships', 'ContainerController@getContainerShips');
        Route::post('/container/moving-container-to-another-zone', 'ContainerController@movingContainerToAnotherZone');
        Route::post('container/getting-rows', 'ContainerController@getListRows');
        Route::post('container/getting-containers-in-row', 'ContainerController@getListContainersInRow');
        Route::get('stats/getting-stats-for-me', 'StatController@getStatsForMe');
        Route::post('technique', 'ContainerController@noticeAboutTechnique');
    });

    # Контейнерный терминал - контролировщики
    Route::group(['prefix' => 'container-controller', 'middleware' => 'role:kt-controller'], function(){
        Route::get('/', 'KTController@controller')->name('kt.controller');
        Route::get('/get-container-tasks/{filter_id}', 'KTController@getContainerTasks');
        Route::get('/task/{id}/container-logs', 'KTController@showTaskContainerLogs2')->name('show.task-container-logs');
        Route::get('/task/{id}/import-logs', 'KTController@getContainerTaskLogs');
        Route::post('task/reject-cancel-position', 'KTController@rejectCancelPosition');
        Route::post('task/confirm-cancel-position', 'KTController@confirmCancelPosition');
        Route::post('task/reject-edit-position', 'KTController@rejectEditPosition');
        Route::post('task/confirm-edit-position', 'KTController@confirmEditPosition');

        # Учет техники
        Route::get('/get-technique-tasks', 'TechniqueController@getTechniqueTasks');
    });

    # Учет техники: контроллер
    Route::group(['prefix' => 'technique-controller', 'middleware' => 'role:technique-controller'], function(){
        Route::get('/', 'TechniqueController@techniqueController');
        Route::post('/get-information-by-qr-code', 'TechniqueController@getInformationByQRCode');
    });

    # Столовая
    Route::group(['prefix' => 'ashana', 'middleware' => 'role:ashana'], function(){
        Route::get('/', 'KitchenController@index')->name('kitchen.index');
        Route::get('/get-statistics', 'KitchenController@getStatistics');
        Route::post('/get-user-info', 'KitchenController@getUserInfo');
        Route::post('/fix-changes', 'KitchenController@fixChanges');
        Route::post('/get-logs', 'KitchenController@getLogs');
        Route::post('/generate-logs', 'KitchenController@generateLogs');
    });

    # Маркировка: Менеджер
    Route::group(['prefix' => 'marking', 'middleware' => 'role:mark-manager'], function(){
        Route::get('/', 'MarkController@index')->name('mark.index');
        Route::get('/create', 'MarkController@create')->name('mark.create');
        Route::post('/store', 'MarkController@store')->name('mark.store');
        Route::get('/{id}/show', 'MarkController@show')->name('mark.show');
        Route::post('/generate-excel', 'MarkController@generateExcel');
    });

    # Маркировка: Диспетчер
    Route::group(['prefix' => 'marking-manager', 'middleware' => 'role:mark-dispatcher'], function(){
        Route::get('/', 'MarkController@markManager')->name('mark.manager');
        Route::get('/get-markings', 'MarkController@getMarkings');
        Route::get('/get-printers', 'MarkController@getPrinters');
        Route::get('/{mark_id}/get-containers', 'MarkController@getContainers');
        Route::post('/command-print', 'MarkController@commandPrint');
        Route::get('/print-using-codes/{printer_id}', 'MarkController@printByUsingCodes'); // только для программиста :)
    });

    # Список белых машинов которые без пропусков заежает и выезжает
    Route::group(['prefix' => 'white-car-lists', 'middleware' => 'role:kpp-security'], function(){
        Route::get('/', 'ViewController@whiteCarLists')->name('white.car.lists');
        Route::post('/search-by-number', 'ViewController@searchByNumberInWCL');
        Route::post('/{id}/fix-date-in-time', 'ViewController@fixDateInTime');
    });

});


# Маршруты для тех водителей которые оформляет себе предварительную пропуск
Route::get('/driver', 'DriverController@index');
Route::post('/check-driver', 'DriverController@check_driver');
Route::post('/order-permit-by-driver', 'DriverController@orderPermitByDriver');

# Форма где можно посмотреть детально все информацию по пропускам
Route::get('/view-detail-info-permit', 'ViewController@index');
Route::get('/view-detail-info-permit/get-car-info/{nm}', 'ViewController@getCarInfo');
Route::get('/view-detail-info-permit/get-driver-info/{nm}', 'ViewController@getDriverInfo');
Route::post('/view-detail-info-permit/download-permits-for-selected-time', 'ViewController@getPermitsForSelectedTime')->name('download.permits');



Route::get('/form4', 'IndexController@form4');
Route::get('/form4/create', 'IndexController@form4Create')->name('form4.create');
Route::get('/form4/{id}/show', 'IndexController@form4Show')->name('form4.show');

# For the Screen
Route::get('/lv-order-screen', 'LVController@lvOrderScreen')->name('lv.order.screen');

Route::get('/scan-qr-code', 'IndexController@scanQR');
Route::post('/avigilon', 'IndexController@avigilon');
