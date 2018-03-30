<?php

Route::get('/booking/ical/{key}.ics', array('as' => 'booking_ical', 'uses' => 'BookingController@ical'));

// API
Route::get('/api/booking/{booking_item_id}/members', array('as' => 'api_booking_members', 'uses' => 'BookingApiController@members'));
Route::get('/api/booking/{booking_item_id}/register/{user_id?}', array('as' => 'api_booking_register', 'uses' => 'BookingApiController@register'));
Route::get('/api/booking/{booking_item_id}/unregister/{user_id?}', array('as' => 'api_booking_unregister', 'uses' => 'BookingApiController@unregister'));

Route::get('/ressource/status/{id}', array('as' => 'ressource_status', 'uses' => 'BookingController@status'));

Route::group(['before' => 'member'], function () {
    Route::get('/booking', array('as' => 'booking', 'uses' => 'BookingController@index'));
    Route::get('/booking/{now}', array('as' => 'booking_with_date', 'uses' => 'BookingController@index'))->where(array('now' => '[0-9]+-[0-9]+-[0-9]+'));
    Route::get('/booking/events', array('as' => 'booking_list_ajax', 'uses' => 'BookingController@listAjax'));
    Route::get('/booking/list', array('as' => 'booking_list', 'uses' => 'BookingController@raw'));
    Route::post('/booking/list', array('as' => 'booking_filter', 'uses' => 'BookingController@raw'));
    //Route::post('/booking/create', array('as' => 'booking_create', 'uses' => 'BookingController@create'));
    Route::get('/booking/new/{start_at}/{end_at}', array('as' => 'booking_new_full', 'uses' => 'BookingController@create'));
    Route::get('/booking/new/{start_at}', array('as' => 'booking_new', 'uses' => 'BookingController@create'));
    Route::get('/booking/new', array('as' => 'booking_new', 'uses' => 'BookingController@create'));
    Route::get('/booking/modify/{id}', array('as' => 'booking_modify', 'uses' => 'BookingController@modify'));
    Route::post('/booking/modify/{id}', array('as' => 'booking_modify_check', 'uses' => 'BookingController@modify_check'))->where(array('id' => '[0-9]+'));
    Route::post('/booking/modify', array('as' => 'booking_modify_check_new', 'uses' => 'BookingController@modify_check'))->where(array('id' => '[0-9]+'));
    Route::get('/booking/delete/{id}', array('as' => 'booking_delete', 'uses' => 'BookingController@delete'));
    Route::get('/booking/show/{id}', array('as' => 'booking_item_show', 'uses' => 'BookingController@show'));
    Route::post('/booking/delete', array('as' => 'booking_delete_ajax', 'uses' => 'BookingController@deleteAjax'));
    Route::post('/booking/update', array('as' => 'booking_ajax_update', 'uses' => 'BookingController@updateAjax'));
    Route::get('/booking/filter_reset', array('as' => 'booking_filter_reset', 'uses' => 'BookingController@cancelFilter'));
    Route::get('/booking/confirm/{id}', array('as' => 'booking_confirm', 'uses' => 'BookingController@confirm'));

    Route::get('/booking/invoicing', array('as' => 'booking_invoicing', 'uses' => 'BookingOrderController@invoicing'));

    Route::get('/booking/quote/{booking_item_id}', array('as' => 'booking_quote', 'uses' => 'BookingController@createQuoteFromBookingItem'));
});


Route::group(['before' => 'superadmin'], function () {
    Route::post('/booking/global-action', array('as' => 'booking_global_action', 'uses' => 'BookingController@globalAction'));

    Route::get('/booking/log-time/{id}', array('as' => 'booking_log_time_ajax', 'uses' => 'BookingController@logTimeAjax'));
    Route::get('/booking/make-gift/{id}', array('as' => 'booking_make_gift', 'uses' => 'BookingController@makeGift'));
    Route::get('/booking/daily-pdf/{location}/{day?}', array('as' => 'booking_daily_pdf', 'uses' => 'BookingController@dailyPdf'));
    Route::get('/booking/daily-pdf-wifi/{location}/{day?}', array('as' => 'booking_daily_pdf_wifi', 'uses' => 'BookingController@dailyPdfWifi'));

});
