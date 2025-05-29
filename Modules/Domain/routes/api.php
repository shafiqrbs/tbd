<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Domain\App\Http\Controllers\B2bController;
use Modules\Domain\App\Http\Controllers\BranchController;
use Modules\Domain\App\Http\Controllers\DomainConfigController;
use Modules\Domain\App\Http\Controllers\DomainController;

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


Route::prefix('/domain')->middleware(array(HeaderAuthenticationMiddleware::class))->group(function() {
    Route::apiResource('/global', DomainController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::apiResource('/setting', DomainController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->names([
            'index' => 'domain.setting.index',
            'store' => 'domain.setting.store',
            'show' => 'domain.setting.show',
            'update' => 'domain.setting.update',
            'destroy' => 'domain.setting.destroy',
        ]);
    Route::prefix('/manage')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
        Route::prefix('/branch')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
            Route::get('/', [BranchController::class,'domainForBranch'])->name('get_domain_for_branch');
            Route::post('create', [BranchController::class,'store'])->name('store_branch');
            Route::post('price/update', [BranchController::class,'priceUpdate'])->name('branch_price_update');
            Route::post('category/update', [BranchController::class,'categoryUpdate'])->name('branch_category_update');
        });
        Route::post('/sub-domain/{id}', [DomainController::class,'subDomain'])->name('sub_domain');
        Route::post('/inventory/{id}', [DomainController::class,'inventorySetting'])->name('domain_inventory_setting');
    });

    Route::prefix('config')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
        Route::get('/', [DomainConfigController::class,'domainConfig'])->name('domain_config');
        Route::post('domain/{id}', [DomainConfigController::class,'domainConfigurationForm'])->name('domain_config_form');
        Route::post('inventory/{id}', [DomainConfigController::class,'inventoryConfig'])->name('domain_inventory_config');
        Route::post('inventory-product/{id}', [DomainConfigController::class,'inventoryProductConfig'])->name('domain_product_config');
        Route::post('inventory-sales/{id}', [DomainConfigController::class,'inventorySalesConfig'])->name('domain_config');
        Route::post('inventory-purchase/{id}', [DomainConfigController::class,'inventoryPurchaseConfig'])->name('domain_config');
        Route::post('inventory-discount/{id}', [DomainConfigController::class,'inventoryDiscountConfig'])->name('domain_config');
        Route::post('inventory-pos/{id}', [DomainConfigController::class,'inventoryPosConfig'])->name('domain_config');
        Route::post('inventory-vat/{id}', [DomainConfigController::class,'inventoryVatConfig'])->name('domain_config');
        Route::post('inventory-requisition/{id}', [DomainConfigController::class,'inventoryRequisitionConfig'])->name('domain_config');
        Route::post('accounting/{id}', [DomainConfigController::class,'accountingConfig'])->name('domain_config');
        Route::post('production/{id}', [DomainConfigController::class,'productionConfig'])->name('domain_config');
        Route::post('gst-vat/{id}', [DomainConfigController::class,'vatConfig'])->name('domain_config');
        Route::get('reset/{id}', [DomainConfigController::class,'resetConfig'])->name('domain_config');
    });

    Route::prefix('restore')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
        Route::get('{id}', [DomainController::class,'restoreData'])->name('domain_restore');
        Route::get('reset/{domain}', [DomainController::class,'resetData'])->name('domain_reset');
        Route::get('delete/{id}', [DomainController::class,'deleteData'])->name('domain_delete');
    });

    Route::prefix('b2b')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
        Route::get('domain', [B2bController::class,'index'])->name('b2b_domain');
        Route::get('users', [DomainController::class,'users'])->name('domain_users');
        Route::get('sub-domain-users', [DomainController::class,'subDomainUsers'])->name('domain_users');
        Route::get('impersonate/{domain}/{user}', [B2bController::class, 'impersonate'])->name('auth');
        Route::post('inline-update/domain', [B2bController::class,'domainInlineUpdate'])->name('domain_inline_update');
        Route::post('inline-update/category', [B2bController::class,'domainInlineUpdateCategory'])->name('domain_inline_update_category');
        Route::post('inline-update/product', [B2bController::class,'domainInlineUpdateProduct'])->name('domain_inline_update_product');
        Route::get('sub-domain', [B2bController::class,'b2bSubDomain'])->name('b2b_sub_domain');
        Route::post('sub-domain/category', [B2bController::class,'categoryWiseProductManage'])->name('category_wise_product_insert');
        Route::get('sub-domain/setting/{id}', [B2bController::class,'b2bSubDomainSetting'])->name('b2b_sub_domain_setting');
        Route::get('sub-domain/category/{id}', [B2bController::class,'b2bSubDomainCategory'])->name('b2b_sub_domain_category');
        Route::get('category-wise/price-update/{id}', [B2bController::class,'b2bCategoryWisePriceUpdate'])->name('b2b_category_wise_price_update');
        Route::get('sub-domain/product/{id}', [B2bController::class,'b2bSubDomainProduct'])->name('b2b_sub_domain_product');
    });
});
