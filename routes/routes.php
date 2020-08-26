<?php

/**
 * Routes for the package would go here
 */

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config(".admin_route_prefix", ""),
    'as' => 'paksuco.',
], function () {
    Route::get('/', "\Paksuco\\Controllers\Controller@index")->name(".admin-");
});
