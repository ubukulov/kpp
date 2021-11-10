<?php
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function(){
    Route::get('/login', 'AuthController@login')->name('admin.login');
    Route::post('/authenticate', 'AuthController@authenticate')->name('admin.authenticate');
    Route::get('/logout', 'AuthController@logout')->name('admin.logout');

    Route::group(['middleware' => 'admin'], function(){
        Route::get('/', 'AdminController@dashboard')->name('admin.dashboard');
        Route::get('/dashboard', 'AdminController@dashboard')->name('admin.dashboard');

        # Position's routes
        Route::resource('/position', 'PositionController');

        # Company's routes
        Route::resource('/company', 'CompanyController');

        # Department's routes
        Route::resource('/department', 'DepartmentController');

        # Employee's routes
        Route::resource('/employee', 'EmployeeController');
        Route::get('/employee/{id}/badge', 'EmployeeController@badge')->name('admin.employee.badge');
        Route::get('/employee/badges/{ids}', 'EmployeeController@badges');

        # Driver's routes
        Route::get('/drivers', 'DriverController@index')->name('admin.drivers.index');

        # Reports
        Route::get('/reports', 'ReportController@index')->name('admin.reports.index');
        Route::post('/get/permits', 'ReportController@getPermits');
        Route::get('/reports/statistics', 'ReportController@statistics')->name('admin.reports.statistics');

        # Рассылка по WhatsApp
        //Route::get('/sending-by-whatsapp', 'AdminController@getSendByWhatsApp')->name('admin.whatsapp.index');
        //Route::post('/sending-by-whatsapp', 'AdminController@sendByWhatsApp')->name('admin.whatsapp.send');

        # Роль
        Route::resource('/role', 'RoleController');

        # Разрешение
        Route::resource('/permission', 'PermissionController');

        # Container Address
        Route::resource('/container-address', 'ContainerAddressController', ['as' => 'admin']);

        # Container
        Route::resource('/containers', 'ContainerController', ['as' => 'admin']);

        # Permits
        Route::resource('/permits', 'PermitController', ['as' => 'admin']);

        # Technique
        Route::resource('/technique', 'TechniqueController', ['as' => 'admin']);

        # White Cars list
        Route::resource('/white-car-list', 'WhiteCarController', ['as' => 'admin']);

        # Dashboard statistics
        Route::get('/get-operations-crane-operator-for-today', 'StatController@getOperationCraneOperatorForToday');

        # WEBCONT
        Route::group(['prefix' => 'webcont'], function(){
            Route::get('/stocks', 'WebcontController@stocks')->name('admin.webcont.stocks');
            Route::get('/logs', 'WebcontController@logs')->name('admin.webcont.logs');
            Route::get('/get/logs', 'WebcontController@getLogsForAdmin');
            Route::post('/search', 'WebcontController@search');
        });

    });
});
