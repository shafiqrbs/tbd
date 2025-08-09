<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Hospital\App\Http\Controllers\HospitalController;
use Modules\Hospital\App\Http\Controllers\OpdController;
use Modules\Hospital\App\Http\Controllers\SettingController;


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

Route::prefix('/hospital/select')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/module', [HospitalController::class,'particularModuleDropdown'])->name('particular_module_dropdown');
    Route::get('/particular', [HospitalController::class,'particularModuleDropdown'])->name('particular_particular_dropdown');

});

Route::prefix('/hospital')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/setting', [SettingController::class,'particularModuleDropdown'])->name('particular_module_dropdown');
    Route::get('/setting', [SettingController::class,'particularModuleDropdown'])->name('particular_module_dropdown');
    Route::apiResource('opd', OPDController::class)->middleware([HeaderAuthenticationMiddleware::class]);

});
