<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Inventory\App\Http\Controllers\BusinessModelController;
use Modules\Inventory\App\Http\Controllers\CategoryGroupController;
use Modules\Inventory\App\Http\Controllers\ConfigController;
use Modules\Inventory\App\Http\Controllers\InventoryController;
use Modules\Inventory\App\Http\Controllers\InvoiceBatchController;
use Modules\Inventory\App\Http\Controllers\InvoiceBatchTransactionController as InvoiceBatchTransactionControllerAlias;
use Modules\Inventory\App\Http\Controllers\OpeningStockController;
use Modules\Inventory\App\Http\Controllers\ParticularController;
use Modules\Inventory\App\Http\Controllers\ProductController;
use Modules\Inventory\App\Http\Controllers\PurchaseController;
use Modules\Inventory\App\Http\Controllers\PurchaseItemController;
use Modules\Inventory\App\Http\Controllers\SalesController;
use Modules\Inventory\App\Http\Controllers\SettingController;
use Modules\Inventory\App\Http\Controllers\StockItemController;

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
    Route::get('/product-unit', [InventoryController::class,'productUnitDropdown'])->name('product_unit_dropdown');
    Route::get('/product-for-recipe', [ProductController::class,'productForRecipe'])->name('product_for_recipe_dropdown');
    Route::get('/particular-type', [ParticularController::class,'particularTypeDropdown'])->name('get_particular_type');
});

Route::prefix('/inventory')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/config', [ConfigController::class,'getConfig'])->name('get_config');
    Route::patch('/config-update/{id}', [ConfigController::class,'updateConfig'])->name('update_config');

    Route::apiResource('/setting', SettingController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::get('/setting/setting-type', [SettingController::class,'settingTypeDropdown'])->name('get_setting_type');

    Route::apiResource('/particular', ParticularController::class)->middleware([HeaderAuthenticationMiddleware::class]);

    Route::apiResource('/category-group', CategoryGroupController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::apiResource('/product', ProductController::class)->middleware([HeaderAuthenticationMiddleware::class]);

    Route::prefix('/product')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
        Route::post('/measurement', [ProductController::class,'measurementAdded'])->name('product_measurement_added');
        Route::get('/measurement/{product}', [ProductController::class,'measurementList'])->name('product_measurement_list');
        Route::delete('/measurement/{product}', [ProductController::class,'measurementDelete'])->name('product_measurement_delete');

        Route::post('/gallery', [ProductController::class,'galleryAdded'])->name('product_gallery_added');
    });

    Route::apiResource('/stock', StockItemController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::get('/stock-item', [StockItemController::class,'stockItem'])->name('get_stock_item');

    Route::apiResource('/sales', SalesController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::get('/sales/edit/{id}', [SalesController::class,'edit'])->name('get_edit_sales');
    Route::apiResource('/invoice-batch', InvoiceBatchController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::apiResource('/invoice-batch-transaction', InvoiceBatchTransactionControllerAlias::class)->middleware([HeaderAuthenticationMiddleware::class]);


    Route::apiResource('/purchase', PurchaseController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::get('/purchase/edit/{id}', [PurchaseController::class,'edit'])->name('get_edit_purchase');

    Route::apiResource('/purchase-item', PurchaseItemController::class)->middleware([HeaderAuthenticationMiddleware::class]);

    Route::apiResource('/opening-stock', OpeningStockController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::post('/opening-stock/inline-update', [OpeningStockController::class,'inlineUpdate'])->name('opening_stock_inline_update');


});


