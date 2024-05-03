<?php

use Illuminate\Support\Facades\Route;
use Modules\NfcCard\App\Http\Controllers\NfcCardController;
use Modules\NfcCard\App\Http\Controllers\RegistrationController;

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

Route::group([],
    function () {
        Route::resource('nfccard', NfcCardController::class)->names('nfccard');
        Route::get('registration', [RegistrationController::class, 'index']);
        Route::get('registration/create', [RegistrationController::class, 'createUserForm']);
        Route::post('registration/store', [RegistrationController::class, 'UserForm'])->name('validate.form');
    });
