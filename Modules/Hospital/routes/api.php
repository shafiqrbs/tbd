<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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



Route::prefix('/hospital')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
       Route::get('/setting', [SettingController::class,'particularModuleDropdown'])->name('particular_module_dropdown');
    //   Route::get('/particular', [AccountingController::class,'settingDropdown'])->name('setting_accounting_dropdown');
 //   Route::get('/setting-type', [AccountingController::class,'settingTypeDropdown'])->name('setting_accounting_dropdown_type');
    Route::apiResource('opd', OPDController::class)->middleware([HeaderAuthenticationMiddleware::class]);

});
