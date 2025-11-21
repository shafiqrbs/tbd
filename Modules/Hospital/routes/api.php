<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Hospital\App\Http\Controllers\BillingController;
use Modules\Hospital\App\Http\Controllers\CategoryController;
use Modules\Hospital\App\Http\Controllers\EpharamaController;
use Modules\Hospital\App\Http\Controllers\HospitalController;

use Modules\Hospital\App\Http\Controllers\InvestigationController;
use Modules\Hospital\App\Http\Controllers\IpdController;
use Modules\Hospital\App\Http\Controllers\LabInvestigationController;
use Modules\Hospital\App\Http\Controllers\MedicineDosageController;
use Modules\Hospital\App\Http\Controllers\OpdController;
use Modules\Hospital\App\Http\Controllers\ParticularController;
use Modules\Hospital\App\Http\Controllers\ParticularModeController;
use Modules\Hospital\App\Http\Controllers\ParticularTypeController;
use Modules\Hospital\App\Http\Controllers\PatientWaiverController;
use Modules\Hospital\App\Http\Controllers\PharmacyController;
use Modules\Hospital\App\Http\Controllers\PrescriptionController;
use Modules\Hospital\App\Http\Controllers\ProductController;
use Modules\Hospital\App\Http\Controllers\RefundController;
use Modules\Hospital\App\Http\Controllers\ReportsController;
use Modules\Hospital\App\Http\Controllers\SettingController;
use Modules\Hospital\App\Http\Controllers\TreatmentMedicineController;


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
    Route::get('/location', [HospitalController::class,'locationDropdown'])->name('location_dropdown');
    Route::get('/category', [HospitalController::class,'categoryDropdown'])->name('category_dropdown');
    Route::get('/bymeal', [HospitalController::class,'mealDropdown'])->name('meal_dropdown');
    Route::get('/investigation', [HospitalController::class,'investigationDropdown'])->name('investigation_dropdown');
    Route::get('/dosage', [HospitalController::class,'dosageDropdown'])->name('dosage_dropdown');
    Route::get('/particular-content', [HospitalController::class,'particularContentDropdown'])->name('particular_content_dropdown');
    Route::get('/module', [HospitalController::class,'particularModuleDropdown'])->name('particular_module_dropdown');
    Route::get('/modules', [HospitalController::class,'particularModuleChildDropdown'])->name('particular_module_dropdown');
    Route::get('/mode', [HospitalController::class,'particularModeDropdown'])->name('particular_module_dropdown');
    Route::get('/particular-type', [HospitalController::class,'particularTypeDropdown'])->name('particular_particular_dropdown');
    Route::get('/particular-master-type', [HospitalController::class,'particularMasterTypeDropdown'])->name('particular_particular_dropdown');
    Route::get('/particulars', [HospitalController::class,'particularTypeChildDropdown'])->name('particular_particular_dropdown');
    Route::get('/particular', [HospitalController::class,'particularDropdown'])->name('particular_particular_dropdown');
    Route::get('/allopdroom', [HospitalController::class,'opdAllOpdDropdown'])->name('particular_particular_dropdown');
    Route::get('/opdroom', [HospitalController::class,'opdRoomDropdown'])->name('particular_particular_dropdown');
    Route::get('/opdreferredroom', [HospitalController::class,'opdReferredRoomDropdown'])->name('particular_particular_dropdown');
    Route::get('/medicine', [HospitalController::class,'medicineDropdown'])->name('medicine_dropdown');
    Route::get('/medicine-generic', [HospitalController::class,'medicineGenericDropdown'])->name('medicine_generic_dropdown');
});

