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

Route::get('/api/test', array('as' => 'api_test', 'uses' => 'ApiController@test'));

Route::get('/api/1.0/location/{location_slug}/{key}', array('as' => 'api_location_update', 'uses' => 'ApiController@updateLocationIp'));
//Route::get('/api/1.0/metric/{location_slug}/{key}/{metric_slug}/{metric_value}', array('as' => 'api_metric_update', 'uses' => 'ApiController@updateMetric'));
Route::post('/api/1.0/offix/{location_slug}/{key}', array('as' => 'api_offix', 'uses' => 'ApiController@offixUpload'));
Route::get('/api/1.0/offix/{secure_key}', array('as' => 'api_offix', 'uses' => 'ApiController@offixDownload'));
Route::get('/api/1.0/user/{secure_key}/{email}', array('as' => 'api_user', 'uses' => 'ApiController@user'));

Route::get('/api/1.0/monitoring/{location_slug}/{key}/agents', array('as' => 'monitoring_agents', 'uses' => 'MonitoringController@agents'));
Route::post('/api/1.0/monitoring/{location_slug}/{key}/agents', array('as' => 'monitoring_feedback', 'uses' => 'MonitoringController@feedback'));

Route::get('/login', array('as' => 'user_login', 'uses' => 'UserController@login'));
Route::post('/login_check', array('before' => 'csrf', 'as' => 'user_login_check', 'uses' => 'UserController@login_check'));
Route::get('/logout', array('as' => 'user_logout', 'uses' => 'UserController@logout'));
Route::controller('password', 'RemindersController');

Route::get('/phonebox/{location_slug}/{key}/{box_id}', array('as' => 'phonebox_index', 'uses' => 'PhoneBoxController@index'));
Route::post('/phonebox/{location_slug}/{key}/{box_id}/auth', array('as' => 'phonebox_auth', 'uses' => 'PhoneBoxController@auth'));
Route::post('/phonebox/{location_slug}/{key}/{box_id}/update', array('as' => 'phonebox_update', 'uses' => 'PhoneBoxController@update'));
Route::post('/phonebox/{location_slug}/{key}/{box_id}/stop', array('as' => 'phonebox_stop', 'uses' => 'PhoneBoxController@stop'));

