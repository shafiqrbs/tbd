<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\StockItemModel;
use Ramsey\Collection\Collection;

class PatientPrescriptionMedicineModel extends Model
{
    use HasFactory;

    protected $table = 'hms_patient_prescription_medicine';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function insertPatientMedicine($domain,$id)
    {
        $prescription = PrescriptionModel::find($id);
        $date =  new \DateTime("now");
        $config = $domain['inv_config'];
        $jsonData = json_decode($prescription['json_content']);
        $medicines = ($jsonData->medicines ?? []);

        if (!empty($medicines) && is_array($medicines)) {
            PatientPrescriptionMedicineModel::where('prescription_id', $id)->forceDelete();
            $insertData = collect($jsonData->medicines)
                ->map(function ($medicine) use ($prescription,$date) {
                    $medicineDetails = MedicineDetailsModel::find($medicine->medicine_id);
                    if ($medicineDetails) {
                        $medicineStock = $medicineDetails->medicineStock;
                        $dose_details ='';
                        $dose_details_bn ='';
                        $daily_quantity = 1;
                        $continue_mode = 'sos';

                        $dosage = $medicine->medicine_dosage_id ?? null;
                        if ($dosage) {
                            $dosage = MedicineDosageModel::find($medicine->medicine_dosage_id);
                            $dose_details = $dosage->name;
                            $dose_details_bn = $dosage->name_bn;
                            $continue_mode = $dosage->continue_mode;
                            $daily_quantity = $dosage->quantity;
                        }

                        $by_meal = '';
                        $by_meal_bn = '';
                        $bymeal = $medicine->medicine_bymeal_id ?? null;
                        if ($bymeal) {
                            $bymeal = MedicineDosageModel::find($medicine->medicine_bymeal_id);
                            $by_meal = $bymeal->name;
                            $by_meal_bn = $bymeal->name_bn;
                        }

                        return [

                            'hms_invoice_id' => $prescription->hms_invoice_id,
                            'prescription_id' => $prescription->id,
                            'company' => $medicineDetails->company ?? null, // notice key: medicine_name not medicineName
                            'medicine_name' => $medicineDetails->name ?? null, // notice key: medicine_name not medicineName
                            'generic' => $medicineStock->product->name ?? null, // notice key: medicine_name not medicineName
                            'generic_id' => $medicineStock->product->id ?? null, // notice key: medicine_name not medicineName
                            'medicine_id' => $medicineDetails->id ?? null,
                            'stock_item_id' => $medicineStock->stock_item_id ?? null,
                            'medicine_dosage_id' => $medicine->medicine_dosage_id ?? null,
                            'medicine_bymeal_id' => $medicine->medicine_bymeal_id ?? null,
                            'dose_details' => $dose_details,
                            'dose_details_bn' => $dose_details_bn,
                            'daily_quantity' => $daily_quantity,
                            'by_meal' => $by_meal,
                            'by_meal_bn' => $by_meal_bn,
                            'continue_mode' => $continue_mode,
                            'quantity' => $medicine->opd_quantity ?? 0,
                            'opd_quantity' => $medicine->opd_quantity ?? 0,
                            'ipd_status' => $medicineStock->ipd_status,
                            'opd_status' => $medicineStock->opd_status,
                            'opd_admin_status' => $medicineStock->admin_status,
                            'is_stock' => true,
                            'is_active' => $medicine->is_active ?? 1,
                            'start_date' => $date,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ];

                    }elseif($medicine->generic) {

                        $dose_details = '';
                        $dose_details_bn = '';
                        $daily_quantity = 1;
                        $continue_mode = 'sos';

                        $dosage = $medicine->medicine_dosage_id ?? null;
                        if ($dosage) {
                            $dosage = MedicineDosageModel::find($medicine->medicine_dosage_id);
                            $dose_details = $dosage->name;
                            $dose_details_bn = $dosage->name_bn;
                            $continue_mode = $dosage->continue_mode;
                            $daily_quantity = $dosage->quantity;
                        }

                        $by_meal = '';
                        $by_meal_bn = '';
                        $bymeal = $medicine->medicine_bymeal_id ?? null;
                        if ($bymeal) {
                            $bymeal = MedicineDosageModel::find($medicine->medicine_bymeal_id);
                            $by_meal = $bymeal->name;
                            $by_meal_bn = $bymeal->name_bn;
                        }

                        return [

                            'hms_invoice_id' => $prescription->hms_invoice_id,
                            'prescription_id' => $prescription->id,
                            'company' => $medicine->company ?? null, // notice key: medicine_name not medicineName
                            'medicine_name' => $medicine->generic ?? null, // notice key: medicine_name not medicineName
                            'generic' => $medicine->generic ?? null, // notice key: medicine_name not medicineName
                            'generic_id' =>  null, // notice key: medicine_name not medicineName
                            'stock_item_id' =>  null,
                            'medicine_dosage_id' => $medicine->medicine_dosage_id ?? null,
                            'medicine_bymeal_id' => $medicine->medicine_bymeal_id ?? null,
                            'dose_details' => $dose_details,
                            'dose_details_bn' => $dose_details_bn,
                            'by_meal' => $by_meal,
                            'by_meal_bn' => $by_meal_bn,
                            'continue_mode' => $continue_mode,
                            'daily_quantity' => $daily_quantity,
                            'quantity' => 0,
                            'is_stock' => false,
                            'ipd_status' => false,
                            'opd_status' => false,
                            'is_active' => $medicine->is_active ?? 1,
                            'start_date' => $date,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ];
                    }
                    return null; // explicit
                })
                ->filter() // ✅ remove nulls
                ->values() // ✅ reset array keys (important for upsert)
                ->toArray();

            $requiredKeys = [
                'hms_invoice_id','prescription_id','company','medicine_name','generic',
                'generic_id','medicine_id','stock_item_id','medicine_dosage_id',
                'medicine_bymeal_id','dose_details','dose_details_bn','by_meal','by_meal_bn',
                'continue_mode','quantity','daily_quantity','is_stock','start_date'
            ];
            $insertData = collect($insertData)->map(function ($item) use ($requiredKeys) {
                foreach ($requiredKeys as $key) {
                    if (!array_key_exists($key, $item)) {
                        $item[$key] = null;
                    }
                }
                return $item;
            })->toArray();
            foreach ($insertData as $item) {
                PatientPrescriptionMedicineModel::updateOrCreate(
                    [
                        'prescription_id' => $item['prescription_id'],
                        'medicine_name' => $item['medicine_name'],
                    ],
                    $item
                );
            }
        }
    }

