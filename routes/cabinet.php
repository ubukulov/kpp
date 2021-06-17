<?php

use Illuminate\Support\Facades\Route;

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
    Route::get('/reports', 'ReportController@index')->name('cabinet.report.index');
    Route::post('/reports/download-report', 'ReportController@downloadReport');

    # КПП для Самсунга
    Route::get('/kpp/samsung', 'CabinetController@kpp_samsung')->name('cabinet.kpp.samsung');
    Route::get('/kpp/samsung/get100logs', 'CabinetController@get100Logs');
    Route::post('/kpp/samsung/get-document-by-code', 'CabinetController@getDocumentByCode');
});
