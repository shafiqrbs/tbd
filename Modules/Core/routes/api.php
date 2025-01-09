<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Core\App\Http\Controllers\CoreController;
use Modules\Core\App\Http\Controllers\CustomerController;
use Modules\Core\App\Http\Controllers\FileUploadController;
use Modules\Core\App\Http\Controllers\LocationController;
use Modules\Core\App\Http\Controllers\SettingController;
use Modules\Core\App\Http\Controllers\SiteMapController;
use Modules\Core\App\Http\Controllers\UserController;
use Modules\Core\App\Http\Controllers\VendorController;
use Modules\Core\App\Http\Controllers\WarehouseController;
use Modules\Inventory\App\Http\Controllers\InventoryController;

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


Route::prefix('/core/select')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/user', [CoreController::class,'user'])->name('user_autosearch');
    Route::get('/executive', [CoreController::class,'executive'])->name('executive_autosearch');
    Route::get('/vendor', [CoreController::class,'vendor'])->name('vendor_autosearch');
    Route::get('/customer', [CoreController::class,'customer'])->name('customer_autosearch');
    Route::get('/location', [CoreController::class,'location'])->name('location_autosearch');
    Route::get('/setting', [CoreController::class,'settingDropdown'])->name('core_setting_dropdown');
    Route::get('/setting-type', [CoreController::class,'settingTypeDropdown'])->name('core_setting_type_dropdown');
    Route::get('/countries', [CoreController::class,'countriesDropdown'])->name('core_countries_dropdown');
});

Route::prefix('/core')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {

    Route::get('/customer/details', [CustomerController::class,'details'])->name('customer_details');
    Route::get('/customer/local-storage', [CustomerController::class,'localStorage'])->name('customer_local_storage');

    Route::get('/vendor/details', [VendorController::class,'details'])->name('vendor_details');
    Route::get('/vendor/local-storage', [VendorController::class,'localStorage'])->name('vendor_local_storage');

    Route::get('/user/local-storage', [UserController::class,'localStorage'])->name('user_local_storage');

    Route::post('/user/image-inline/{id}', [UserController::class,'updateImage'])->name('core_user_update_image');

    Route::apiResource('user', UserController::class)->middleware([HeaderAuthenticationMiddleware::class]);

    Route::apiResource('location', LocationController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::apiResource('customer', CustomerController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::apiResource('vendor', VendorController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::apiResource('warehouse', WarehouseController::class)->middleware([HeaderAuthenticationMiddleware::class]);

    Route::apiResource('setting', SettingController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->names([
            'index' => 'core.setting.index',
            'store' => 'core.setting.store',
            'show' => 'core.setting.show',
            'update' => 'core.setting.update',
            'destroy' => 'core.setting.destroy',
        ]);
    ;
    Route::get('/setting/inline-status/{id}', [SettingController::class,'inlineStatus'])->name('core_setting_inline_status');

    Route::get('/file-upload', [FileUploadController::class,'index'])->name('index_file_upload');
    Route::get('/file-upload/process', [FileUploadController::class,'fileProcessToDB'])->name('upload_file_process');
    Route::post('/file-upload/store', [FileUploadController::class,'store'])->name('store_file_upload');
}
);