    public static function insertIpdPatientMedicine($domain,$id)
    {
        $prescription = PrescriptionModel::find($id);
        $date =  new \DateTime("now");
        $config = $domain['inv_config'];
        $jsonData = json_decode($prescription['json_content']);
        $medicines = ($jsonData->medicines ?? []);

        if (!empty($medicines) && is_array($medicines)) {
            $insertData = collect($jsonData->medicines)
                ->map(function ($medicine) use ($prescription,$date) {
                    $medicineDetails = MedicineDetailsModel::find($medicine->medicine_id);
                    if ($medicineDetails && empty($medicine->id) ) {
                        $medicineStock = $medicineDetails->medicineStock;
                        $dose_details ='';
                        $dose_details_bn ='';
                        $daily_quantity = 1;
                        $continue_mode = 'sos';

                        $dosage = $medicine->medicine_dosage_id ?? null;
                        if ($dosage) {
                            $dosage = MedicineDosageModel::find($medicine->medicine_dosage_id);
                            $dose_details = $dosage->name;
                            $dose_details_bn = $dosage->name_bn;
                            $continue_mode = $dosage->continue_mode;
                            $daily_quantity = $dosage->quantity;
                        }

                        $by_meal = '';
                        $by_meal_bn = '';
                        $bymeal = $medicine->medicine_bymeal_id ?? null;
                        if ($bymeal) {
                            $bymeal = MedicineDosageModel::find($medicine->medicine_bymeal_id);
                            $by_meal = $bymeal->name;
                            $by_meal_bn = $bymeal->name_bn;
                        }

                        return [

                            'hms_invoice_id' => $prescription->hms_invoice_id,
                            'prescription_id' => $prescription->id,
                            'company' => $medicineDetails->company ?? null, // notice key: medicine_name not medicineName
                            'medicine_name' => $medicineDetails->name ?? null, // notice key: medicine_name not medicineName
                            'generic' => $medicineStock->product->name ?? null, // notice key: medicine_name not medicineName
                            'generic_id' => $medicineStock->product->id ?? null, // notice key: medicine_name not medicineName
                            'medicine_id' => $medicineDetails->id ?? null,
                            'stock_item_id' => $medicineStock->stock_item_id ?? null,
                            'medicine_dosage_id' => $medicine->medicine_dosage_id ?? null,
                            'medicine_bymeal_id' => $medicine->medicine_bymeal_id ?? null,
                            'dose_details' => $dose_details,
                            'dose_details_bn' => $dose_details_bn,
                            'daily_quantity' => $daily_quantity,
                            'by_meal' => $by_meal,
                            'by_meal_bn' => $by_meal_bn,
                            'continue_mode' => $continue_mode,
                            'quantity' => $medicine->opd_quantity ?? 0,
                            'opd_quantity' => $medicine->opd_quantity ?? 0,
                            'ipd_status' => $medicineStock->ipd_status,
                            'opd_status' => $medicineStock->opd_status,
                            'opd_admin_status' => $medicineStock->admin_status,
                            'is_stock' => true,
                            'is_active' => $medicine->is_active ?? 1,
                            'start_date' => $date,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ];

                    }elseif($medicine->generic && empty($medicine->id)) {

                        $dose_details = '';
                        $dose_details_bn = '';
                        $daily_quantity = 1;
                        $continue_mode = 'sos';

                        $dosage = $medicine->medicine_dosage_id ?? null;
                        if ($dosage) {
                            $dosage = MedicineDosageModel::find($medicine->medicine_dosage_id);
                            $dose_details = $dosage->name;
                            $dose_details_bn = $dosage->name_bn;
                            $continue_mode = $dosage->continue_mode;
                            $daily_quantity = $dosage->quantity;
                        }

                        $by_meal = '';
                        $by_meal_bn = '';
                        $bymeal = $medicine->medicine_bymeal_id ?? null;
                        if ($bymeal) {
                            $bymeal = MedicineDosageModel::find($medicine->medicine_bymeal_id);
                            $by_meal = $bymeal->name;
                            $by_meal_bn = $bymeal->name_bn;
                        }

                        return [

                            'hms_invoice_id' => $prescription->hms_invoice_id,
                            'prescription_id' => $prescription->id,
                            'company' => $medicine->company ?? null, // notice key: medicine_name not medicineName
                            'medicine_name' => $medicine->generic ?? null, // notice key: medicine_name not medicineName
                            'generic' => $medicine->generic ?? null, // notice key: medicine_name not medicineName
                            'generic_id' =>  null, // notice key: medicine_name not medicineName
                            'stock_item_id' =>  null,
                            'medicine_dosage_id' => $medicine->medicine_dosage_id ?? null,
                            'medicine_bymeal_id' => $medicine->medicine_bymeal_id ?? null,
                            'dose_details' => $dose_details,
                            'dose_details_bn' => $dose_details_bn,
                            'by_meal' => $by_meal,
                            'by_meal_bn' => $by_meal_bn,
                            'continue_mode' => $continue_mode,
                            'daily_quantity' => $daily_quantity,
                            'quantity' => 0,
                            'is_stock' => false,
                            'ipd_status' => false,
                            'opd_status' => false,
                            'is_active' => $medicine->is_active ?? 1,
                            'start_date' => $date,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ];
                    }elseif ($medicine->id){
                        self::updateIpdMedicine($medicine);
                    }
                    return null; // explicit
                })
                ->filter() // ✅ remove nulls
                ->values() // ✅ reset array keys (important for upsert)
                ->toArray();

            $requiredKeys = [
                'hms_invoice_id','hms_invoice_id','prescription_id','company','medicine_name','generic',
                'generic_id','medicine_id','stock_item_id','medicine_dosage_id',
                'medicine_bymeal_id','dose_details','dose_details_bn','by_meal','by_meal_bn',
                'continue_mode','quantity','daily_quantity','is_stock','start_date'
            ];
            $insertData = collect($insertData)->map(function ($item) use ($requiredKeys) {
                foreach ($requiredKeys as $key) {
                    if (!array_key_exists($key, $item)) {
                        $item[$key] = null;
                    }
                }
                return $item;
            })->toArray();
            foreach ($insertData as $item) {
                PatientPrescriptionMedicineModel::updateOrCreate(
                    [
                        'prescription_id' => $item['prescription_id'],
                        'medicine_name' => $item['medicine_name'],
                    ],
                    $item
                );
            }


        }
    }