Route::group(['before' => 'member'], function() {
    Route::get('/profile/{id}', array('as' => 'user_profile', 'uses' => 'UserController@profile'))->where(array('id' => '[0-9]+'));
//    Route::get('/users/directory', array('as' => 'user_directory', 'uses' => 'UserController@directory'));
    Route::get('/profile/edit', array('as' => 'user_edit', 'uses' => 'UserController@edit'));
//    Route::post('/profile/edit', array('as' => 'user_edit', 'uses' => 'UserController@edit_check'));
    Route::get('/user/modify/{id}', array('as' => 'user_modify', 'uses' => 'UserController@modify'))->where(array('id' => '[0-9]+'));
    Route::post('/user/modify/{id}', array('as' => 'user_modify_check', 'uses' => 'UserController@modify_check'))->where(array('id' => '[0-9]+'));

    Route::get('/user/export-profile/{id}', array('as' => 'user_export_profile', 'uses' => 'UserController@exportMemberProfile'))->where(array('id' => '[0-9]+'));

    Route::get('/user/change-location', array('as' => 'user_change_location', 'uses' => 'UserController@ChangeLocation'));
    Route::get('/user/birthday', array('as' => 'user_birthday', 'uses' => 'UserController@birthday'));

	Route::get('/users', array('as' => 'members', 'uses' => 'UserController@members'));

    Route::get('/user/affiliate/{id?}', array('as' => 'user_affiliate', 'uses' => 'UserController@affiliate'));

    Route::get('/organisation/{id}/usage/{period?}', array('as' => 'organisation_usage', 'uses' => 'OrganisationController@usage'))->where(array('id' => '[0-9]+', 'period' => '^[0-9]{4}-[0-9]{2}$'));


    Route::any('/pasttimes', array('as' => 'pasttime_list', 'uses' => 'PastTimeController@liste'));
    Route::get('/pasttimes/{month}', array('as' => 'pasttime_list_month', 'uses' => 'PastTimeController@liste'))->where(array('month' => '[0-9]{2}'));
    Route::get('/pasttime/add', array('as' => 'pasttime_add', 'uses' => 'PastTimeController@add'));
    Route::post('/pasttime/add', array('as' => 'pasttime_add_check', 'uses' => 'PastTimeController@add_check'));
    Route::get('/pasttime/modify/{id}', array('as' => 'pasttime_modify', 'uses' => 'PastTimeController@modify'))->where(array('id' => '[0-9]+'));
    Route::post('/pasttime/modify/{id}', array('as' => 'pasttime_modify_check', 'uses' => 'PastTimeController@modify_check'))->where(array('id' => '[0-9]+'));
    Route::get('/pasttime/reset-filter', array('as' => 'pasttime_filter_reset', 'uses' => 'PastTimeController@cancelFilter'));
    Route::get('/pasttime/confirm/{id}', array('as' => 'pasttime_confirm', 'uses' => 'PastTimeController@confirm'));
    Route::post('/pasttime/confirm-multiple', array('as' => 'pasttime_confirm_multiple', 'uses' => 'PastTimeController@confirmMultiple'));
    Route::post('/pasttime/global-action', array('as' => 'pasttime_global_action', 'uses' => 'PastTimeController@globalAction'));

    Route::get('/invoices', array('as' => 'invoice_list', 'uses' => 'InvoiceController@invoiceList'));
    Route::get('/invoices/reset-filter', array('as' => 'invoice_filter_reset', 'uses' => 'InvoiceController@cancelFilter'));
    Route::post('/invoices', array('as' => 'invoice_list', 'uses' => 'InvoiceController@invoiceList'));
    Route::get('/quotes/{filtre}', array('as' => 'quote_list', 'uses' => 'InvoiceController@quoteList'));
    Route::get('/invoice/{id}/print/pdf', array('as' => 'invoice_print_pdf', 'uses' => 'InvoiceController@print_pdf'))->where(array('id' => '[0-9]+'));
    Route::post('/invoice/stripe/{id}', array('as' => 'invoice_stripe', 'uses' => 'InvoiceController@stripe'))->where(array('id' => '[0-9]+'));

    Route::post('/wall/add', array('as' => 'wall_add_check', 'uses' => 'WallPostController@add_check'));
    Route::post('/wall/reply', array('as' => 'wall_reply', 'uses' => 'WallPostController@reply'));
    Route::get('/wall/page/{page_index}', array('as' => 'wall_page', 'uses' => 'WallPostController@page'));

    Route::get('/checkin/start', array('as' => 'checkin_start', 'uses' => 'CheckinController@start'));
    Route::get('/checkin/stop', array('as' => 'checkin_stop', 'uses' => 'CheckinController@stop'));
    Route::get('/checkin/status', array('as' => 'checkin_status', 'uses' => 'CheckinController@status'));

    Route::get('/user/refresh_personnal_code', array('as' => 'user_refresh_personnal_code', 'uses' => 'UserController@refreshPersonnalCode'));


    Route::get('/subscription', array('as' => 'subscription_manage', 'uses' => 'SubscriptionController@manage'));
    Route::post('/subscription', array('as' => 'subscription_manage', 'uses' => 'SubscriptionController@manage_post'));
//    Route::get('/subscription/cancel', array('as' => 'subscription_cancel', 'uses' => 'SubscriptionController@cancel'));
//    Route::get('/subscription/add', array('as' => 'subscription_add', 'uses' => 'SubscriptionController@add'));
//    Route::post('/subscription/add', array('as' => 'subscription_add_check', 'uses' => 'SubscriptionController@add_check'));
//    Route::get('/subscription/modify/{id}', array('as' => 'subscription_modify', 'uses' => 'SubscriptionController@modify'));
//    Route::post('/subscription/modify/{id}', array('as' => 'subscription_modify_check', 'uses' => 'SubscriptionController@modify_check'));
//    Route::get('/subscription/renew/{id}', array('as' => 'subscription_renew', 'uses' => 'SubscriptionController@renew'));

});

