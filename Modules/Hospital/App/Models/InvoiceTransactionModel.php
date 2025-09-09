<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Modules\Hospital\App\Entities\InvoiceParticular;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\StockItemModel;
use Ramsey\Collection\Collection;

class InvoiceTransactionModel extends Model
{
    use HasFactory;

    protected $table = 'hms_invoice_transaction';
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

    public static function insertInvestigations($domain,$id)
    {
        $prescription = PrescriptionModel::with(['invoice_transaction:id'])->find($id);
        $date =  new \DateTime("now");
        $jsonData = json_decode($prescription['json_content']);
        $investigations = ($jsonData->patient_report->patient_examination->investigation ?? []);
        if (!empty($investigations) && is_array($investigations)) {
            $invoiceTransaction = self::updateOrCreate(
                [
                    'invoice_id'                    => $prescription->hms_invoice_id,
                    'prescription_id'               => $prescription->id,
                ],
                [
                    'created_by_id'    => $prescription->created_by_id,
                    'updated_at'    => $date,
                    'created_at'    => $date,
                ]
            );
            collect($investigations)->map(function ($investigation) use ($prescription,$invoiceTransaction,$date) {

                $particular = ParticularModel::find($investigation->id);
                if($particular){
                    InvoiceParticularModel::updateOrCreate(
                        [
                            'hms_invoice_id'             => $prescription->hms_invoice_id,
                            'prescription_id'            => $prescription->id,
                            'invoice_transaction_id'     => $invoiceTransaction->id,
                            'particular_id'              => $investigation->id,
                        ],
                        [
                            'name'      => $particular->name,
                            'quantity'      => 1,
                            'price'         => $particular->price ?? 0,
                            'updated_at'    => $date,
                            'created_at'    => $date,
                        ]
                    );
                }
            })->toArray();
        }
    }

    public static function insertIpdInvestigations($domain,$invoice,$user,$data)
    {

        $date =  new \DateTime("now");
        $jsonData = json_decode($data['json_investigation']);
        $investigations = ($jsonData ?? []);
        if (!empty($investigations) && is_array($investigations)) {
            $invoiceTransaction = self::insert(
                [
                    'invoice_id' => $invoice,
                    'created_by_id'    => $user,
                    'updated_at'    => $date,
                    'created_at'    => $date,
                ]
            );
            collect($investigations)->map(function ($investigation) use ($invoice,$invoiceTransaction,$date) {

                $particular = ParticularModel::find($investigation->id);
                if($particular){
                    InvoiceParticularModel::updateOrCreate(
                        [
                            'hms_invoice_id'             => $invoice,
                            'invoice_transaction_id'     => $invoiceTransaction->id,
                            'particular_id'              => $investigation->id,
                        ],
                        [
                            'name'      => $particular->name,
                            'quantity'      => 1,
                            'price'         => $particular->price ?? 0,
                            'updated_at'    => $date,
                            'created_at'    => $date,
                        ]
                    );
                }
            })->toArray();
        }
    }

