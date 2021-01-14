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

Route::get('/', function () {
	return response()->json( [
		"message" => "IRS"
	] );
});

Route::get('/phpinfo', function () {
	phpinfo();
});

Route::post('/login', 'AuthController@login');
Route::get('/export', 'ExportController@index');

### IRS - tr_hv_production_daily (mb Juki - 180219)
Route::get('/tr_hv_production_daily', 'Tr_hv_production_dailyController@index')->name('tr_hv_production_daily');
//Route::get('/tr_hv_production_daily_method2', 'Tr_hv_production_dailyController@method_2')->name('tr_hv_production_daily_method2/');
Route::get('/sqvi', 'SqviController@cron')->name('sqvi');
Route::get('/sqvi_date/{ddmmyyyy}', 'Sqvi_dateController@index')->name('sqvi_date/{ddmmyyyy}');
Route::get('/sqvi/json', 'SqviController@json')->name('sqvi/json');
Route::get('/crop_harvest/{comp_ba}', 'Crop_harvestController@cron')->name('crop_harvest/{comp_ba}');
Route::get('/crop_harvest_date/{ddmmyyyy}/{comp_ba}', 'Crop_harvest_dateController@index')->name('crop_harvest_date/{ddmmyyyy}/{comp_ba}');
Route::get('crop_harvest/json/{comp_ba}', 'Crop_harvestController@json')->name('crop_harvest/json/{comp_ba}');
Route::get('/zpay_view_rawat/{comp_ba}', 'Zpay_view_rawatController@cron')->name('zpay_view_rawat/{comp_ba}');
Route::get('/zpay_view_rawat_date/{ddmmyyyy}/{comp_ba}', 'Zpay_view_rawat_dateController@index')->name('zpay_view_rawat_date/{ddmmyyyy}/{comp_ba}');
Route::get('/zpay_view_rawat/json/{comp_ba}', 'Zpay_view_rawatController@json')->name('zpay_view_rawat/json/{comp_ba}');
Route::get('/employee/{comp_ba}', 'EmployeeController@cron')->name('employee/{comp_ba}');
Route::get('/employee/json/{comp_ba}', 'EmployeeController@json')->name('employee/json/{comp_ba}');
Route::get('/vra/json/{comp_ba}', 'VRAController@json')->name('vra/json/{comp_ba}');
Route::get('/test', 'TestController@cron')->name('test');
Route::get('/getToken', 'GetTokenController@index');

Route::get('/getEmployee/{comp_ba}', 'EmployeeController@getEmployee')->name('getEmployee/{comp_ba}');
Route::get('/getZpayviewrawat/{comp_ba}', 'Zpay_view_rawatController@getZpayviewrawat')->name('getZpayviewrawat/{comp_ba}');
Route::get('/getSqvi', 'SqviController@getSqvi')->name('getSqvi');
Route::get('/getCropharvest/{comp_ba}', 'Crop_harvestController@getCropharvest')->name('getCropharvest/{comp_ba}');
Route::get('/getVRA/{comp_ba}', 'VRAController@getVRA')->name('getVRA/{comp_ba}');

### END IRS - tr_hv_production_daily (mb Juki - 180219)

### Finger Site
Route::get('/fingersite', 'FingerSiteController@cron')->name('fingersite');
### END Finger Site