<?php

use Illuminate\Support\Facades\Route;

# Кабинет клиента
Route::group(['prefix' => 'cabinet', 'middleware' => ['auth'], 'namespace' => 'Cabinet'], function(){
    Route::get('/', 'CabinetController@cabinet')->name('cabinet');
    Route::resource('/employees', 'EmployeeController', ['as' => 'cabinet']);
    Route::resource('/permits', 'PermitController', ['as' => 'cabinet']);
    Route::get('/services', 'ServiceController@index')->name('cabinet.service.index');
    Route::get('/reports', 'ReportController@index')->name('cabinet.report.index');
    Route::post('/get/permits', 'PermitController@getPermits');
    Route::get('/employees/{id}/badge', 'EmployeeController@badge')->name('employee.badge');
});
