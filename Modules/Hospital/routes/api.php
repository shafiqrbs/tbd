<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Hospital\App\Http\Controllers\HospitalController;

use Modules\Hospital\App\Http\Controllers\OpdController;
use Modules\Hospital\App\Http\Controllers\ParticularController;
use Modules\Hospital\App\Http\Controllers\ParticularModeController;
use Modules\Hospital\App\Http\Controllers\ParticularTypeController;
use Modules\Hospital\App\Http\Controllers\SettingController;


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

Route::prefix('/hospital/select')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/module', [HospitalController::class,'particularModuleDropdown'])->name('particular_module_dropdown');
    Route::get('/modules', [HospitalController::class,'particularModuleChildDropdown'])->name('particular_module_dropdown');
    Route::get('/mode', [HospitalController::class,'particularModeDropdown'])->name('particular_module_dropdown');
    Route::get('/particular-type', [HospitalController::class,'particularTypeDropdown'])->name('particular_particular_dropdown');
    Route::get('/particular-master-type', [HospitalController::class,'particularMasterTypeDropdown'])->name('particular_particular_dropdown');
    Route::get('/particulars', [HospitalController::class,'particularTypeChildDropdown'])->name('particular_particular_dropdown');
    Route::get('/particular', [HospitalController::class,'particularDropdown'])->name('particular_particular_dropdown');
    Route::get('/medicine', [HospitalController::class,'medicineDropdown'])->name('medicine_dropdown');
    Route::get('/medicine-generic', [HospitalController::class,'medicineGenericDropdown'])->name('medicine_generic_dropdown');

});

Route::prefix('/hospital')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/config', [HospitalController::class,'domainHospitalConfig'])->name('domain_hospital_config');
    Route::get('/medicine-import', [HospitalController::class,'insertMedicineStock'])->name('insert_medicine_stock');
    Route::get('/particular', [SettingController::class,'particularModuleDropdown'])->name('particular_module_dropdown');
    Route::get('/setting', [SettingController::class,'particularModuleDropdown'])->name('particular_module_dropdown');
    Route::get('/setting/matrix', [HospitalController::class,'settingMatrix'])->name('particular_module_dropdown');
    Route::get('/setting', [SettingController::class,'particularModuleDropdown'])->name('particular_module_dropdown');
    Route::apiResource('opd', OpdController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::get('visiting-room', [OpdController::class,'getVisitingRooms'])->name('send_prescription');
    Route::get('send-to-prescription/{id}', [OpdController::class,'sendPrescription'])->name('send_prescription');
    Route::prefix('core')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
        Route::apiResource('particular', ParticularController::class)
            ->middleware([HeaderAuthenticationMiddleware::class])
            ->names([
                'index' => 'particular.index',
                'store' => 'particular.store',
                'show' => 'particular.show',
                'update' => 'particular.update',
                'destroy' => 'particular.destroy',
            ]);
        Route::apiResource('particular-type', ParticularTypeController::class)
            ->middleware([HeaderAuthenticationMiddleware::class])
            ->names([
                'index' => 'particular-type.index',
                'store' => 'particular-type.store',
                'show' => 'particular-type.show',
                'update' => 'particular-type.update',
                'destroy' => 'particular-type.destroy',
            ]);
        Route::apiResource('/particular-mode', ParticularModeController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    });
    Route::post('/setting-matrix/create', [HospitalController::class,'settingMatrixCreate'])->name('particular_module_dropdown');


});
