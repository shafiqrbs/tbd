<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\AppsApi\App\Http\Controllers\AppsApiController;
use Modules\AppsApi\App\Http\Controllers\GlobalApiController;
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




/*Route::middleware(['auth:sanctum'])->get('/globalapi', function (Request $request) {
    Route::post('/index', [\Modules\GlobalApi\Http\Controllers\GlobalApiController::class,'index'])->name('index');
});*/

Route::prefix('/apps')->middleware([LogRequestResponse::class])->group(function() {
    Route::get('/splash', [GlobalApiController::class,'splash'])->name('terminal_splash');
});

Route::prefix('/terminal')->middleware([LogRequestResponse::class])->group(function() {

//    Route::post('/sign-in', [GlobalApiController::class,'signIn'])->name('sign_in');
//    Route::get('/customer', [GlobalApiController::class,'customer'])->name('terminal_customer');
//    Route::get('/domain', [GlobalApiController::class,'domain'])->name('terminal_doamin');
//    Route::get('/medicine-brand', [GlobalApiController::class,'medicineBrand'])->name('terminal_medicine_brand');
    Route::get('/domain', [GlobalApiController::class,'index'])->name('api_index');
/*    Route::get('/customer', [GlobalApiController::class,'customer'])->name('api_customer');
    Route::get('/splash', [GlobalApiController::class,'splash'])->name('terminal_splash');
    Route::get('/index', [AppsApiController::class,'index'])->name('terminal_index');*/
});

