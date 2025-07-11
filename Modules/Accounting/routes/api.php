<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Accounting\App\Http\Controllers\AccountGroupHeadController;
use Modules\Accounting\App\Http\Controllers\AccountHeadMasterController;
use Modules\Accounting\App\Http\Controllers\AccountingController;
use Modules\Accounting\App\Http\Controllers\AccountHeadController;
use Modules\Accounting\App\Http\Controllers\AccountSettingController;
use Modules\Accounting\App\Http\Controllers\AccountVoucherController;
use Modules\Accounting\App\Http\Controllers\AccountVoucherEntryController;
use Modules\Accounting\App\Http\Controllers\TransactionModeController;
use Modules\Core\App\Http\Middleware\LogRequestResponse;

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


Route::prefix('/accounting/select')->middleware([HeaderAuthenticationMiddleware::class,LogRequestResponse::class])->group(function() {
    Route::get('/transaction-method', [AccountingController::class,'transactionMethodDropdown'])->name('transaction_method_dropdown');
    Route::get('/setting', [AccountingController::class,'settingDropdown'])->name('setting_accounting_dropdown');
    Route::get('/setting-type', [AccountingController::class,'settingTypeDropdown'])->name('setting_accounting_dropdown_type');
    Route::get('/voucher', [AccountingController::class,'accountVoucherDropdown'])->name('accounting_head_dropdown');
    Route::get('/head', [AccountingController::class,'accountHeadDropdown'])->name('accounting_head_dropdown');
    Route::get('/head-master', [AccountingController::class,'accountHeadMasterDropdown'])->name('accounting_head_master_dropdown');
    Route::get('/ledger', [AccountingController::class,'accountLedgerDropdown'])->name('accounting_ledger_dropdown');
    Route::get('/head-dropdown', [AccountingController::class,'accountAllDropdownBySlug'])->name('account_all_dropdown_by_slug');
});

Route::prefix('/accounting')->middleware([HeaderAuthenticationMiddleware::class,LogRequestResponse::class])->group(function() {

    Route::get('/transaction-mode-data', [TransactionModeController::class,'transactionMode'])->name('transaction_mode');
    Route::get('/transaction-mode/local-storage', [TransactionModeController::class,'LocalStorage'])->name('transaction_mode_local_storage');
    Route::get('/account-head/local-storage', [AccountGroupHeadController::class,'LocalStorage'])->name('head_group_local_storage');

    Route::post('/transaction-mode-update/{id}', [TransactionModeController::class,'update'])->name('transaction-mode.update-customize');
    Route::apiResource('/transaction-mode', TransactionModeController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::apiResource('/account-head', AccountHeadController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::get('/account-head-generate', [AccountHeadController::class,'generateAccountHead'])->name('account_head_generate');
    Route::get('/account-head-reset', [AccountHeadController::class,'resetAccountHead'])->name('account_head_reset');
    Route::get('/account-voucher-reset', [AccountHeadController::class,'resetAccountVoucher'])->name('account_head_reset');
    Route::get('/account-sub-head', [AccountHeadController::class,'accountSubHead'])->name('account_sub_head');
    Route::get('/account-ledger', [AccountHeadController::class,'accountLedger'])->name('account_ledger');
    Route::get('/account-ledger-wise/journal/{id}', [AccountHeadController::class,'accountLedgerWiseJournal'])->name('account_ledger_wise_journal');
    Route::get('/account-ledger-reset', [AccountHeadController::class,'resetAccountLedgerHead'])->name('account_head_reset');

    Route::get('/voucher/wise-ledger-details', [AccountVoucherController::class,'accountVoucherWiseLedger'])->name('account_voucher_wise_ledger');
    Route::post('/voucher/update-heads', [AccountVoucherController::class, 'updateVoucherHeads'])->name('account_voucher_update_heads');
    Route::get('/voucher/status-update/{id}', [AccountVoucherController::class, 'statusUpdate'])->name('account_voucher_update_status');
    Route::apiResource('/voucher',
        AccountVoucherController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->parameters(['setting' => 'accounting.voucher'])
        ->names([
            'index' => 'accounting.voucher.index',
            'store' => 'accounting.voucher.store',
            'show' => 'accounting.voucher.show',
            'update' => 'accounting.voucher.update',
            'destroy' => 'accounting.voucher.destroy'
        ]);

    Route::apiResource('/account-head-master',
        AccountHeadMasterController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->parameters(['setting' => 'accounting.headmaster'])
        ->names([
            'index' => 'accounting.headmaster.index',
            'store' => 'accounting.headmaster.store',
            'show' => 'accounting.headmaster.show',
            'update' => 'accounting.headmaster.update',
            'destroy' => 'accounting.headmaster.destroy'
        ]);


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

    Route::get('/voucher-entry/approve/{id}', [AccountVoucherEntryController::class,'accountVoucherApprove'])->name('account_voucher_approve');
    Route::apiResource('/voucher-entry', AccountVoucherEntryController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->parameters(['setting' => 'accounting.setting'])
        ->names([
            'index' => 'accounting.setting.index',
            'store' => 'accounting.setting.store',
            'show' => 'accounting.setting.show',
            'update' => 'accounting.setting.update',
            'destroy' => 'accounting.setting.destroy'
        ]);

});

