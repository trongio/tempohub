<?php

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

Auth::routes();

Route::get('/', 'PageController@getMonitoring');

Route::get('/room-sensors','PageController@getRoomSensors');
Route::get('/room-live-data','PageController@getRoomLiveData');
Route::get('/room-average-data', 'PageController@getRoomAverageData');
Route::get('/all-sensors-today-data-by-room-id','PageController@getAllSensorsTodayDataByRoomId');
Route::get('/get-sensor-graphycs-data', 'PageController@getSensorPast7DaysData');
Route::get('/get-room-map-data', 'PageController@getRoomLiveDataForMap');

Route::prefix('reports')->group(function () {
	Route::get('/', 'PageController@getReports');
	Route::get('/export', 'PageController@getReportsExport');
	Route::get('/user-actions', 'PageController@getReportUserActions');
	Route::get('/alerts', 'PageController@getReportAlerts');
});

Route::get('/settings', 'PageController@getSettings');

Route::get('/about', 'PageController@getAbout');

Route::prefix('administration')->group(function () {
    Route::get('/', 'PageController@getAdministration');

    //warehouses

    Route::post('/add-new-warehouse','PageController@postAddWarehouse');
    Route::get('/delete-warehouse/{warehouse_id}','PageController@getDeleteWarehouse');
    Route::post('/edit-warehouse','PageController@postEditWarehouse');

    //sensors
    Route::get('/sensors', 'PageController@getAdministrationSensors');

    //warehouse rooms

    Route::get('/warehouse-rooms/{warehouse_id}','PageController@getWarehouseRooms');
    Route::get('/delete-warehouse-room/{roomid}','PageController@getDeleteWarehouseRoom');
    Route::post('/add-new-warehouse-room','PageController@postAddWarehouseRoom');
    Route::post('/edit-warehouse-room','PageController@postEditWarehouseRoom');

    //warehouse room sensors

    Route::get('/warehouse-room-sensors/{roomid}','PageController@getAddWarehouseRoomSensors');
    Route::post('/add-new-warehouse-room-sensor','PageController@postAddWarehouseRoomSensor');
    Route::post('/add-sensor-to-point','PageController@postAddSensorToPoint');
    Route::get('/delete-warehouse-room-sensor/{sensorid}','PageController@getDeleteWarehouseRoomSensor');
    Route::get('/unlink-warehouse-room-sensor/{sensorid}','PageController@getUnlinkWarehouseRoomSensor');
    Route::post('/edit-warehouse-room-sensor','PageController@postEditWarehouseRoomSensor');
    Route::post('/device-sensors','PageController@getDeviceSensorsOrders');
    Route::get('/controller-sensors', 'PageController@getControllerSensors');
    Route::get('/room-points', 'PageController@getRoomPoints');
    
    //alerts
    Route::get('/get-controllers', 'PageController@getControllersByWarehouse');
    Route::get('/get-sensors', 'PageController@getSensorsByControllers');

    Route::get('/alerts', 'PageController@getAdministrationAlerts');
    Route::get('/alert-device-sensors', 'AlertController@getAlertDeviceSensors');
    Route::post('/add_alert', 'AlertController@postAddNewAlert');
    Route::post('/edit_alert', 'AlertController@postEditAlert');
    Route::get('/delete-alert/{alert_id}','AlertController@getDeleteAlert');
    
});

Route::prefix('ajaxes')->group(function () {
    Route::get('/warehouse-rooms','PageController@getRooms');
    Route::get('/warehouse-room-sensors','PageController@getSensors');
});

Route::get('/alert-read-status-change', 'AlertController@getChangeAlertStatus');

Route::get('/recived-alerts', 'AlertController@getRecivedAlerts');

Route::get('/logout', 'UserController@getLogout');

Route::post('/user-profile', 'UserController@updateUserProfile');
Route::post('/update-password', 'UserController@postUpdatePassword');
Route::post('/user-settings', 'UserController@postUpdateUserSettings');

Route::get('/home', 'PageController@index')->name('home')->middleware('auth');