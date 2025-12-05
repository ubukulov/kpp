<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cabinet\ReportController;
use App\Http\Controllers\Cabinet\CabinetController;
use App\Http\Controllers\Cabinet\EmployeeController;
use App\Http\Controllers\Cabinet\PermitController;
use App\Http\Controllers\Cabinet\WhiteCarController;
use App\Http\Controllers\Cabinet\PositionController;
use App\Http\Controllers\Cabinet\DepartmentController;
use App\Http\Controllers\Cabinet\WebcontController;
use App\Http\Controllers\Cabinet\TechniqueController;
use App\Http\Controllers\Cabinet\CargoKeeperController;
use App\Http\Controllers\Cabinet\WmsController;
use App\Http\Controllers\Cabinet\UtilityController;
use App\Http\Controllers\Cabinet\KitchenController;
use App\Http\Controllers\Cabinet\ServiceController;
use App\Http\Controllers\Cabinet\DispatcherController;

# Кабинет клиента
Route::group(['prefix' => 'cabinet', 'middleware' => ['auth'], 'namespace' => 'Cabinet'], function(){
    Route::get('/', 'CabinetController@cabinet')->name('cabinet');
    Route::resource('/employees', 'EmployeeController', ['as' => 'cabinet']);
    Route::resource('/permits', 'PermitController', ['as' => 'cabinet']);

    Route::post('/get/permits', 'PermitController@getPermits');
    Route::get('/employees/{id}/badge', 'EmployeeController@badge')->name('employee.badge');
    Route::get('/employees/badges/{ids}', 'EmployeeController@badges');

    # Отчеты по машинам
    Route::get('/reports', 'ReportController@index')->name('cabinet.report.index');
    Route::post('/reports/download-report', 'ReportController@downloadReport');

    # КПП для Самсунга
    Route::get('/kpp/samsung', 'CabinetController@kpp_samsung')->name('cabinet.kpp.samsung');
    Route::get('/kpp/samsung/get100logs', 'CabinetController@get100Logs');
    Route::post('/kpp/samsung/get-document-by-code', 'CabinetController@getDocumentByCode');

    # Штрих-код для Самсунга
    Route::get('/samsung/barcode', 'CabinetController@barcode')->name('cabinet.barcode.samsung');
    Route::get('/samsung/barcode/get-orders', 'CabinetController@getOrders');
    Route::post('/samsung/barcode/command-print', 'CabinetController@printOrders');

    # Штрих-код для Bosch
    Route::get('/bosch/barcode', 'CabinetController@getBarcodeForBosch')->name('cabinet.barcode.bosch');
    Route::get('/bosch/barcode/get-orders', 'CabinetController@getOrdersForBosch');
    Route::post('/bosch/barcode/command-print', 'CabinetController@printOrdersBosch');
    Route::post('/bosch/barcode/get-sscc', 'CabinetController@getSSCC');

    # Растаможка Самсунг
    Route::get('customs', 'CabinetController@customs')->name('cabinet.customs.index');
    Route::post('/get/permits-customs', 'CabinetController@getPermitsCustoms');

    # White Cars list
    Route::resource('/white-car-list', 'WhiteCarController', ['as' => 'cabinet']);
    Route::get('/white-car-list/{id}/destroy', 'WhiteCarController@destroy')->name('cabinet.wcl.destroy');

    Route::get('white-car-list/guest/cars', 'WhiteController@guestCars')->name('cabinet.white-cars.guest.index');
    Route::get('white-car-list/guest/cars/create', 'WhiteCarController@guestCreate')->name('cabinet.white-cars.guest.create');
    Route::post('white-car-list/guest/cars/store', 'WhiteCarController@guestStore')->name('cabinet.white-cars.guest.store');

    # Position's routes
    Route::resource('/position', 'PositionController', ['as' => 'cabinet']);

    # Department's routes
    Route::resource('/department', 'DepartmentController', ['as' => 'cabinet']);

    # WEBCONT
    Route::get('webcont', 'WebcontController@index')->name('cabinet.webcont.index');
    Route::get('webcont/{id}/show', 'WebcontController@show')->name('cabinet.webcont.show');
    Route::get('webcont/aftos', 'WebcontController@aftosWebcont')->name('cabinet.webcont.aftos');
    Route::post('webcont/aftos/search', 'WebcontController@aftosWebcont');

    Route::group(['prefix' => 'webcont'], function(){
        Route::get('/reports', 'WebcontController@reports')->name('cabinet.webcont.reports');
        Route::post('/get-reports', 'WebcontController@getReports');
        Route::post('get-detail', 'WebcontController@getDetail');
        Route::post('get-stats-today', 'WebcontController@getStatsToday');
        Route::post('get-reports-car', 'WebcontController@getReportsCar');

        # Контейнерный терминал - контролировщики - Начальники смены
        Route::group(['prefix' => 'container-controller', 'middleware' => 'role:kt-controller', 'as' => 'cabinet.webcont.'], function(){
            Route::get('/', 'WebcontController@controller')->name('kt.controller');
            Route::get('/get-container-tasks/{filter_id}', 'WebcontController@getContainerTasks');
            Route::get('/task/{id}/container-logs', 'WebcontController@showTaskContainerLogs2')->name('show.task-container-logs');
            Route::get('/task/{id}/import-logs', 'WebcontController@getContainerTaskLogs');
            Route::post('task/reject-cancel-position', 'WebcontController@rejectCancelPosition');
            Route::post('task/confirm-cancel-position', 'WebcontController@confirmCancelPosition');
            Route::post('task/reject-edit-position', 'WebcontController@rejectEditPosition');
            Route::post('task/confirm-edit-position', 'WebcontController@confirmEditPosition');
            Route::get('get-schedules', 'WebcontController@getSchedule'); // получить список расписание крановщиков и стропальщиков
            Route::get('get-crane-users', 'WebcontController@getCraneUsers'); // получить список крановщиков которые нет в расписание
            Route::get('get-slinger-users','WebcontController@getSlingerUsers'); // получить список стропальщиков которые свободные
            Route::get('get-techniques', 'WebcontController@getTechniques'); // список свободных техники

            # Учет техники
            Route::get('/get-technique-tasks', 'TechniqueController@getTechniqueTasks');
        });

        # Учет техники: контроллер
        Route::group(['prefix' => 'technique-controller', 'middleware' => 'role:technique-controller', 'as' => 'cabinet.webcont.'], function(){
            Route::get('/', 'WebcontController@techniqueController')->name('technique.controller');
            Route::post('/get-information-by-qr-code', 'WebcontController@getInformationByQRCode');
        });

        # Груз: Кладовщик
        Route::group(['prefix' => 'cargo-controller', 'middleware' => 'role:cargo-controller', 'as' => 'cabinet.webcont.'], function(){
            Route::get('/', 'CargoKeeperController@controller')->name('cargo.controller');
            Route::get('get-cargo-tasks', 'CargoKeeperController@getCargoTasks');
            Route::get('cargo-task/{id}/show', 'CargoKeeperController@showCargoTask');
            Route::get('cargo-task/{id}-{pos}/start', 'CargoKeeperController@startCargoTask');
            Route::get('cargo-task/{id}-{pos}/edit', 'CargoKeeperController@editCargoTask');
            Route::get('get-techniques', 'CargoKeeperController@getTechniques');
            Route::get('get-employees', 'CargoKeeperController@getEmployees');
            Route::get('get-areas', 'CargoKeeperController@getAreas');
            Route::post('cargo/fix-operations', 'CargoKeeperController@fixOperations');
            Route::post('cargo/tasks/changes', 'CargoKeeperController@changeTasks');
            Route::get('get-cargo-services', 'CargoKeeperController@getCargoServices');
        });
    });

    # WMS
    Route::group(['prefix' => 'wms'], function(){
        Route::get('orders', 'WmsController@orders')->name('cabinet.wms.orders');
        Route::get('boxes', 'WmsController@boxes')->name('cabinet.wms.boxes');
        Route::get('estore', 'WmsController@estore')->name('cabinet.wms.estore');
        Route::get('resend-receive', 'WmsController@resendReceive')->name('cabinet.wms.resend');
        Route::get('/resend-receive/{type}/{receiptID}', 'WmsController@resendUpdate')->name('cabinet.wms.resendUpdated');
        Route::get('/resend-receive-ackans/{receiptID}', 'WmsController@resendAckanUpdate')->name('cabinet.wms.ackansUpdated');
        Route::get('/resend-ship-ackans/{OrderID}', 'WmsController@resendShipAckanUpdate')->name('cabinet.wms.shipAckansUpdated');
        Route::get('/resend-ship/{type}/{shipID}', 'WmsController@resendShip')->name('cabinet.wms.resendShip');
        Route::get('/resend-return-grinfo/{receiptID}', 'WmsController@resendReturnGrin')->name('cabinet.wms.resendReturnGrin');
        Route::get('/resend-return-ackans/{receiptID}', 'WmsController@resendReturnAckan')->name('cabinet.wms.resendReturnAckan');
        Route::get('/pallet-sscc', 'WmsController@palletSSCC')->name('cabinet.wms.palletSSCC');
        Route::get('/pallet-sscc/generate', 'WmsController@generatePalletSSCC')->name('cabinet.wms.generatePalletSSCC');
        Route::post('/pallet-sscc/{code}/print', 'WmsController@printPalletSSCC')->name('cabinet.wms.printPalletSSCC');

        # Bosch
        Route::get('bosch/invoices', 'WmsController@boschInvoices')->name('cabinet.wms.boschInvoices');
        Route::post('bosch/invoices', 'WmsController@boschImport')->name('cabinet.wms.boschImport');
        Route::get('bosch/invoice/{id}/print', 'WmsController@boschPrint');
    });

    # Ком. услуги
    Route::group(['prefix' => 'utilities'], function(){
        Route::get('energy', 'UtilityController@energy')->name('cabinet.utilities.energy');
    });

    # Ashana
    Route::group(['prefix' => 'ashana'], function(){
        Route::get('history', 'KitchenController@ashana')->name('cabinet.ashana.index');
        Route::get('talon', 'KitchenController@talon')->name('cabinet.ashana.talon');
        Route::get('reports', 'KitchenController@reports')->name('cabinet.ashana.reports');
        Route::post('get-logs', 'KitchenController@getLogs');
        Route::post('generate-reports', 'KitchenController@generateReports');
        Route::get('exceptions', 'KitchenController@exceptions')->name('cabinet.ashana.exceptions');
        Route::get('exceptions/create', 'KitchenController@exceptionCreate')->name('cabinet.ashana.exception.create');
        Route::post('exceptions/store', 'KitchenController@exceptionStore')->name('cabinet.ashana.exception.store');
        Route::get('exceptions/{id}/delete', 'KitchenController@exceptionDelete')->name('cabinet.ashana.exception.delete');
    });

    # Ячейки
    Route::get('/barcode-for-wms-boxes', 'WmsController@barcodeForWmsBoxes')->name('cabinet.barcodeForWmsBoxes');
    Route::post('/barcode-for-wms-boxes', 'WmsController@barcodeForWmsBoxes2');
    Route::get('/barcode-get-client-boxes/{depID}', 'WmsController@getClientBoxes');
    Route::get('/jti/barcode-command-print', 'WmsController@jtiCommandPrint');

    # Диспетчер (оповещение)
    Route::group(['prefix' => 'dispatcher'], function(){
        Route::get('/', 'DispatcherController@index')->name('cabinet.dispatcher.index');
    });

    # Техники
    Route::group(['prefix' => 'technique'], function(){
        Route::get('/', 'TechniqueController@technique')->name('cabinet.technique.index');
    });

    # Сервисы
    Route::group(['prefix' => 'services'], function(){
        Route::get('/', 'ServiceController@index')->name('cabinet.service.index');
        Route::get('/create', 'ServiceController@create')->name('cabinet.service.create');
        Route::post('store', 'ServiceController@store')->name('cabinet.service.store');
    });
});
