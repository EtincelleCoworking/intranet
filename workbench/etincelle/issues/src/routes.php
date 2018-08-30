<?php

//Route::get('/issues/ical/{key}.ics', array('as' => 'issues_ical', 'uses' => 'IssueController@ical'));
//
//// API
//Route::get('/api/issues/{issues_item_id}/members', array('as' => 'api_issues_members', 'uses' => 'issuesApiController@members'));
//Route::get('/api/issues/{issues_item_id}/register/{user_id?}', array('as' => 'api_issues_register', 'uses' => 'issuesApiController@register'));
//Route::get('/api/issues/{issues_item_id}/unregister/{user_id?}', array('as' => 'api_issues_unregister', 'uses' => 'issuesApiController@unregister'));
//


Route::group(['before' => 'member'], function () {
});

Route::group(['before' => 'superadmin'], function () {
    Route::get('/issues', array('as' => 'issues', 'uses' => 'IssueController@index'));
    Route::get('/issues/create', array('as' => 'issue_create', 'uses' => 'IssueController@create'));
//    Route::post('/issues/list', array('as' => 'issues_filter', 'uses' => 'IssueController@raw'));
    Route::post('/issues/create', array('as' => 'issues_create', 'uses' => 'IssueController@modify_check'));
    Route::get('/issues/modify/{id}', array('as' => 'issues_modify', 'uses' => 'IssueController@modify'));
    Route::post('/issues/modify/{id}', array('as' => 'issues_modify_check', 'uses' => 'IssueController@modify_check'))->where(array('id' => '[0-9]+'));
    Route::get('/issues/delete/{id}', array('as' => 'issues_delete', 'uses' => 'IssueController@delete'));
    Route::get('/issues/{id}', array('as' => 'issues_item_show', 'uses' => 'IssueController@show'))->where(array('id' => '[0-9]+'));
//    Route::get('/issues/filter_reset', array('as' => 'issues_filter_reset', 'uses' => 'IssueController@cancelFilter'));

});
