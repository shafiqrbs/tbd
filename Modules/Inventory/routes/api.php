<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Core\App\Http\Middleware\LogRequestResponse;
use Modules\Inventory\App\Http\Controllers\BusinessModelController;
use Modules\Inventory\App\Http\Controllers\CategoryGroupController;
use Modules\Inventory\App\Http\Controllers\ConfigController;
use Modules\Inventory\App\Http\Controllers\DiscountConfigController;
use Modules\Inventory\App\Http\Controllers\InventoryController;
use Modules\Inventory\App\Http\Controllers\InvoiceBatchController;
use Modules\Inventory\App\Http\Controllers\InvoiceBatchTransactionController as InvoiceBatchTransactionControllerAlias;
use Modules\Inventory\App\Http\Controllers\OpeningStockController;
use Modules\Inventory\App\Http\Controllers\ParticularController;
use Modules\Inventory\App\Http\Controllers\PosController;
use Modules\Inventory\App\Http\Controllers\ProductController;
use Modules\Inventory\App\Http\Controllers\PurchaseController;
use Modules\Inventory\App\Http\Controllers\PurchaseItemController;
use Modules\Inventory\App\Http\Controllers\PurchaseReturnController;
use Modules\Inventory\App\Http\Controllers\RequisitionController;
use Modules\Inventory\App\Http\Controllers\RequisitionMatrixBoardController;
use Modules\Inventory\App\Http\Controllers\SalesController;
use Modules\Inventory\App\Http\Controllers\SalesReturnController;
use Modules\Inventory\App\Http\Controllers\SettingController;
use Modules\Inventory\App\Http\Controllers\StockItemController;
use Modules\Inventory\App\Http\Controllers\WarehouseIssueController;

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


Route::prefix('/inventory/select')->middleware([HeaderAuthenticationMiddleware::class,LogRequestResponse::class])->group(function() {
    Route::get('/setting', [InventoryController::class,'settingDropdown'])->name('setting_dropdown');
    Route::get('/category', [InventoryController::class,'categoryDropdown'])->name('category_dropdown');
    Route::get('/group-category', [InventoryController::class,'categoryGroupDropdown'])->name('category_group_dropdown');
    Route::get('/product-brand', [InventoryController::class,'brandDropdown'])->name('product_brand_dropdown');
    Route::get('/product-unit', [InventoryController::class,'productUnitDropdown'])->name('product_unit_dropdown');
    Route::get('/product-for-recipe', [ProductController::class,'productForRecipe'])->name('product_for_recipe_dropdown');
    Route::get('/particular-type', [ParticularController::class,'particularTypeDropdown'])->name('get_particular_type');
    Route::get('/particular', [ParticularController::class,'particularDropdown'])->name('get_particular_dropdown');

});

