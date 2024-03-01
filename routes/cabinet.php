<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Cabinet\ReportController;

# Кабинет клиента
Route::group(['prefix' => 'cabinet', 'middleware' => ['auth'], 'namespace' => 'Cabinet'], function(){
    Route::get('/', 'CabinetController@cabinet')->name('cabinet');
    Route::resource('/employees', 'EmployeeController', ['as' => 'cabinet']);
    Route::resource('/permits', 'PermitController', ['as' => 'cabinet']);
    Route::get('/services', 'ServiceController@index')->name('cabinet.service.index');

    Route::post('/get/permits', 'PermitController@getPermits');
    Route::get('/employees/{id}/badge', 'EmployeeController@badge')->name('employee.badge');
    Route::get('/employees/badges/{ids}', 'EmployeeController@badges');

    # Отчеты по машинам
    Route::get('/reports', [ReportController::class, 'index'])->name('cabinet.report.index');
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
    Route::post('/get/permits-customs', 'PermitController@getPermitsCustoms');

    # White Cars list
    Route::resource('/white-car-list', 'WhiteCarController', ['as' => 'cabinet']);
    Route::get('/white-car-list/{id}/destroy', 'WhiteCarController@destroy')->name('cabinet.wcl.destroy');

    # Position's routes
    Route::resource('/position', 'PositionController', ['as' => 'cabinet']);

    # Department's routes
    Route::resource('/department', 'DepartmentController', ['as' => 'cabinet']);

    # WEBCONT
    Route::get('webcont', 'WebcontController@index')->name('cabinet.webcont.index');
    Route::get('webcont/{id}/show', 'WebcontController@show')->name('cabinet.webcont.show');
    Route::get('webcont/aftos', 'WebcontController@aftosWebcont')->name('cabinet.webcont.aftos');
    Route::post('webcont/aftos/search', 'WebcontController@aftosWebcontSearch');

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

    # Ashana
    Route::get('ashana', 'KitchenController@ashana')->name('cabinet.ashana.index');
    Route::get('/talon', 'KitchenController@talon')->name('cabinet.ashana.talon');
    Route::get('/ashana/reports', 'KitchenController@reports')->name('cabinet.ashana.reports');
    Route::post('ashana/get-logs', 'KitchenController@getLogs');
    Route::post('ashana/generate-reports', 'KitchenController@generateReports');

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
});
