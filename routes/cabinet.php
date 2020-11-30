<?php

use Illuminate\Support\Facades\Route;

# Кабинет клиента

Route::group([/*'middleware' => 'auth',*/'prefix' => 'cabinet', 'namespace' => 'Cabinet'], function(){
    Route::get('/login', 'AuthController@login')->name('cabinet.login');
    Route::post('/login', 'AuthController@authenticate')->name('cabinet.authenticate');
    Route::get('/forget-password', 'AuthController@forgetPassword')->name('cab.forget-password');
    Route::get('/', 'CabinetController@cabinet')->name('cabinet');
    Route::resource('/employees', 'EmployeeController');
    Route::get('/permits', 'CabinetController@permits_list')->name('cabinet.permits.list');
});