Route::prefix('/hospital')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
    Route::get('/config', [HospitalController::class,'domainHospitalConfig'])->name('domain_hospital_config');
    Route::get('/patient-search', [HospitalController::class,'patientSearch'])->name('patient_search');
    Route::get('/room-cabin', [HospitalController::class,'roomCabin'])->name('patient_search');
    Route::get('/medicine-import', [HospitalController::class,'insertUpazilaDistrict'])->name('insert_medicine_stock');
    Route::get('/medicine-process', [HospitalController::class,'insertMedicineStockProcess'])->name('insert_medicine_stock_process');
    Route::get('/particular', [SettingController::class,'particularModuleDropdown'])->name('particular_module_dropdown');
    Route::get('/setting', [SettingController::class,'particularModuleDropdown'])->name('particular_module_dropdown');
    Route::get('/mode/matrix', [HospitalController::class,'modeMatrix'])->name('particular_module_dropdown');
    Route::post('/mode/matrix-ordering', [HospitalController::class,'matrixUpdateOrdering'])->name('matrixUpdateOrdering');
    Route::post('/mode/matrix-inline-update/{id}', [HospitalController::class,'matrixInlineUpdate'])->name('matrixUpdateOrdering');
    Route::get('/setting/matrix', [HospitalController::class,'settingMatrix'])->name('particular_module_dropdown');
    Route::get('/setting', [SettingController::class,'particularModuleDropdown'])->name('particular_module_dropdown');
    Route::get('visiting-room', [OpdController::class,'getVisitingRooms'])->name('getVisitingRooms');
    Route::get('send-to-prescription/{id}', [OpdController::class,'sendPrescription'])->name('send_prescription');
    Route::post('/opd/referred/{id}', [OpdController::class,'referred'])->name('prescription_referred');
    Route::get('/core/particular/doctor-nurse', [ParticularController::class, 'doctorNurseStaff'])->name('particular.doctorNurseStaff');
    Route::get('/core/userinfo/{id}', [HospitalController::class,'userInfo'])->name('userInfo');
    Route::post('/core/health-share', [HospitalController::class,'healthShare'])->name('healthShare');
    Route::get('/core/store-user', [HospitalController::class,'storeUser'])->name('storeUser');
    Route::post('/core/store-user/inline-update/{id}', [HospitalController::class,'storeUserInlineUpdate'])->name('storeUserInlineUpdate');
    Route::prefix('core')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
        Route::get('/user-import', [HospitalController::class,'userImport'])->name('user-import');
        Route::apiResource('particular', ParticularController::class)
            ->middleware([HeaderAuthenticationMiddleware::class])
            ->names([
                'index' => 'particular.index',
                'store' => 'particular.store',
                'show' => 'particular.show',
                'update' => 'particular.update',
                'destroy' => 'particular.destroy',
            ]);
        Route::post('/particular/inline-update/{id}', [ParticularController::class,'particularInlineUpdate'])->name('particular_inline_update');
        Route::post('/particular/ordering', [ParticularController::class,'updateOrdering'])->name('particular_ordering_update');

        Route::apiResource('particular-type', ParticularTypeController::class)
            ->middleware([HeaderAuthenticationMiddleware::class])
            ->names([
                'index' => 'particular-type.index',
                'store' => 'particular-type.store',
                'show' => 'particular-type.show',
                'update' => 'particular-type.update',
                'destroy' => 'particular-type.destroy',
            ]);
        Route::apiResource('category', CategoryController::class)
            ->middleware([HeaderAuthenticationMiddleware::class])
            ->names([
                'index' => 'category.index',
                'store' => 'category.store',
                'show' => 'category.show',
                'update' => 'category.update',
                'destroy' => 'category.destroy',
            ]);
        Route::apiResource('investigation', InvestigationController::class)
            ->middleware([HeaderAuthenticationMiddleware::class])
            ->names([
                'index' => 'investigation.index',
                'store' => 'investigation.store',
                'show' => 'investigation.show',
                'update' => 'investigation.update',
                'destroy' => 'investigation.destroy',
            ]);
        Route::apiResource('treatment', TreatmentMedicineController::class)
            ->middleware([HeaderAuthenticationMiddleware::class])
            ->names([
                'index' => 'treatment.index',
                'store' => 'treatment.store',
                'show' => 'treatment.show',
                'update' => 'treatment.update',
                'destroy' => 'treatment.destroy',
            ]);

        Route::apiResource('dosage', MedicineDosageController::class)
            ->middleware([HeaderAuthenticationMiddleware::class])
            ->names([
                'index' => 'dosage.index',
                'store' => 'dosage.store',
                'show' => 'dosage.show',
                'update' => 'dosage.update',
                'destroy' => 'dosage.destroy',
            ]);

        Route::apiResource('/particular-mode', ParticularModeController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    });
    Route::post('/setting-matrix/create', [HospitalController::class,'settingMatrixCreate'])->name('particular_module_dropdown');
    Route::get('/prescription/patient/{id}/{prescription}', [PrescriptionController::class,'patientPrescription'])->name('patient_prescription');
    Route::apiResource('opd', OpdController::class)->middleware([HeaderAuthenticationMiddleware::class]);
    Route::patch('/opd/vital-update/{id}', [OpdController::class, 'vitalUpdate'])->name('vitalUpdate');
    Route::patch('/opd/patient-waver/{id}', [OpdController::class, 'patientWaver'])->name('patientWaver');
    Route::post('/opd/police-case/{id}', [OpdController::class, 'policeCase'])->name('patientWaver');
    Route::apiResource('prescription', PrescriptionController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->names([
            'index' => 'prescription.index',
            'store' => 'prescription.store',
            'show' => 'prescription.show',
            'update' => 'prescription.update',
            'destroy' => 'prescription.destroy',
        ]);
    Route::get('/prescription/vital/{id}', [PrescriptionController::class, 'vitalCheck'])->name('vitalCheck');

    Route::apiResource('ipd', IpdController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->names([
            'index' => 'ipd.index',
            'store' => 'ipd.store',
            'show' => 'ipd.show',
            'update' => 'ipd.update',
            'destroy' => 'ipd.destroy',
        ]);
    Route::get('/ipd/view/{id}', [IpdController::class, 'ipdAdmissionShow'])->name('find_bill');
    Route::get('/ipd/efresh-order/{id}', [IpdController::class,'efreshOrder'])->name('ipd_efreshOrder');
    Route::post('/ipd/patient-chart/{id}', [IpdController::class,'patientChart'])->name('ipd_data_process');
    Route::post('/ipd/data-process/{id}', [IpdController::class,'ipdDataProcess'])->name('ipd_data_process');
    Route::get('/ipd/transaction/{id}', [IpdController::class,'transaction'])->name('ipd_data_transaction');
    Route::get('/ipd/release/{id}/{mode}', [IpdController::class,'release'])->name('ipd_data_transaction');

    Route::apiResource('epharma',
        EpharamaController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->names([
            'index' => 'epharma.index',
            'store' => 'epharma.store',
            'show' => 'epharma.show',
            'update' => 'epharma.update',
            'destroy' => 'epharma.destroy',
        ]);

    Route::apiResource('medicine',
        ProductController::class)
        ->middleware([HeaderAuthenticationMiddleware::class])
        ->names([
            'index' => 'medicine.index',
            'store' => 'medicine.store',
            'show' => 'medicine.show',
            'update' => 'medicine.update',
            'destroy' => 'medicine.destroy',
        ]);
    Route::get('/epharma/barcode/{barcode}', [EpharamaController::class,'patientPrescription'])->name('patient_prescription');
    Route::prefix('lab-investigation')->name('lab-investigation.')->group(function () {
        Route::get('/sample-confirm/{id}', [LabInvestigationController::class,'barcodeConfirm'])->name('barcodeConfirm_update');
        Route::get('test-reports', [LabInvestigationController::class, 'labReports'])->name('lab_reports');
        Route::apiResource('', LabInvestigationController::class)->parameters(['' => 'id']);
        Route::post('/report/inline-update/{id}', [LabInvestigationController::class,'inlineUpdate'])->name('inline_update');
        Route::get('{id}/report/{reportId}', [LabInvestigationController::class, 'report'])->name('report');
        Route::get('/print/{id}', [LabInvestigationController::class, 'print'])->name('print');
    });

    Route::prefix('billing')->name('billing')->group(function () {
        Route::get('/final-bill', [BillingController::class, 'findBill'])->name('find_bill');
        Route::apiResource('', BillingController::class)->parameters(['' => 'id']);
        Route::get('{id}/final-bill', [BillingController::class, 'finalBillDetails'])->name('find_bill');
        Route::get('{id}/payment/{transactionId}', [BillingController::class, 'transaction'])->name('transaction');
    });

    Route::prefix('refund')->name('refund')->group(function () {
        Route::apiResource('', RefundController::class)->parameters(['' => 'id']);
        Route::get('{id}/payment/{transactionId}', [RefundController::class, 'transaction'])->name('transaction');
    });

    Route::prefix('patient-waiver')->name('patient_waiver')->group(function () {
        Route::get('invoice', [PatientWaiverController::class, 'invoice'])->name('patient_waiver_invoice');
        Route::apiResource('', PatientWaiverController::class)->parameters(['' => 'id']);
        Route::get('process/{id}', [PatientWaiverController::class, 'process'])->name('patient_waiver_process');
        Route::get('approve/{id}', [PatientWaiverController::class, 'approve'])->name('patient_waiver_process');
        Route::get('details/{id}', [PatientWaiverController::class, 'patientDetails'])->name('patient_waiver_process');
        Route::get('print/{id}', [PatientWaiverController::class, 'print'])->name('patient_waiver_process');
    });

    Route::prefix('reports')->middleware([HeaderAuthenticationMiddleware::class])->group(function() {
        Route::get('/dashboard-daily-summary', [ReportsController::class,'dailySummary'])->name('dashboard_daily_summary');
    });

    });
