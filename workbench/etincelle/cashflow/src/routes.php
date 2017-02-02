<?php

Route::group(['before' => 'member'], function () {
});

Route::group(['before' => 'superadmin'], function () {
    Route::get('/cashflow', array('as' => 'cashflow', 'uses' => 'CashflowController@index'));
    Route::get('/cashflow/{id}/delete', array('as' => 'cashflow_delete', 'uses' => 'CashflowController@delete'));
    Route::get('/cashflow/{id}/refresh', array('as' => 'cashflow_refresh', 'uses' => 'CashflowController@refresh'));

    Route::get('/cashflow/{account_id}/add', array('as' => 'cashflow_add', 'uses' => 'OperationController@add'));
    Route::post('/cashflow/{account_id}/add', array('as' => 'cashflow_add_check', 'uses' => 'OperationController@add_check'));
    Route::get('/cashflow/modify/{id}', array('as' => 'cashflow_modify', 'uses' => 'OperationController@modify'))->where(array('id' => '[0-9]+'));
    Route::post('/cashflow/modify/{id}', array('as' => 'cashflow_modify_check', 'uses' => 'OperationController@modify_check'))->where(array('id' => '[0-9]+'));
});
