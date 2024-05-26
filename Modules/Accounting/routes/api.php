<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Accounting\App\Http\Controllers\AccountingController;
use Modules\Accounting\App\Http\Controllers\AccountHeadController;
use Modules\Accounting\App\Http\Controllers\TransactionModeController;

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


Route::prefix('/accounting/select')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/transaction-method', [AccountHeadController::class,'transactionMethodDropdown'])->name('transaction_method_dropdown');
});

Route::prefix('/accounting')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {

    Route::get('/transaction-mode-data', [TransactionModeController::class,'transactionMode'])->name('transaction_mode');
    Route::get('/transaction-mode/local-storage', [TransactionModeController::class,'LocalStorage'])->name('transaction_mode_local_storage');

    Route::apiResource('/transaction-mode', TransactionModeController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::apiResource('/account-head', AccountHeadController::class)->middleware([HeaderAuthenticationMiddleware::class]);

});

