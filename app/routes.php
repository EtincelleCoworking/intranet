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
	Route::get('/users/list', array('as' => 'user_list', 'uses' => 'UserController@liste'));
});
