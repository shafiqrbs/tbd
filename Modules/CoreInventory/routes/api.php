<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
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

Route::middleware(['auth:sanctum'])->middleware([LogRequestResponse::class])->prefix('v1')->name('api.')->group(function () {
    Route::get('coreinventory', fn (Request $request) => $request->user())->name('coreinventory');
});
