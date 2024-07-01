<?php

use Illuminate\Support\Facades\Route;
use Modules\CoreInventory\App\Http\Controllers\CoreInventoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([], function () {
    Route::resource('coreinventory', CoreInventoryController::class)->names('coreinventory');
});
