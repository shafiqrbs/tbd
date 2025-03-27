<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Accounting\App\Http\Controllers\AccountGroupHeadController;
use Modules\Accounting\App\Http\Controllers\AccountingController;
use Modules\Accounting\App\Http\Controllers\AccountHeadController;
use Modules\Accounting\App\Http\Controllers\AccountSettingController;
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
    Route::get('/transaction-method', [AccountingController::class,'transactionMethodDropdown'])->name('transaction_method_dropdown');
    Route::get('/setting', [AccountingController::class,'settingDropdown'])->name('setting_accounting_dropdown');
    Route::get('/setting-type', [AccountingController::class,'settingTypeDropdown'])->name('setting_accounting_dropdown_type');
    Route::get('/head', [AccountingController::class,'accountHeadDropdown'])->name('accounting_head_dropdown');
    Route::get('/ledger', [AccountingController::class,'accountLedgerDropdown'])->name('accounting_ledger_dropdown');
});

Route::prefix('/accounting')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {

    Route::get('/transaction-mode-data', [TransactionModeController::class,'transactionMode'])->name('transaction_mode');
    Route::get('/transaction-mode/local-storage', [TransactionModeController::class,'LocalStorage'])->name('transaction_mode_local_storage');
    Route::get('/account-head/local-storage', [AccountGroupHeadController::class,'LocalStorage'])->name('head_group_local_storage');

    Route::post('/transaction-mode-update/{id}', [TransactionModeController::class,'update'])->name('transaction-mode.update-customize');
    Route::apiResource('/transaction-mode', TransactionModeController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::apiResource('/account-head', AccountHeadController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::get('/account-head-generate', [AccountHeadController::class,'generateAccountHead'])->name('account_head_generate');
    Route::get('/account-head-reset', [AccountHeadController::class,'resetAccountHead'])->name('account_head_reset');
    Route::get('/account-sub-head', [AccountHeadController::class,'accountSubHead'])->name('account_sub_head');
    Route::get('/account-ledger', [AccountHeadController::class,'accountLedger'])->name('account_ledger');

    Route::apiResource('/setting', AccountSettingController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->parameters(['setting' => 'accounting.setting'])
        ->names([
            'index' => 'accounting.setting.index',
            'store' => 'accounting.setting.store',
            'show' => 'accounting.setting.show',
            'update' => 'accounting.setting.update',
            'destroy' => 'accounting.setting.destroy'
        ]);
    ;

});

