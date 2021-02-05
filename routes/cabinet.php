<?php

use Illuminate\Support\Facades\Route;

# Кабинет клиента
Route::group(['prefix' => 'cabinet', 'namespace' => 'Cabinet'], function(){
    Route::get('/login', 'AuthController@login')->name('cabinet.login');
    Route::post('/login', 'AuthController@authenticate')->name('cabinet.authenticate');
    Route::get('/forget-password', 'AuthController@forgetPassword')->name('cab.forget-password');
    Route::get('/logout', 'AuthController@logout')->name('cabinet.logout');

    Route::group(['middleware' => ['cabinet']], function(){
        Route::get('/', 'CabinetController@cabinet')->name('cabinet');
        Route::resource('/employees', 'EmployeeController', ['as' => 'cabinet']);
        Route::resource('/permits', 'PermitController', ['as' => 'cabinet']);
        Route::get('/services', 'ServiceController@index')->name('cabinet.service.index');
        Route::get('/reports', 'ReportController@index')->name('cabinet.report.index');
        Route::post('/get/permits', 'PermitController@getPermits');
    });
});