    public static function updateIpdMedicine($medicine){

        $dose_details ='';
        $dose_details_bn ='';
        $continue_mode = 'sos';
        $daily_quantity = 1;

        $dosage = $medicine->medicine_dosage_id ?? null;
        if ($dosage) {
            $dosage = MedicineDosageModel::find($medicine->medicine_dosage_id);
            $dose_details = $dosage->name;
            $dose_details_bn = $dosage->name_bn;
            $continue_mode = $dosage->continue_mode;
            $daily_quantity = $dosage->quantity;
        }

        $by_meal = '';
        $by_meal_bn = '';
        $bymeal = $medicine->medicine_bymeal_id ?? null;
        if ($bymeal) {
            $bymeal = MedicineDosageModel::find($medicine->medicine_bymeal_id);
            $by_meal = $bymeal->name;
            $by_meal_bn = $bymeal->name_bn;
        }
        self::updateOrCreate(
            [
                'id' => $medicine->id,
            ],
            [
                'is_active' => $medicine->is_active,
                'daily_quantity' => $daily_quantity,
                'medicine_dosage_id' => $medicine->medicine_dosage_id ?? null,
                'medicine_bymeal_id' => $medicine->medicine_bymeal_id ?? null,
                'dose_details' => $dose_details,
                'dose_details_bn' => $dose_details_bn,
                'by_meal' => $by_meal,
                'by_meal_bn' => $by_meal_bn,
                'continue_mode' => $continue_mode,
            ]
        );
    }

