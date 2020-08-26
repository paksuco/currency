<?php

/**
 * Routes for the package would go here
 */

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config("currencies.admin_route_prefix", ""),
    'as' => 'paksuco.',
], function () {
    Route::get('/currencies', "\Paksuco\Currency\Controllers\CurrencyController@index")->name("currencies.admin");
});
