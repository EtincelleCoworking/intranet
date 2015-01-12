<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', array('as' => 'dashboard', 'uses' => 'UserController@dashboard'));

Route::get('/login', array('as' => 'user_login', 'uses' => 'UserController@login'));
Route::post('/login_check', array('before' => 'csrf', 'as' => 'user_login_check', 'uses' => 'UserController@login_check'));
Route::get('/logout', array('as' => 'user_logout', 'uses' => 'UserController@logout'));

Route::group(['before' => 'auth'], function() {
	Route::get('/profile', array('as' => 'user_profile', 'uses' => 'UserController@profile'));
	Route::get('/users', array('as' => 'user_list', 'uses' => 'UserController@liste'));
	Route::get('/user/add', array('as' => 'user_add', 'uses' => 'UserController@add'));
	Route::post('/user/add', array('as' => 'user_add_check', 'uses' => 'UserController@add_check'));
	Route::get('/user/modify/{id}', array('as' => 'user_modify', 'uses' => 'UserController@modify'))->where(array('id' => '[0-9]+'));
	Route::post('/user/modify/{id}', array('as' => 'user_modify_check', 'uses' => 'UserController@modify_check'))->where(array('id' => '[0-9]+'));

	Route::get('/invoices', array('as' => 'invoice_list', 'uses' => 'InvoiceController@liste'));
	Route::get('/invoice/add', array('as' => 'invoice_add', 'uses' => 'InvoiceController@add'));
	Route::post('/invoice/add', array('as' => 'invoice_add_check', 'uses' => 'InvoiceController@add_check'));
	Route::get('/invoice/modify/{id}', array('as' => 'invoice_modify', 'uses' => 'InvoiceController@modify'))->where(array('id' => '[0-9]+'));
	Route::post('/invoice/modify/{id}', array('as' => 'invoice_modify_check', 'uses' => 'InvoiceController@modify_check'))->where(array('id' => '[0-9]+'));
});
