<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Core\App\Http\Controllers\CoreController;
use Modules\Core\App\Http\Controllers\CustomerController;
use Modules\Core\App\Http\Controllers\LocationController;
use Modules\Core\App\Http\Controllers\UserController;
use Modules\Core\App\Http\Controllers\VendorController;
use Modules\Inventory\App\Http\Controllers\InventoryController;

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


Route::prefix('/core/select')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/user', [CoreController::class,'user'])->name('user_autosearch');
    Route::get('/executive', [CoreController::class,'executive'])->name('executive_autosearch');
    Route::get('/vendor', [CoreController::class,'vendor'])->name('vendor_autosearch');
    Route::get('/customer', [CoreController::class,'customer'])->name('customer_autosearch');
    Route::get('/location', [CoreController::class,'location'])->name('location_autosearch');
});

Route::prefix('/core')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/customer/details', [CustomerController::class,'details'])->name('customer_details');
    Route::get('/customer/local-storage', [CustomerController::class,'localStorage'])->name('customer_local_storage');

    Route::get('/vendor/details', [VendorController::class,'details'])->name('vendor_details');
    Route::get('/vendor/local-storage', [VendorController::class,'localStorage'])->name('vendor_local_storage');

    Route::apiResource('user', UserController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::apiResource('location', LocationController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::apiResource('customer', CustomerController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::apiResource('vendor', VendorController::class)->middleware([HeaderAuthenticationMiddleware::class]);
});

