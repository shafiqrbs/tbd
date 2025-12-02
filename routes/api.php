<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::POST('/user-login', [LoginController::class,'userLogin'])->name('user_login');


// jwt authentication
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login-tb', [AuthController::class, 'loginTB']);
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

