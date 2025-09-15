<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Core\App\Http\Middleware\LogRequestResponse;
use Modules\Medicine\App\Http\Controllers\PurchaseController;

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

/*Route::middleware(['auth:sanctum'])->prefix('v1')->name('api.')->group(function () {
    Route::get('medicine', fn (Request $request) => $request->user())->name('medicine');
});*/

Route::prefix('/medicine')->middleware([HeaderAuthenticationMiddleware::class,LogRequestResponse::class])->group(function() {
    Route::apiResource('/purchase', PurchaseController::class);
//    Route::get('/purchase/edit/{id}', [PurchaseController::class,'edit'])->name('get_edit_purchase');
//    Route::get('/purchase/copy/{id}', [PurchaseController::class,'purchaseCopy'])->name('get_purchase_copy');
//    Route::get('/purchase/approve/{id}', [PurchaseController::class,'approve'])->name('approve_purchase');

});

