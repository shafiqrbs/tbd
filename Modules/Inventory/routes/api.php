<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Inventory\App\Http\Controllers\BusinessModelController;
use Modules\Inventory\App\Http\Controllers\CategoryGroupController;
use Modules\Inventory\App\Http\Controllers\ConfigController;

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


Route::prefix('/inventory/select')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/business-model', [BusinessModelController::class,'businessModelDropdown'])->name('business_model_dropdown');
    Route::get('/category-group', [CategoryGroupController::class,'categoryGroupDropdown'])->name('category_group_dropdown');
});

Route::prefix('/inventory')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/config', [ConfigController::class,'getConfig'])->name('get_config');
    Route::PATCH('/config-update', [ConfigController::class,'updateConfig'])->name('update_config');

    Route::apiResource('/category-group', CategoryGroupController::class)->middleware([HeaderAuthenticationMiddleware::class]);

});


