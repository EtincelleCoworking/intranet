<?php

Route::group(['before' => 'member'], function () {
});

Route::group(['before' => 'superadmin'], function () {
    Route::get('/cashflow', array('as' => 'cashflow', 'uses' => 'CashflowController@index'));
    Route::get('/cashflow/graph', array('as' => 'cashflow_graph', 'uses' => 'CashflowController@graph'));
    Route::get('/cashflow/{account_id}/delete/{id}', array('as' => 'cashflow_operation_delete', 'uses' => 'CashflowController@delete'));
    Route::get('/cashflow/{account_id}/refresh/{id}', array('as' => 'cashflow_operation_refresh', 'uses' => 'CashflowController@refresh'));
    Route::get('/cashflow/{account_id}/archive/{id}', array('as' => 'cashflow_operation_archive', 'uses' => 'CashflowController@archive'));

    Route::get('/cashflow/{account_id}/add', array('as' => 'cashflow_operation_add', 'uses' => 'OperationController@add'));
    Route::post('/cashflow/{account_id}/add', array('as' => 'cashflow_operation_add_check', 'uses' => 'OperationController@add_check'));
    Route::get('/cashflow/{account_id}/modify/{id}', array('as' => 'cashflow_operation_modify', 'uses' => 'OperationController@modify'))->where(array('id' => '[0-9]+'));
    Route::post('/cashflow/{account_id}/modify/{id}', array('as' => 'cashflow_operation_modify_check', 'uses' => 'OperationController@modify_check'))->where(array('id' => '[0-9]+'));

    Route::post('/cashflow/{account_id}/modify', array('as' => 'cashflow_account_modify_check', 'uses' => 'AccountController@modify_check'))->where(array('account_id' => '[0-9]+'));
});
