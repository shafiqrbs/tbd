<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Core\App\Http\Middleware\LogRequestResponse;
use Modules\NbrVatTax\App\Http\Controllers\NbrVatTaxController;

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

Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
    Route::get('nbrvattax', fn (Request $request) => $request->user())->name('nbrvattax');
});

Route::prefix('/nbr/select')->middleware([HeaderAuthenticationMiddleware::class,LogRequestResponse::class])->group(function() {
    Route::get('/tariff', [NbrVatTaxController::class,'tariffDropdown'])->name('get_tariff_dropdown');

});

Route::prefix('/nbr')->middleware([HeaderAuthenticationMiddleware::class,LogRequestResponse::class])->group(function() {
//    Route::get('/config', [ConfigController::class,'getConfig'])->name('get_config');
});