    public static function insertPatientMedicineUpdate($domain, $id)
    {
        $prescription = PrescriptionModel::find($id);
        $date = new \DateTime("now");
        $config = $domain['inv_config'] ?? null;
        $jsonData = json_decode($prescription['json_content']);
        $medicines = $jsonData->medicines ?? [];

        if (!empty($medicines) && is_array($medicines)) {

            $insertData = collect($medicines)
                ->map(function ($medicine) use ($prescription, $date) {

                    $medicineDetails = MedicineDetailsModel::find($medicine->medicine_id);
                    $dose_details = '';
                    $dose_details_bn = '';
                    $continue_mode = 'sos';
                    $daily_quantity = 1;
                    // Dosage
                    if (!empty($medicine->medicine_dosage_id)) {
                        $dosage = MedicineDosageModel::find($medicine->medicine_dosage_id);
                        if ($dosage) {
                            $dose_details = $dosage->name;
                            $dose_details_bn = $dosage->name_bn;
                            $continue_mode = $dosage->continue_mode;
                            $daily_quantity = $dosage->quantity;
                        }
                    }

                    // By Meal
                    $by_meal = '';
                    $by_meal_bn = '';
                    if (!empty($medicine->medicine_bymeal_id)) {
                        $bymeal = MedicineDosageModel::find($medicine->medicine_bymeal_id);
                        if ($bymeal) {
                            $by_meal = $bymeal->name;
                            $by_meal_bn = $bymeal->name_bn;
                        }
                    }

                    // CASE 1: Medicine found
                    if ($medicineDetails) {
                        $medicineStock = $medicineDetails->medicineStock;

                        return [
                            'hms_invoice_id' => $prescription->hms_invoice_id,
                            'prescription_id' => $prescription->id,
                            'company' => $medicineDetails->company ?? null,
                            'medicine_name' => $medicineDetails->name ?? null,
                            'generic' => $medicineStock->product->name ?? null,
                            'generic_id' => $medicineStock->product->id ?? null,
                            'medicine_id' => $medicineDetails->id ?? null,
                            'stock_item_id' => $medicineStock->stock_item_id ?? null,
                            'medicine_dosage_id' => $medicine->medicine_dosage_id ?? null,
                            'medicine_bymeal_id' => $medicine->medicine_bymeal_id ?? null,
                            'dose_details' => $dose_details,
                            'dose_details_bn' => $dose_details_bn,
                            'daily_quantity' => $daily_quantity,
                            'by_meal' => $by_meal,
                            'by_meal_bn' => $by_meal_bn,
                            'continue_mode' => $continue_mode,
                            'quantity' => $medicine->opd_quantity ?? 0,
                            'is_stock' => true,
                            'start_date' => $date,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ];
                    }

                    // CASE 2: Generic only
                    elseif (!empty($medicine->generic)) {
                        return [
                            'hms_invoice_id' => $prescription->hms_invoice_id,
                            'prescription_id' => $prescription->id,
                            'company' => $medicine->company ?? null,
                            'medicine_name' => $medicine->generic ?? null,
                            'generic' => $medicine->generic ?? null,
                            'generic_id' => null,
                            'medicine_id' => null,
                            'stock_item_id' => null,
                            'medicine_dosage_id' => $medicine->medicine_dosage_id ?? null,
                            'medicine_bymeal_id' => $medicine->medicine_bymeal_id ?? null,
                            'dose_details' => $dose_details,
                            'dose_details_bn' => $dose_details_bn,
                            'daily_quantity' => $daily_quantity,
                            'by_meal' => $by_meal,
                            'by_meal_bn' => $by_meal_bn,
                            'continue_mode' => $continue_mode,
                            'quantity' => 0,
                            'is_stock' => false,
                            'start_date' => $date,
                            'created_at' => $date,
                            'updated_at' => $date,
                        ];
                    }

                    return null; // skip invalid medicine

                })
                ->filter()
                ->values()
                ->toArray();

            // ✅ Ensure all keys match DB table columns
            $requiredKeys = [
                'hms_invoice_id','prescription_id','company','medicine_name','generic',
                'generic_id','medicine_id','stock_item_id','medicine_dosage_id',
                'medicine_bymeal_id','dose_details','dose_details_bn','by_meal','by_meal_bn',
                'continue_mode','quantity','daily_quantity','is_stock','start_date','created_at','updated_at'
            ];

            $insertData = collect($insertData)->map(function ($item) use ($requiredKeys) {
                foreach ($requiredKeys as $key) {
                    if (!array_key_exists($key, $item)) {
                        $item[$key] = null;
                    }
                }
                return $item;
            })->toArray();

            // ✅ Perform upsert
            PatientPrescriptionMedicineModel::upsert(
                $insertData,
                ['prescription_id', 'medicine_name'], // Unique keys
                [ // Columns to update if exists
                    'company','generic','generic_id','medicine_id','stock_item_id',
                    'medicine_dosage_id','medicine_bymeal_id','dose_details','dose_details_bn',
                    'by_meal','by_meal_bn','continue_mode','quantity','daily_quantity','is_stock',
                    'start_date','updated_at'
                ]
            );
            foreach ($insertData as $item) {
                PatientPrescriptionMedicineModel::updateOrCreate(
                    [
                        'prescription_id' => $item['prescription_id'],
                        'medicine_name' => $item['medicine_name'],
                    ],
                    $item
                );
            }
        }
    }

