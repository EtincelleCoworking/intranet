<?php

Route::get('/booking/ical/{key}.ics', array('as' => 'booking_ical', 'uses' => 'BookingController@ical'));

Route::group(['before' => 'member'], function () {
    Route::get('/booking', array('as' => 'booking', 'uses' => 'BookingController@index'));
    Route::get('/booking/events', array('as' => 'booking_list_ajax', 'uses' => 'BookingController@listAjax'));
    Route::get('/booking/list', array('as' => 'booking_list', 'uses' => 'BookingController@raw'));
    Route::post('/booking/list', array('as' => 'booking_filter', 'uses' => 'BookingController@raw'));
    Route::post('/booking/create', array('as' => 'booking_create', 'uses' => 'BookingController@create'));
    Route::get('/booking/delete/{id}', array('as' => 'booking_delete', 'uses' => 'BookingController@delete'));
    Route::post('/booking/delete', array('as' => 'booking_delete_ajax', 'uses' => 'BookingController@deleteAjax'));
    Route::post('/booking/update', array('as' => 'booking_ajax_update', 'uses' => 'BookingController@updateAjax'));
    Route::get('/booking/filter_reset', array('as' => 'booking_filter_reset', 'uses' => 'BookingController@cancelFilter'));
});

Route::group(['before' => 'superadmin'], function () {

});
