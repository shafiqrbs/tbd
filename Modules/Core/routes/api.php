<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Core\App\Http\Controllers\CustomerController;
use Modules\Core\App\Http\Controllers\LocationController;
use Modules\Core\App\Http\Controllers\UserController;

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


Route::prefix('/customer')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/select-search', [CustomerController::class,'selectSearch'])->name('customer_autosearch');
});
Route::prefix('/location')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/select-search', [LocationController::class,'selectSearch'])->name('location_autosearch');
});
Route::apiResource('user', UserController::class)->middleware([HeaderAuthenticationMiddleware::class]);
Route::apiResource('location', LocationController::class)->middleware([HeaderAuthenticationMiddleware::class]);
Route::apiResource('customer', CustomerController::class)->middleware([HeaderAuthenticationMiddleware::class]);
Route::apiResource('vendor', UserController::class)->middleware([HeaderAuthenticationMiddleware::class]);