    public static function getPatientIpdMedicine($id){
        $today = now()->toDateString();

        $rows = self::join('hms_invoice', 'hms_invoice.id', '=', 'hms_patient_prescription_medicine.hms_invoice_id')
            ->leftJoin(
                'hms_patient_prescription_medicine_daily_history',
                function ($join) use ($today) {
                    $join->on(
                        'hms_patient_prescription_medicine_daily_history.prescription_medicine_id',
                        '=',
                        'hms_patient_prescription_medicine.id'
                    )->whereDate(
                        'hms_patient_prescription_medicine_daily_history.created_at',
                        '=',
                        $today
                    );
                }
            )
            // Only medicines that DO NOT have today's history
            ->whereNull('hms_patient_prescription_medicine_daily_history.id')

            ->where(function ($query) use ($id) {
                $query->where('hms_invoice.id', $id)
                    ->orWhere('hms_invoice.uid', $id);
            })
            ->where([
                'hms_patient_prescription_medicine.is_active' => 1,
                'hms_patient_prescription_medicine.is_stock' => 1
            ])
            ->select('hms_patient_prescription_medicine.*')
            ->orderBy('hms_patient_prescription_medicine.medicine_name', 'ASC')
            ->get();

        return $rows;

        /*$rows = self::join('hms_invoice', 'hms_invoice.id', '=', 'hms_patient_prescription_medicine.hms_invoice_id')
            ->leftJoin(
                'hms_patient_prescription_medicine_daily_history',
                'hms_patient_prescription_medicine_daily_history.prescription_medicine_id',
                '=',
                'hms_patient_prescription_medicine.id'
            )
            ->where(function($query) {
                $query->whereDate('hms_patient_prescription_medicine_daily_history.created_at', '!=', now()->toDateString())
                    ->orWhereNull('hms_patient_prescription_medicine_daily_history.id');
            })
            ->where(function ($query) use ($id) {
                $query->where('hms_invoice.id', $id)
                    ->orWhere('hms_invoice.uid', $id);
            })
            ->where([
                'hms_patient_prescription_medicine.is_active'=> 1,
                'hms_patient_prescription_medicine.is_stock'=> 1
            ])
            ->select('hms_patient_prescription_medicine.*')
            ->orderBy('hms_patient_prescription_medicine.medicine_name','ASC')
            ->get();

        return $rows;*/

    }


}