Route::prefix('/inventory')->middleware([HeaderAuthenticationMiddleware::class,LogRequestResponse::class])->group(function() {
    Route::get('/config', [ConfigController::class,'getConfig'])->name('get_config');
    Route::get('/config/{id}', [ConfigController::class,'getConfigById'])->name('get_config_by_id');
    Route::post('/config-update/{id}', [ConfigController::class,'updateConfig'])->name('update_config');

    Route::apiResource('/setting', SettingController::class);
    Route::get('/setting/setting-type', [SettingController::class,'settingTypeDropdown'])->name('get_setting_type');

    Route::apiResource('/particular', ParticularController::class);

    Route::apiResource('/category-group', CategoryGroupController::class);
    Route::apiResource('/product', ProductController::class);

    Route::prefix('/product')->group(function() {

        Route::get('/status/inline-update/{id}', [ProductController::class,'productStatusInlineUpdate'])->name('product_status_inline_update');
        Route::prefix('/measurement')->group(function() {
            Route::post('', [ProductController::class,'measurementAdded'])->name('product_measurement_added');
            Route::post('/sales-purchase/{product}', [ProductController::class,'measurementSalesPurchaseUpdate'])->name('product_measurement_sales_purchase_update');
            Route::get('/{product}', [ProductController::class,'measurementList'])->name('product_measurement_list');
            Route::delete('/{product}', [ProductController::class,'measurementDelete'])->name('product_measurement_delete');
        });

        Route::get('/nbr-tariff/{product}', [ProductController::class,'nbrTariff'])->name('nbr_tariff_list');
        Route::post('/nbr-tariff/inline-update/{product}', [ProductController::class,'nbrTariffInlineUpdate'])->name('nbr_tariff_inline_update');
        Route::post('/stock/sku', [StockItemController::class,'stockSkuAdded'])->name('product_stock_sku');
        Route::get('/stock/sku/{product}', [StockItemController::class,'stockSkuList'])->name('product_stock_list');
        Route::delete('/stock/sku/{product}', [StockItemController::class,'stockItemDelete'])->name('product_stock_delete');
        Route::post('/stock/sku/inline-update/{stock_id}', [StockItemController::class,'stockSkuInlineUpdate'])->name('product_stock_sku_inline_update');
        Route::post('/gallery', [ProductController::class,'galleryAdded'])->name('product_gallery_added');
        Route::post('/gallery/delete', [ProductController::class,'galleryDelete'])->name('product_gallery_delete');

    });

    Route::prefix('/discount')->group(function() {
        Route::get('config', [DiscountConfigController::class,'index'])->name('discount_config');
        Route::get('users', [DiscountConfigController::class,'userDiscount'])->name('discount_config');
        Route::POST('user-update/{id}', [DiscountConfigController::class,'userDiscountUpdate'])->name('discount_config');
    });

    Route::apiResource('/discount', DiscountConfigController::class)
        ->names([
            'index' => 'discount.config.index',
            'store' => 'discount.config.store',
            'show' => 'discount.config.show',
            'update' => 'domain.setting.update',
            'destroy' => 'domain.setting.destroy',
        ]);
    Route::apiResource('/stock', StockItemController::class);
    Route::get('/stock-item', [StockItemController::class,'stockItem'])->name('get_stock_item');
    Route::get('/stock-item/matrix', [StockItemController::class,'stockItemMatrix'])->name('get_stock_item_matrix');
    Route::get('generate/stock-item/xlsx', [StockItemController::class,'stockItemXlsxGenerate'])->name('get_stock_item_xlsx_generate');


    Route::prefix('sales')->name('sales.')->group(function () {
        Route::prefix('return')->name('return.')->group(function () {
            Route::get('{id}/approve/{type}', [SalesReturnController::class, 'approve'])->name('sales-return.approve');

            Route::apiResource('', SalesReturnController::class)
                ->parameters(['' => 'id']);
        });

        Route::apiResource('', SalesController::class)->parameters(['' => 'id']);

        Route::get('edit/{id}', [SalesController::class, 'edit'])->name('edit');
        Route::get('copy/{id}', [SalesController::class, 'salesCopy'])->name('copy');
        Route::get('domain-customer/{id}', [SalesController::class, 'domainCustomerSales'])->name('get_domain_customer_sales');
        Route::get('not-domain-customer/{id}', [SalesController::class, 'notDomainCustomerSales'])->name('get_not_domain_customer_sales');
    });

    Route::apiResource('/invoice-batch', InvoiceBatchController::class);
    Route::apiResource('/invoice-batch-transaction', InvoiceBatchTransactionControllerAlias::class);


    Route::prefix('purchase')->name('purchase.')->group(function () {
        Route::prefix('return')->name('return.')->group(function () {
            Route::get('{id}/edit', [PurchaseReturnController::class, 'edit'])->name('edit');
            Route::get('{id}/approve/{vendor}', [PurchaseReturnController::class, 'approve'])->name('purchase-return.approve');
            Route::get('vendor-wise-purchase-item', [PurchaseReturnController::class, 'vendorWisePurchaseItem'])->name('vendorWisePurchaseItem');

            // FIXED ✅
            Route::apiResource('', PurchaseReturnController::class)
                ->parameters(['' => 'id']);
        });

        Route::apiResource('', PurchaseController::class)->parameters(['' => 'id']);

        Route::get('edit/{id}', [PurchaseController::class, 'edit'])->name('edit');
        Route::get('copy/{id}', [PurchaseController::class, 'purchaseCopy'])->name('copy');
        Route::get('approve/{id}', [PurchaseController::class, 'approve'])->name('approve');
    });




    Route::apiResource('/purchase-item', PurchaseItemController::class);

    Route::apiResource('/opening-stock', OpeningStockController::class);
    Route::post('/opening-stock/inline-update', [OpeningStockController::class,'inlineUpdate'])->name('opening_stock_inline_update');
  //  Route::post('/inventory/branch-management', [BranchManagementController::class,'branchManagement'])->name('branch_management_update');

    Route::apiResource('/requisition', RequisitionController::class);
    Route::apiResource('/warehouse-issue', WarehouseIssueController::class);
    Route::prefix('/requisition')->group(function() {
        Route::get('approve/{id}', [RequisitionController::class,'approve'])->name('requisition_approve');
        Route::prefix('matrix/board')->group(function() {
            Route::get('', [RequisitionMatrixBoardController::class,'index'])->name('requisition_matrix_board_index');
            Route::get('{id}', [RequisitionMatrixBoardController::class,'matrixBoardDetails'])->name('requisition_matrix_board_details');
            Route::post('create', [RequisitionMatrixBoardController::class,'store'])->name('requisition_matrix_board_create');
            Route::post('quantity-update', [RequisitionMatrixBoardController::class,'matrixBoardQuantityUpdate'])->name('requisition_matrix_board_quantity_update');
            Route::post('batch-generate/{id}', [RequisitionMatrixBoardController::class,'matrixBoardBatchGenerate'])->name('requisition_matrix_board_batch_generate');
            Route::prefix('production')->group(function() {
                Route::get('{id}', [RequisitionMatrixBoardController::class,'boardWiseProduction']);
                Route::post('quantity-update', [RequisitionMatrixBoardController::class,'matrixBoardProductionQuantityUpdate']);
                Route::post('process', [RequisitionMatrixBoardController::class,'matrixBoardProductionProcess']);
                Route::post('approve/{id}', [RequisitionMatrixBoardController::class,'matrixBoardProductionApproved']);
//                Route::get('vendor-requisition/{id}', [RequisitionMatrixBoardController::class,'matrixBoardProductionToRequisition']);
            });
        });
    });

    Route::prefix('/pos')->group(function() {
        Route::get('check/invoice-mode', [PosController::class,'checkInvoiceMode'])->name('pos_check_invoice_mode');
        Route::post('inline-update', [PosController::class,'invoiceUpdate'])->name('pos_inline_update');
        Route::get('invoice-details', [PosController::class,'invoiceDetails'])->name('pos_invoice_details');
        Route::get('sales-complete/{id}', [PosController::class,'posSalesComplete'])->name('pos_sales_complete');
    });

    Route::prefix('report')->group(function() {
        Route::prefix('stock-item')->group(function() {
            Route::get('history', [StockItemController::class,'stockItemHistory']);
        });
        Route::get('daily/sales', [SalesController::class,'dailySalesReport'])->name('daily_sales_report');
//        Route::get('matrix/warehouse-xlsx', [ProductionReportController::class,'matrixWarehouseXlsx'])->name('matrix_warehouse_xlsx');
//        Route::get('matrix/warehouse-pdf', [ProductionReportController::class,'matrixWarehousePdf'])->name('matrix_warehouse_pdf');
    });
});


Route::get('stock-item/download', [StockItemController::class,'stockItemDownload'])->middleware([LogRequestResponse::class])->name('stock_item_download');