Route::group(['before' => 'superadmin'], function() {
    Route::get('/admin/{target_period?}', array('as' => 'admin_dashboard', 'uses' => 'DashboardController@admin'))->where(array('target_period' => '^[0-9]{4}-[0-9]{2}$'));
    //Route::get('/operating', array('as' => 'operating', 'uses' => 'OperatingController@index'));

    Route::get('/planning/populate', array('as' => 'planning_populate', 'uses' => 'TeamPlanningController@populate'));
    Route::get('/planning', array('as' => 'planning', 'uses' => 'TeamPlanningController@index'));
    Route::get('/planning/add', array('as' => 'planning_add', 'uses' => 'TeamPlanningController@add'));
    Route::get('/planning/modify/{id}', array('as' => 'planning_modify', 'uses' => 'TeamPlanningController@modify'))->where(array('id' => '[0-9]+'));
    Route::post('/planning/modify/{id?}', array('as' => 'planning_modify_check', 'uses' => 'TeamPlanningController@modify_check'))->where(array('id' => '[0-9]+'));
    Route::get('/planning/delete/{id}', array('as' => 'planning_delete', 'uses' => 'TeamPlanningController@delete'))->where(array('id' => '[0-9]+'));
    Route::get('/planning/list', array('as' => 'planning_list', 'uses' => 'TeamPlanningController@liste'));

    Route::get('/planning.json', array('as' => 'planning_json', 'uses' => 'TeamPlanningController@json'));

    Route::get('/user/add', array('as' => 'user_add', 'uses' => 'UserController@add'));
    Route::post('/user/add_raw', array('as' => 'user_add_raw', 'uses' => 'UserController@add_raw'));
	Route::get('/user/login-as/{id}', array('as' => 'user_login_as', 'uses' => 'UserController@login_as'));
	Route::post('/user/add', array('as' => 'user_add_check', 'uses' => 'UserController@add_check'));
	Route::get('/user/list', array('as' => 'user_list', 'uses' => 'UserController@liste'));
	Route::post('/user/list', array('as' => 'user_filter', 'uses' => 'UserController@liste'));
	Route::get('/user/reset', array('as' => 'user_filter_reset', 'uses' => 'UserController@cancelFilter'));
	Route::get('/user/slack/{id}', array('as' => 'user_invite_slack', 'uses' => 'UserController@slackInvite'));

    Route::get('/locations', array('as' => 'location_list', 'uses' => 'LocationController@liste'));
    Route::get('/location/modify/{id}', array('as' => 'location_modify', 'uses' => 'LocationController@modify'))->where(array('id' => '[0-9]+'));
    Route::post('/location/modify/{id}', array('as' => 'location_modify_check', 'uses' => 'LocationController@modify_check'))->where(array('id' => '[0-9]+'));
    Route::get('/location/{location_slug}', array('as' => 'location_show', 'uses' => 'LocationController@show'));


    Route::get('/invoice/add/{type}', array('as' => 'invoice_add', 'uses' => 'InvoiceController@add'))->where(array('type' => '[A-Z]{1}'));
	Route::get('/invoice/add/{type}/{organisation}', array('as' => 'invoice_add_organisation', 'uses' => 'InvoiceController@add'))->where(array('type' => '[A-Z]{1}', 'organisation' => '[0-9]+'));
	Route::post('/invoice/add/{type}', array('as' => 'invoice_add_check', 'uses' => 'InvoiceController@add_check'))->where(array('type' => '[A-Z]{1}'));
    Route::get('/invoice/modify/{id}', array('as' => 'invoice_modify', 'uses' => 'InvoiceController@modify'))->where(array('id' => '[0-9]+'));
    Route::post('/invoice/modify/{id}', array('as' => 'invoice_modify_check', 'uses' => 'InvoiceController@modify_check'))->where(array('id' => '[0-9]+'));
	Route::post('/invoice/comment/{id}', array('as' => 'invoice_comment_add', 'uses' => 'InvoiceCommentController@add'))->where(array('id' => '[0-9]+'));
    Route::get('/invoice/validate/{id}', array('as' => 'invoice_validate', 'uses' => 'InvoiceController@validate'))->where(array('id' => '[0-9]+'));
    Route::get('/invoice/canceled/{id}', array('as' => 'invoice_cancel', 'uses' => 'InvoiceController@cancel'))->where(array('id' => '[0-9]+'));
	Route::get('/invoice/delete/{id}', array('as' => 'invoice_delete', 'uses' => 'InvoiceController@delete'))->where(array('id' => '[0-9]+'));
	Route::post('/invoice/{id}/item/modify', array('as' => 'invoice_item_modify', 'uses' => 'InvoiceItemController@modify'))->where(array('id' => '[0-9]+'));
	Route::get('/invoice/{invoice}/item/{id}/delete', array('as' => 'invoice_item_delete', 'uses' => 'InvoiceItemController@delete'))->where(array('invoice' => '[0-9]+', 'id' => '[0-9]+'));
	Route::get('/invoice/send/{id}', array('as' => 'invoice_send', 'uses' => 'InvoiceController@send'))->where(array('id' => '[0-9]+'));
	Route::get('/invoice/unpaid/{space_slug?}', array('as' => 'invoice_unpaid', 'uses' => 'InvoiceController@unpaid'));
	Route::get('/invoice/paid/{id}', array('as' => 'invoice_paid', 'uses' => 'InvoiceController@paid'));
	Route::get('/invoice/invoicing', array('as' => 'invoice_invoicing', 'uses' => 'InvoiceController@invoicing'));

	Route::get('/ressources', array('as' => 'ressource_list', 'uses' => 'RessourceController@liste'));
	Route::get('/ressource/add', array('as' => 'ressource_add', 'uses' => 'RessourceController@add'));
	Route::post('/ressource/add', array('as' => 'ressource_add_check', 'uses' => 'RessourceController@add_check'));
	Route::get('/ressource/modify/{id}', array('as' => 'ressource_modify', 'uses' => 'RessourceController@modify'));
	Route::post('/ressource/modify/{id}', array('as' => 'ressource_modify_check', 'uses' => 'RessourceController@modify_check'));
    Route::get('/ressource/up/{id}', array('as' => 'ressource_order_up', 'uses' => 'RessourceController@order_up'));
    Route::get('/ressource/down/{id}', array('as' => 'ressource_order_down', 'uses' => 'RessourceController@order_down'));

	Route::get('/organisations', array('as' => 'organisation_list', 'uses' => 'OrganisationController@liste'));
	Route::get('/organisations/reset-filter', array('as' => 'organisation_filter_reset', 'uses' => 'OrganisationController@cancelFilter'));
	Route::post('/organisations', array('as' => 'organisation_list', 'uses' => 'OrganisationController@liste'));
	Route::get('/organisation/add', array('as' => 'organisation_add', 'uses' => 'OrganisationController@add'));
	Route::post('/organisation/add', array('as' => 'organisation_add_check', 'uses' => 'OrganisationController@add_check'));
	Route::get('/organisation/modify/{id}', array('as' => 'organisation_modify', 'uses' => 'OrganisationController@modify'))->where(array('id' => '[0-9]+'));
	Route::post('/organisation/modify/{id}', array('as' => 'organisation_modify_check', 'uses' => 'OrganisationController@modify_check'))->where(array('id' => '[0-9]+'));
    Route::post('/organisation/{id}/add/user', array('as' => 'organisation_add_user', 'uses' => 'OrganisationController@add_user'))->where(array('id' => '[0-9]+'));
    Route::post('/organisation/{id}/add/users', array('as' => 'organisation_add_users', 'uses' => 'OrganisationController@add_users'))->where(array('id' => '[0-9]+'));
	Route::post('/organisation/user/add/{id}', array('as' => 'organisation_user_add', 'uses' => 'OrganisationController@user_add'))->where(array('id' => '[0-9]+'));
	Route::get('/organisation/{organisation}/delete/user/{id}', array('as' => 'organisation_delete_user', 'uses' => 'OrganisationController@delete_user'))->where(array('organisation' => '[0-9]+', 'id' => '[0-9]+'));
    Route::get('/organisation/{id}/remind', array('as' => 'organisation_remind', 'uses' => 'OrganisationController@remind'))->where(array('id' => '[0-9]+'));
    Route::post('/organisation/{id}/remind/send', array('as' => 'organisation_remind_send', 'uses' => 'OrganisationController@remind_send'))->where(array('id' => '[0-9]+'));
    Route::get('/organisation/{id}/usage/export', array('as' => 'organisation_usage_export', 'uses' => 'OrganisationController@usage_export'))->where(array('id' => '[0-9]+'));

    Route::get('/domiciliation/{id}/renew', array('as' => 'domiciliation_renew', 'uses' => 'DomiciliationController@renew'));

    Route::get('/countries', array('as' => 'country_list', 'uses' => 'CountryController@liste'));
    Route::get('/country/add', array('as' => 'country_add', 'uses' => 'CountryController@add'));
    Route::post('/country/add', array('as' => 'country_add_check', 'uses' => 'CountryController@add_check'));
    Route::get('/country/modify/{id}', array('as' => 'country_modify', 'uses' => 'CountryController@modify'))->where(array('id' => '[0-9]+'));
    Route::post('/country/modify/{id}', array('as' => 'country_modify_check', 'uses' => 'CountryController@modify_check'))->where(array('id' => '[0-9]+'));

    Route::get('/devices', array('as' => 'device_list', 'uses' => 'DeviceController@liste'));
    Route::post('/devices', array('as' => 'device_list', 'uses' => 'DeviceController@liste'));
    Route::get('/device/add', array('as' => 'device_add', 'uses' => 'DeviceController@add'));
    Route::post('/device/add', array('as' => 'device_add_check', 'uses' => 'DeviceController@add_check'));
    Route::get('/device/modify/{id}', array('as' => 'device_modify', 'uses' => 'DeviceController@modify'))->where(array('id' => '[0-9]+'));
    Route::post('/device/modify/{id}', array('as' => 'device_modify_check', 'uses' => 'DeviceController@modify_check'))->where(array('id' => '[0-9]+'));
    Route::get('/device/delete/{id}', array('as' => 'device_delete', 'uses' => 'DeviceController@delete'))->where(array('id' => '[0-9]+'));
    Route::get('/device/enable/{id}', array('as' => 'device_enable', 'uses' => 'DeviceController@enableTracking'))->where(array('id' => '[0-9]+'));
    Route::get('/device/disable/{id}', array('as' => 'device_disable', 'uses' => 'DeviceController@disableTracking'))->where(array('id' => '[0-9]+'));
    Route::get('/device/reset-filter', array('as' => 'device_filter_reset', 'uses' => 'DeviceController@cancelFilter'));

    Route::get('/vats', array('as' => 'vat_list', 'uses' => 'VatTypeController@liste'));
    Route::get('/vat/add', array('as' => 'vat_add', 'uses' => 'VatTypeController@add'));
    Route::post('/vat/add', array('as' => 'vat_add_check', 'uses' => 'VatTypeController@add_check'));
    Route::get('/vat/modify/{id}', array('as' => 'vat_modify', 'uses' => 'VatTypeController@modify'))->where(array('id' => '[0-9]+'));
    Route::post('/vat/modify/{id}', array('as' => 'vat_modify_check', 'uses' => 'VatTypeController@modify_check'))->where(array('id' => '[0-9]+'));
    Route::get('/vat/overview', array('as' => 'vat_overview', 'uses' => 'VatController@overview'));

    Route::get('/stats/overview', array('as' => 'stats_overview', 'uses' => 'StatsController@overview'));
    Route::get('/stats/sales', array('as' => 'stats_sales', 'uses' => 'StatsController@sales'));
    Route::get('/stats/customers', array('as' => 'stats_customers', 'uses' => 'StatsController@customers'));
    Route::get('/stats/charges', array('as' => 'stats_charges', 'uses' => 'StatsController@charges'));
    Route::get('/stats/subscriptions', array('as' => 'stats_subscriptions', 'uses' => 'StatsController@subscriptions'));
    Route::get('/stats/sales_per_category/{period?}', array('as' => 'stats_sales_per_category', 'uses' => 'StatsController@sales_per_category'));
    Route::get('/stats/sales_per_category_and_location/{period}/{location_id}', array('as' => 'stats_sales_per_category_and_location', 'uses' => 'StatsController@sales_per_category'));
    Route::get('/stats/members', array('as' => 'stats_members', 'uses' => 'StatsController@members'));
    Route::get('/stats/age', array('as' => 'stats_age', 'uses' => 'StatsController@age'));
    Route::get('/stats/spaces', array('as' => 'stats_spaces', 'uses' => 'StatsController@spaces'));
    Route::get('/stats/spaces/{space_slug}/{period}', array('as' => 'stats_spaces_details', 'uses' => 'StatsController@spaces_details'));
    Route::get('/stats/ressource/{ressource_id}', array('as' => 'stats_sales_per_ressource', 'uses' => 'StatsController@sales_per_ressource'));
    Route::get('/stats/top_customers', array('as' => 'stats_top_customers', 'uses' => 'StatsController@top_customers'));

    Route::get('/tags', array('as' => 'tag_list', 'uses' => 'TagController@liste'));
    Route::get('/tag/add', array('as' => 'tag_add', 'uses' => 'TagController@add'));
    Route::post('/tag/add', array('as' => 'tag_add_check', 'uses' => 'TagController@add_check'));
    Route::get('/tag/modify/{id}', array('as' => 'tag_modify', 'uses' => 'TagController@modify'))->where(array('id' => '[0-9]+'));
    Route::post('/tag/modify/{id}', array('as' => 'tag_modify_check', 'uses' => 'TagController@modify_check'))->where(array('id' => '[0-9]+'));

    Route::any('/charges/{filtre}', array('as' => 'charge_list', 'uses' => 'ChargeController@liste'))->where(array('filtre' => '[a-z-_]+'));
    Route::get('/charge/add', array('as' => 'charge_add', 'uses' => 'ChargeController@add'));
    Route::post('/charge/add', array('as' => 'charge_add_check', 'uses' => 'ChargeController@add_check'));
    Route::get('/charge/modify/{id}', array('as' => 'charge_modify', 'uses' => 'ChargeController@modify'))->where(array('id' => '[0-9]+'));
    Route::post('/charge/modify/{id}', array('as' => 'charge_modify_check', 'uses' => 'ChargeController@modify_check'))->where(array('id' => '[0-9]+'));
    Route::delete('/charge/delete/{id}', array('as' => 'charge_delete', 'uses' => 'ChargeController@delete'))->where(array('id' => '[0-9]+'));
    Route::delete('/charge/{charge}/item/{id}/delete', array('as' => 'charge_item_delete', 'uses' => 'ChargeItemController@delete'))->where(array('charge' => '[0-9]+', 'id' => '[0-9]+'));
    Route::delete('/charge/{charge}/payment/{id}/delete', array('as' => 'charge_payment_delete', 'uses' => 'ChargePaymentController@delete'))->where(array('charge' => '[0-9]+', 'id' => '[0-9]+'));
    Route::get('/charge/duplicate/{id}', array('as' => 'charge_duplicate', 'uses' => 'ChargeController@duplicate'))->where(array('id' => '[0-9]+'));

    Route::get('/pasttime/delete/{id}', array('as' => 'pasttime_delete', 'uses' => 'PastTimeController@delete'))->where(array('id' => '[0-9]+'));
    Route::post('/pasttime/invoice', array('as' => 'pasttime_invoice', 'uses' => 'PastTimeController@invoice'));


    Route::get('/subscriptions', array('as' => 'subscription_list', 'uses' => 'SubscriptionController@liste'));
    Route::post('/subscriptions', array('as' => 'subscription_list', 'uses' => 'SubscriptionController@liste'));
    Route::get('/subscription/add', array('as' => 'subscription_add', 'uses' => 'SubscriptionController@add'));
    Route::post('/subscription/add', array('as' => 'subscription_add_check', 'uses' => 'SubscriptionController@add_check'));
    Route::get('/subscription/modify/{id}', array('as' => 'subscription_modify', 'uses' => 'SubscriptionController@modify'));
    Route::post('/subscription/modify/{id}', array('as' => 'subscription_modify_check', 'uses' => 'SubscriptionController@modify_check'));
    Route::get('/subscription/delete/{id}', array('as' => 'subscription_delete', 'uses' => 'SubscriptionController@delete'));
    Route::get('/subscription/renew/{id}', array('as' => 'subscription_renew', 'uses' => 'SubscriptionController@renew'));
    Route::get('/subscription/renew/company/{id}', array('as' => 'subscription_renew_company', 'uses' => 'SubscriptionController@renewCompany'));
    Route::get('/subscription/reset-filter', array('as' => 'subscription_filter_reset', 'uses' => 'SubscriptionController@cancelFilter'));
    Route::get('/subscription/overuse', array('as' => 'subscription_overuse', 'uses' => 'SubscriptionController@overuse'));
    Route::get('/subscription/overuse/managed/{id}', array('as' => 'subscription_overuse_managed', 'uses' => 'SubscriptionController@overuseManaged'));

    Route::get('/wall/delete/{id}', array('as' => 'wall_delete', 'uses' => 'WallPostController@delete'));
    Route::get('/wall/delete-reply/{id}', array('as' => 'wall_delete_reply', 'uses' => 'WallPostController@deleteReply'));

});

// JSON
Route::get('/user/organisations/{id}', array('as' => 'user_json_organisations', 'uses' => 'UserController@json_organisations'))->where(array('id' => '[0-9]+'));
Route::get('/organisation/users/{id}', array('as' => 'organisation_json_users', 'uses' => 'OrganisationController@json_users'))->where(array('id' => '[0-9]+'));
Route::get('/organisation/infos/{id}', array('as' => 'organisation_json_infos', 'uses' => 'OrganisationController@json_infos'))->where(array('id' => '[0-9]+'));
Route::get('/tags/list', array('as' => 'tag_json_list', 'uses' => 'TagController@json_list'));
Route::get('/organisations/list', array('as' => 'organisation_json_list', 'uses' => 'OrganisationController@json_list'));
