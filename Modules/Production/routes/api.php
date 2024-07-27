<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Production\App\Http\Controllers\ProductionRecipeController;
use Modules\Production\App\Http\Controllers\ProductionRecipeItemsController;
use Modules\Production\App\Http\Controllers\SettingController;

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


Route::prefix('/production/select')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/setting-type', [SettingController::class,'settingTypeDropdown'])->name('pro_setting_type_dropdown');
});

Route::prefix('/production')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::apiResource('setting', SettingController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->names([
            'index' => 'production.setting.index',
            'store' => 'production.setting.store',
            'show' => 'production.setting.show',
            'update' => 'production.setting.update',
            'destroy' => 'production.setting.destroy'
        ]);
    ;

    Route::apiResource('recipe-items', ProductionRecipeItemsController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->names([
            'index' => 'production.items.index',
            'store' => 'production.items.store',
            'show' => 'production.items.show',
            'update' => 'production.items.update',
            'destroy' => 'production.items.destroy'
        ]);
    ;

    Route::apiResource('recipe', ProductionRecipeController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->names([
            'index'     => 'production.recipe.index',
            'restore'   => 'production.recipe.restore',
            'store'     => 'production.recipe.store',
            'show'      => 'production.recipe.show',
            'update'    => 'production.recipe.update',
            'destroy'   => 'production.recipe.destroy'
        ]);
    ;
});
