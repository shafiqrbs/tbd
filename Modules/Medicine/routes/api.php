<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Core\App\Http\Middleware\LogRequestResponse;
use Modules\Medicine\App\Http\Controllers\MedicineController;
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

Route::prefix('/medicine/select')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/bymeal', [MedicineController::class,'medicineBymealDropdown'])->name('meal_dropdown');
    Route::get('/dosage', [MedicineController::class,'medicineDosageDropdown'])->name('dosage_dropdown');
    Route::get('/generic', [MedicineController::class,'medicineGenericDropdown'])->name('medicine_generic_dropdown');
    Route::get('/company', [MedicineController::class,'medicineCompanyDropdown'])->name('medicine_generic_dropdown');
});

Route::prefix('/pharmacy')->middleware([HeaderAuthenticationMiddleware::class,LogRequestResponse::class])->group(function() {
    Route::apiResource('/purchase', PurchaseController::class);
    Route::apiResource('medicine',
        MedicineController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->names([
            'index' => 'medicine.index',
            'store' => 'medicine.store',
            'show' => 'medicine.show',
            'update' => 'medicine.update',
            'destroy' => 'medicine.destroy',
        ]);
    Route::post('/medicine/inline-update/{id}', [MedicineController::class,'medicineInlineUpdate'])->name('particular_inline_update');
});

