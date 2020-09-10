<?php

/**
 * Routes for the package would go here
 */

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => Config::get("currencies.admin_route_prefix", ""),
    'as' => 'paksuco.',
], function () {
    Route::get('/currencies', "\Paksuco\Currency\Controllers\CurrencyController@index")->name("currencies.admin");
});

Route::get('currency/{currency_id}', '\Paksuco\Currency\Controllers\CurrencyController@set');
