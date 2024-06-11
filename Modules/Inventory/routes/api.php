<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Inventory\App\Http\Controllers\BusinessModelController;
use Modules\Inventory\App\Http\Controllers\CategoryGroupController;
use Modules\Inventory\App\Http\Controllers\ConfigController;
use Modules\Inventory\App\Http\Controllers\InventoryController;
use Modules\Inventory\App\Http\Controllers\InvoiceBatchController;
use Modules\Inventory\App\Http\Controllers\OpeningStockController;
use Modules\Inventory\App\Http\Controllers\ProductController;
use Modules\Inventory\App\Http\Controllers\PurchaseController;
use Modules\Inventory\App\Http\Controllers\PurchaseItemController;
use Modules\Inventory\App\Http\Controllers\SalesController;

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


Route::prefix('/inventory/select')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/setting', [InventoryController::class,'settingDropdown'])->name('setting_dropdown');
    Route::get('/category', [InventoryController::class,'categoryDropdown'])->name('category_dropdown');
    Route::get('/group-category', [InventoryController::class,'categoryGroupDropdown'])->name('category_group_dropdown');
    Route::get('/product-brand', [InventoryController::class,'brandDropdown'])->name('product_brand_dropdown');
});

Route::prefix('/inventory')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/config', [ConfigController::class,'getConfig'])->name('get_config');
    Route::PATCH('/config-update', [ConfigController::class,'updateConfig'])->name('update_config');

    Route::apiResource('/category-group', CategoryGroupController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::apiResource('/product', ProductController::class)->middleware([HeaderAuthenticationMiddleware::class]);

    Route::apiResource('/sales', SalesController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::apiResource('/invoice-batch', InvoiceBatchController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::get('/sales/edit/{id}', [SalesController::class,'edit'])->name('get_edit_sales');


    Route::apiResource('/purchase', PurchaseController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::get('/purchase/edit/{id}', [PurchaseController::class,'edit'])->name('get_edit_purchase');

    Route::apiResource('/purchase-item', PurchaseItemController::class)->middleware([HeaderAuthenticationMiddleware::class]);

    Route::apiResource('/opening-stock', OpeningStockController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::post('/opening-stock/inline-update', [OpeningStockController::class,'inlineUpdate'])->name('opening_stock_inline_update');
    Route::get('/stock-item', [ProductController::class,'stockItem'])->name('get_stock_item');

});


