<?php

// API
Route::post('/api/sensor/{location_slug}/{sensor_slug}', array('as' => 'api_sensor_log', 'uses' => 'SensorApiController@log'));
Route::get('/api/sensor/{location_slug}/{sensor_slug}', array('as' => 'api_sensor_view', 'uses' => 'SensorApiController@view'));

Route::group(['before' => 'superadmin'], function () {

});
