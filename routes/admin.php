<?php
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function(){
    Route::get('/dashboard', 'AdminController@dashboard')->name('admin.dashboard');

    # Position's routes
    Route::resource('/position', 'PositionController');

    # Company's routes
    Route::resource('/company', 'CompanyController');

    # Employee's routes
    Route::resource('/employee', 'EmployeeController');

    # Driver's routes
    Route::get('/drivers', 'DriverController@index')->name('admin.drivers.index');
});