    public static function insertIpdMedicines($domain,$invoice,$user,$data)
    {
        $date =  new \DateTime("now");
        $config = $domain['inv_config'];
        $jsonData = json_decode($data['json_content_medicine']);
        $medicines = ($jsonData->medicines ?? []);
        if (!empty($medicines) && is_array($medicines)) {

            $invoiceTransaction = self::insert(
                [
                    'invoice_id' => $invoice,
                    'created_by_id'    => $user,
                    'updated_at'    => $date,
                    'created_at'    => $date,
                ]
            );

            if (empty($invoiceTransaction->sale_id)) {
                $insertData['config_id'] = $config;
                $insertData['customer_id'] = $invoice->customer_id ?? null;
                $sales = self::create($insertData);
                $insertData = collect($jsonData->medicines)
                    ->map(function ($medicine) use ($sales, $date) {
                        if (StockItemModel::find($medicine->medicine_id)) {
                            return [
                                'sale_id' => $sales->id,
                                'name' => $medicine->medicine_name ?? null, // notice key: medicine_name not medicineName
                                'stock_item_id' => $medicine->medicine_id ?? null,
                                'quantity' => $medicine->quantity ?? 0,
                                'price' => $medicine->price ?? 0,
                                'created_at' => $date,
                                'updated_at' => $date,
                            ];
                        }
                        return null; // explicit
                    })
                    ->filter() // ✅ remove nulls
                    ->values() // ✅ reset array keys (important for upsert)
                    ->toArray();

                SalesItemModel::upsert(
                    $insertData,
                    ['sale_id', 'name'], // unique keys
                    ['stock_item_id', 'quantity', 'price', 'updated_at'] // update columns
                );

            } else {
                $sales = self::find($invoiceTransaction->sale_id);
                collect($medicines)->map(function ($medicine) use ($sales, $date) {
                    if (StockItemModel::find($medicine->medicine_id)) {
                        SalesItemModel::updateOrCreate(
                            [
                                'sale_id' => $sales->id,
                                'name' => $medicine->medicineName ?? null, // unique keys
                            ],
                            [
                                'stock_item_id' => $medicine->medicine_id ?? null,
                                'quantity' => $medicine->quantity ?? 0,
                                'price' => $medicine->price ?? 0,
                                'updated_at' => $date,
                                'created_at' => $date,
                            ]
                        );
                    }
                })->toArray();
            }
            InvoiceTransactionModel::where('id', $invoiceTransaction->id)
                ->update(['sale_id' => $sales->id]);
        }
    }

    public static function insertIpdPatientExamination($domain,$invoice,$user,$data)
    {
        $date =  new \DateTime("now");
        $config = $domain['inv_config'];
        $jsonData = json_decode($data['json_content_medicine']);
        $medicines = ($jsonData->medicines ?? []);
        if (!empty($medicines) && is_array($medicines)) {

            $invoiceTransaction = self::insert(
                [
                    'invoice_id' => $invoice,
                    'created_by_id'    => $user,
                    'updated_at'    => $date,
                    'created_at'    => $date,
                ]
            );

            if (empty($invoiceTransaction->sale_id)) {
                $insertData['config_id'] = $config;
                $insertData['customer_id'] = $invoice->customer_id ?? null;
                $sales = self::create($insertData);
                $insertData = collect($jsonData->medicines)
                    ->map(function ($medicine) use ($sales, $date) {
                        if (StockItemModel::find($medicine->medicine_id)) {
                            return [
                                'sale_id' => $sales->id,
                                'name' => $medicine->medicine_name ?? null, // notice key: medicine_name not medicineName
                                'stock_item_id' => $medicine->medicine_id ?? null,
                                'quantity' => $medicine->quantity ?? 0,
                                'price' => $medicine->price ?? 0,
                                'created_at' => $date,
                                'updated_at' => $date,
                            ];
                        }
                        return null; // explicit
                    })
                    ->filter() // ✅ remove nulls
                    ->values() // ✅ reset array keys (important for upsert)
                    ->toArray();

                SalesItemModel::upsert(
                    $insertData,
                    ['sale_id', 'name'], // unique keys
                    ['stock_item_id', 'quantity', 'price', 'updated_at'] // update columns
                );

            } else {
                $sales = self::find($invoiceTransaction->sale_id);
                collect($medicines)->map(function ($medicine) use ($sales, $date) {
                    if (StockItemModel::find($medicine->medicine_id)) {
                        SalesItemModel::updateOrCreate(
                            [
                                'sale_id' => $sales->id,
                                'name' => $medicine->medicineName ?? null, // unique keys
                            ],
                            [
                                'stock_item_id' => $medicine->medicine_id ?? null,
                                'quantity' => $medicine->quantity ?? 0,
                                'price' => $medicine->price ?? 0,
                                'updated_at' => $date,
                                'created_at' => $date,
                            ]
                        );
                    }
                })->toArray();
            }
            InvoiceTransactionModel::where('id', $invoiceTransaction->id)
                ->update(['sale_id' => $sales->id]);
        }
    }

}
