<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Utility\App\Http\Controllers\SiteMapController;
use Modules\Utility\App\Http\Controllers\UtilityController;


/*
    |--------------------------------------------------------------------------
    | API Routes
    |--------------------------------------------------------------------------
    |
    | Here is where you can register API routes for your application. These
    | routes are loaded by the RouteServiceProvider within a group which
    | is assigned the "api" middleware group. Enjoy building your API!
    |
*/




Route::prefix('/utility/select')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/setting', [UtilityController::class,'settingDropdown'])->name('utility_setting_dropdown');
    Route::get('/product-unit', [UtilityController::class,'productUnitDropdown'])->name('utility_product_unit');
});

Route::prefix('/utility')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::apiResource('/sitemap', SiteMapController::class)->middleware([HeaderAuthenticationMiddleware::class]);

});

