<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Domain\App\Http\Controllers\BranchController;
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

        Route::prefix('branch')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
            Route::get('/', [BranchController::class,'domainForBranch'])->name('get_domain_for_branch');
            Route::post('/create', [BranchController::class,'store'])->name('store_branch');
            Route::post('price/update', [BranchController::class,'priceUpdate'])->name('branch_price_update');
            Route::post('category/update', [BranchController::class,'categoryUpdate'])->name('branch_category_update');
        });

        Route::post('/sub-domain/{id}', [DomainController::class,'subDomain'])->name('sub_domain');
        Route::post('/inventory/{id}', [DomainController::class,'inventorySetting'])->name('domain_inventory_setting');
    });
    Route::prefix('restore')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
        Route::get('/reset', [DomainController::class,'resetData'])->name('domain_reset');
        Route::get('/delete', [DomainController::class,'deleteData'])->name('domain_delete');
    });
});
