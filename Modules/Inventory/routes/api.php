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
use Modules\Inventory\App\Http\Controllers\RequisitionController;
use Modules\Inventory\App\Http\Controllers\RequisitionMatrixBoardController;
use Modules\Inventory\App\Http\Controllers\SalesController;
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
    Route::get('generate/stock-item/xlsx', [StockItemController::class,'stockItemXlsxGenerate'])->name('get_stock_item_xlsx_generate');

    Route::apiResource('/sales', SalesController::class);
    Route::get('/sales/edit/{id}', [SalesController::class,'edit'])->name('get_edit_sales');
    Route::get('/sales/copy/{id}', [SalesController::class,'salesCopy'])->name('get_sales_copy');
    Route::get('/sales/domain-customer/{id}', [SalesController::class,'domainCustomerSales'])->name('get_domain_customer_sales');
    Route::get('/sales/not-domain-customer/{id}', [SalesController::class,'notDomainCustomerSales'])->name('get_not_domain_customer_sales');
    Route::apiResource('/invoice-batch', InvoiceBatchController::class);
    Route::apiResource('/invoice-batch-transaction', InvoiceBatchTransactionControllerAlias::class);


    Route::apiResource('/purchase', PurchaseController::class);
    Route::get('/purchase/edit/{id}', [PurchaseController::class,'edit'])->name('get_edit_purchase');
    Route::get('/purchase/copy/{id}', [PurchaseController::class,'purchaseCopy'])->name('get_purchase_copy');
    Route::get('/purchase/approve/{id}', [PurchaseController::class,'approve'])->name('approve_purchase');

    Route::apiResource('/purchase-item', PurchaseItemController::class);

    Route::apiResource('/opening-stock', OpeningStockController::class);
    Route::post('/opening-stock/inline-update', [OpeningStockController::class,'inlineUpdate'])->name('opening_stock_inline_update');
  //  Route::post('/inventory/branch-management', [BranchManagementController::class,'branchManagement'])->name('branch_management_update');

    Route::apiResource('/requisition', RequisitionController::class);
    Route::apiResource('/warehouse-issue', WarehouseIssueController::class);
    Route::prefix('/requisition')->group(function() {
        Route::get('approve/{id}', [RequisitionController::class,'approve'])->name('requisition_approve');
        Route::prefix('matrix/board')->group(function() {
            Route::get('{id}', [RequisitionMatrixBoardController::class,'matrixBoard'])->name('requisition_matrix_board');
            Route::post('create', [RequisitionMatrixBoardController::class,'store'])->name('requisition_matrix_board_create');
            Route::post('quantity-update', [RequisitionMatrixBoardController::class,'matrixBoardQuantityUpdate'])->name('requisition_matrix_board_quantity_update');
            Route::post('batch-generate/{id}', [RequisitionMatrixBoardController::class,'matrixBoardBatchGenerate'])->name('requisition_matrix_board_batch_generate');
        });
    });

    Route::prefix('/pos')->group(function() {
        Route::get('check/invoice-mode', [PosController::class,'checkInvoiceMode'])->name('pos_check_invoice_mode');
        Route::post('inline-update', [PosController::class,'invoiceUpdate'])->name('pos_inline_update');
        Route::get('invoice-details', [PosController::class,'invoiceDetails'])->name('pos_invoice_details');
        Route::get('sales-complete/{id}', [PosController::class,'posSalesComplete'])->name('pos_sales_complete');
    });

    Route::prefix('report')->group(function() {
        Route::get('daily/sales', [SalesController::class,'dailySalesReport'])->name('daily_sales_report');
//        Route::get('matrix/warehouse-xlsx', [ProductionReportController::class,'matrixWarehouseXlsx'])->name('matrix_warehouse_xlsx');
//        Route::get('matrix/warehouse-pdf', [ProductionReportController::class,'matrixWarehousePdf'])->name('matrix_warehouse_pdf');
    });
});


Route::get('stock-item/download', [StockItemController::class,'stockItemDownload'])->middleware([LogRequestResponse::class])->name('stock_item_download');
