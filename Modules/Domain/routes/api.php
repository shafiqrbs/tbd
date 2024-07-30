<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

    Route::prefix('restore')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
        Route::get('/reset', [DomainController::class,'resetData'])->name('domain_reset');
        Route::get('/delete', [DomainController::class,'deleteData'])->name('domain_delete');
    });
});
