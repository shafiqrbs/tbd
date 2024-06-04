<?php

use Illuminate\Support\Facades\Route;
use Modules\NbrVatTax\App\Http\Controllers\NbrVatTaxController;

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
    Route::resource('nbrvattax', NbrVatTaxController::class)->names('nbrvattax');
});
