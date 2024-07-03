<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Production\App\Models\SettingModel;

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
    Route::get('/setting', [SettingModel::class,'settingDropdown'])->name('pro_setting_dropdown');
});

Route::prefix('/production')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::apiResource('setting', SettingModel::class)->middleware([HeaderAuthenticationMiddleware::class])->parameters(['setting' => 'production.setting']);
});
