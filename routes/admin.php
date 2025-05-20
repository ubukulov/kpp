<?php
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'namespace' => 'Admin'/*, 'middleware' => ['throttle:limit_admin']*/], function(){
    Route::get('/login', 'AuthController@login')->name('admin.login');
    Route::post('/authenticate', 'AuthController@authenticate')->name('admin.authenticate');
    Route::get('/logout', 'AuthController@logout')->name('admin.logout');

    Route::group(['middleware' => 'admin'], function(){
        Route::get('/', 'AdminController@dashboard')->name('admin.dashboard');
        Route::get('/dashboard', 'AdminController@dashboard')->name('admin.dashboard');

        # Admin Catalog routes
        Route::group(['prefix' => 'catalog'], function(){
            # Position's routes
            Route::resource('/position', 'PositionController');

            # Company's routes
            Route::resource('/company', 'CompanyController');

            # Department's routes
            Route::resource('/department', 'DepartmentController');

            # Роль
            Route::resource('/role', 'RoleController');

            # Разрешение
            Route::resource('/permission', 'PermissionController');

            # Container Address
            Route::resource('/container-address', 'ContainerAddressController', ['as' => 'admin']);

            # Container
            Route::resource('/containers', 'ContainerController', ['as' => 'admin']);

            # Technique
            Route::resource('/technique', 'TechniqueController', ['as' => 'admin']);

            # Printers
            Route::resource('/printer', 'PrinterController', ['as' => 'admin']);

            # Technique
            Route::resource('tech-type', 'TechniqueTypeController', ['as' => 'admin']);
            Route::resource('tech-place', 'TechniquePlaceController', ['as' => 'admin']);
        });

        # Employee's routes
        Route::resource('/employee', 'EmployeeController');
        Route::get('/employee/{id}/badge', 'EmployeeController@badge')->name('admin.employee.badge');
        Route::get('/employee/badges/{ids}', 'EmployeeController@badges');

        # Driver's routes
        Route::get('/drivers', 'DriverController@index')->name('admin.drivers.index');

        # Reports
        Route::post('/get/permits', 'ReportController@getPermits');
        Route::group(['prefix' => 'reports'], function(){
            Route::get('/', 'ReportController@index')->name('admin.reports.index');
            Route::get('/statistics', 'ReportController@statistics')->name('admin.reports.statistics');
            Route::get('/users/download', 'ReportController@downloadUser')->name('admin.reports.download.users');
        });

        # Рассылка по WhatsApp
        //Route::get('/sending-by-whatsapp', 'AdminController@getSendByWhatsApp')->name('admin.whatsapp.index');
        //Route::post('/sending-by-whatsapp', 'AdminController@sendByWhatsApp')->name('admin.whatsapp.send');

        # Permits
        Route::resource('/permits', 'PermitController', ['as' => 'admin']);

        # White Cars list
        Route::resource('/white-car-list', 'WhiteCarController', ['as' => 'admin']);
        Route::get('/white-car-list/report/wcl-changes', 'WhiteCarController@getWCLReports')->name('admin.white-car-list.reports');
        Route::get('/white-car-list/{company_id}/get-changes', 'WhiteCarController@getWCLChanges');
        Route::get('/white-car-list/import/form', 'WhiteCarController@importForm')->name('admin.wcl.importForm');
        Route::post('/white-car-list/import-execute', 'WhiteCarController@importExecute');
        Route::get('white-car-list/guest/cars', 'WhiteCarController@guestCars')->name('admin.white-cars.guest.index');
        Route::get('white-car-list/guest/cars/create', 'WhiteCarController@guestCreate')->name('admin.white-cars.guest.create');
        Route::post('white-car-list/guest/cars/store', 'WhiteCarController@guestStore')->name('admin.white-cars.guest.store');

        # Dashboard statistics
        Route::get('/get-operations-crane-operator-for-today', 'StatController@getOperationCraneOperatorForToday');

        # WEBCONT
        Route::group(['prefix' => 'webcont'], function(){
            Route::get('/stocks', 'WebcontController@stocks')->name('admin.webcont.stocks');
            Route::get('/logs', 'WebcontController@logs')->name('admin.webcont.logs');
            Route::get('/get/logs', 'WebcontController@getLogsForAdmin');
            Route::post('/search', 'WebcontController@search');
            Route::get('reports', 'WebcontController@reports')->name('admin.webcont.reports');
            Route::post('get-reports', 'WebcontController@getReports');
            Route::post('get-detail', 'WebcontController@getDetail');
            Route::post('get-stats-today', 'WebcontController@getStatsToday');
        });

        # Авторизация по ид пользователя
        Route::get('/employee/{id}/auth', 'AdminController@authByEmployee')->name('admin.employee.auth');

        # Диспетчер (оповещение) админская часть
        Route::group(['prefix' => 'dispatcher'], function(){
            Route::get('/list', 'DispatcherController@list')->name('admin.dispatcher.list');
            Route::get('/list/create', 'DispatcherController@listCreate')->name('admin.dispatcher.list.create');
            Route::post('/list/store', 'DispatcherController@listStore')->name('admin.dispatcher.list.store');
            Route::resource('/alerts', 'DispatcherController', ['as' => 'admin.dispatcher']);
        });

        # CKUD
        Route::group(['prefix' => 'ckud'], function(){
            Route::get('/form', 'CKUDController@index')->name('admin.ckud.index');
            Route::get('/{company_id}/get-users', 'CKUDController@getUsers');
        });

        # Асхана
        Route::group(['prefix' => 'ashana'], function(){
            Route::get('/form', 'KitchenController@index')->name('admin.ashana.index');
            Route::get('/{company_id}/get-employees', 'KitchenController@getEmployees');
            Route::post('change-ashana-logs', 'KitchenController@changeAshanaLogs');
        });

        # Mark
        Route::get('/mark/info', 'AdminController@mark');
    });
});
