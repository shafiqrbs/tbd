<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Core\App\Http\Middleware\LogRequestResponse;
use Modules\Production\App\Http\Controllers\ConfigController;
use Modules\Production\App\Http\Controllers\ProductionInhouseController;
use Modules\Production\App\Http\Controllers\ProductionBatchController;
use Modules\Production\App\Http\Controllers\ProductionIssueController;
use Modules\Production\App\Http\Controllers\ProductionRecipeController;
use Modules\Production\App\Http\Controllers\ProductionRecipeItemsController;
use Modules\Production\App\Http\Controllers\ProductionReportController;
use Modules\Production\App\Http\Controllers\SettingController;

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

Route::prefix('/production/select')->middleware([HeaderAuthenticationMiddleware::class,LogRequestResponse::class])->group(function() {
    Route::get('/setting-type', [SettingController::class,'settingTypeDropdown'])->name('pro_setting_type_dropdown');
    Route::get('/config-dropdown', [ConfigController::class,'configDropdown'])->name('pro_config_dropdown');
    Route::get('/items-dropdown', [ProductionRecipeItemsController::class,'dropdown'])->name('pro_item_dropdown');
    Route::get('/batch-dropdown', [ProductionBatchController::class,'batchDropdown'])->name('pro_batch_dropdown');

});

Route::prefix('/production')->middleware([HeaderAuthenticationMiddleware::class,LogRequestResponse::class])->group(function() {

    Route::apiResource('issue', ProductionIssueController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->names([
            'index' => 'production.issue.index',
            'store' => 'production.issue.store',
            'show' => 'production.issue.show',
            'update' => 'production.issue.update',
            'destroy' => 'production.issue.destroy'
        ]);


    Route::apiResource('setting', SettingController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->names([
            'index' => 'production.setting.index',
            'store' => 'production.setting.store',
            'show' => 'production.setting.show',
            'update' => 'production.setting.update',
            'destroy' => 'production.setting.destroy'
        ]);


    Route::get('/config', [ConfigController::class,'show'])->name('pro_config_show');
    Route::patch('/config-update', [ConfigController::class,'update'])->name('pro_config_update');
    Route::get('/user-warehouse', [ConfigController::class,'userWarehouse'])->name('pro_user_warehouse');

    Route::post('/inline-update-value-added', [ProductionRecipeItemsController::class,'inlineUpdateValueAdded'])->name('pro_inline_update_value_added');
    Route::post('/inline-update-element-status', [ProductionRecipeItemsController::class,'inlineUpdateElementStatus'])->name('pro_inline_update_element_status');
    Route::post('/recipe-items-process', [ProductionRecipeItemsController::class,'updateProcess'])->name('pro_item_update_process');

    Route::prefix('production-item-amendments')->group(function() {
        Route::get('', [ProductionRecipeItemsController::class, 'amendmentIndex']);
        Route::post('{id}', [ProductionRecipeItemsController::class,'amendmentProcess']);

    });

    Route::post('recipe-items/update-warehouse', [ProductionRecipeItemsController::class, 'updateWarehouse'])->name('pro_item_update_warehouse');

    Route::apiResource('recipe-items', ProductionRecipeItemsController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->names([
            'index' => 'production.items.index',
            'store' => 'production.items.store',
            'show' => 'production.items.show',
            'update' => 'production.items.update',
            'destroy' => 'production.items.destroy'
        ]);
    ;

    Route::get('generate/finish-goods/xlsx', [ProductionRecipeItemsController::class,'finishGoodsXlsxGenerate'])->name('get_finish_goods_xlsx_generate');


    Route::apiResource('recipe', ProductionRecipeController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->names([
            'index'     => 'production.recipe.index',
            'store'     => 'production.recipe.store',
            'show'      => 'production.recipe.show',
            'update'    => 'production.recipe.update',
            'destroy'   => 'production.recipe.destroy'
        ]);
    ;


    Route::apiResource('batch', ProductionBatchController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->names([
            'index'     => 'production.batch.index',
            'store'     => 'production.batch.store',
            'show'      => 'production.batch.show',
            'update'    => 'production.batch.update',
            'destroy'   => 'production.batch.destroy'
        ]);
    ;
    Route::post('/batch/create-batch-item', [ProductionBatchController::class,'insertBatchItem'])->name('production_insert_batch_item');
    Route::post('/batch/item/inline-quantity-update', [ProductionBatchController::class,'batchItemQuantityInlineUpdate'])->name('production_batch_item_quantity_update');
    Route::post('/batch/raw-item/inline-quantity-update', [ProductionBatchController::class,'batchRawItemQuantityInlineUpdate'])->name('production_batch_raw_item_quantity_update');
    Route::post('/batch/approve/{id}', [ProductionBatchController::class,'batchApproved'])->name('production_batch_approve');
    Route::post('/batch/confirm-receive/{id}', [ProductionBatchController::class,'batchConfirmReceive'])->name('production_batch_receive_confirm');
    Route::get('/batch/recipe/{id}', [ProductionBatchController::class,'getBatchRecipe'])->name('production_batch_wise_recipe');;
    Route::delete('/batch/item-delete/{id}', [ProductionBatchController::class,'batchItemDelete'])->name('production_batch_item_delete');;


    Route::prefix('restore')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
        Route::get('/item', [ProductionRecipeItemsController::class,'restore'])->name('pro_item_restore');
    });

    Route::prefix('report')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
        Route::get('issue', [ProductionReportController::class,'issueReport'])->name('issue_report');
        Route::get('issue-xlsx', [ProductionReportController::class,'issueGenerateXlsx'])->name('issue_generate_xlsx');
        Route::get('issue-pdf', [ProductionReportController::class,'issueGeneratePdf'])->name('issue_generate_pdf');
        Route::get('matrix/warehouse', [ProductionReportController::class,'matrixWarehouse'])->name('matrix_warehouse');
        Route::get('matrix/warehouse-xlsx', [ProductionReportController::class,'matrixWarehouseXlsx'])->name('matrix_warehouse_xlsx');
        Route::get('matrix/warehouse-pdf', [ProductionReportController::class,'matrixWarehousePdf'])->name('matrix_warehouse_pdf');
    });

});

Route::get('finish-goods/download', [ProductionRecipeItemsController::class,'finishGoodsDownload'])->middleware([LogRequestResponse::class])->name('finish_goods_download');
Route::get('issue-xlsx/download/{type}', [ProductionReportController::class,'issueReportDownload'])->name('issue_report_download');
Route::get('matrix-report/download/{type}', [ProductionReportController::class,'matrixReportDownload'])->name('matrix_report_download');

