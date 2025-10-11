<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\UserModel;
use Modules\Hospital\App\Entities\InvoiceParticular;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;
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

    public function items()
    {
        return $this->hasMany(InvoiceParticularModel::class, 'invoice_transaction_id');
    }


    public function createdDoctorInfo()
    {
        return $this->hasOne(UserModel::class, 'id', 'created_by_id');
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
                    'hms_invoice_id'                    => $prescription->hms_invoice_id,
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

    public static function insertIpdInvestigations($domain,$id,$data)
    {

        $date =  new \DateTime("now");
        $invoice = InvoiceModel::find($id);
        $investigations = $data;

        if (!empty($investigations) && is_array($investigations)) {
            $invoiceTransaction = self::create(
                [
                    'hms_invoice_id' => $invoice->id,
                    'created_by_id'    => $domain['user_id'],
                    'mode' => 'investigation',
                    'updated_at'    => $date,
                    'created_at'    => $date,
                ]
            );

            collect($investigations)->map(function ($investigation) use ($invoice,$invoiceTransaction,$date) {

                $particular = ParticularModel::find($investigation['id']);
                if($particular){
                    InvoiceParticularModel::updateOrCreate(
                        [
                            'hms_invoice_id'             => $invoice->id,
                            'invoice_transaction_id'     => $invoiceTransaction->id,
                            'particular_id'              => $particular->id,
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

    public static function insertIpdMedicines($domain,$id,$data)
    {
        $date =  new \DateTime("now");
        $config = $domain['inv_config'];
        $invoice = InvoiceModel::find($id);
        $medicines = $data;
        if (!empty($medicines) && is_array($medicines)) {
            $invoiceTransaction = self::create(
                [
                    'hms_invoice_id' => $invoice->id,
                    'mode' => 'medicine',
                    'created_by_id'    => $domain['user_id'],
                    'updated_at'    => $date,
                    'created_at'    => $date,
                ]
            );
            if (empty($invoiceTransaction->sale_id)) {
                $insertData['config_id'] = $config;
                $insertData['customer_id'] = $invoice->customer_id ?? null;
                $sales = HospitalSalesModel::create($insertData);
                $insertData = collect($medicines)
                        ->map(function ($medicine) use ($sales, $date) {
                            if (StockItemModel::find($medicine['medicine_id'])) {
                                return [
                                    'sale_id' => $sales->id,
                                    'name' => $medicine['medicine_name'] ?? null, // notice key: medicine_name not medicineName
                                    'stock_item_id' => $medicine['medicine_id'] ?? null,
                                    'quantity' => $medicine['quantity'] ?? 0,
                                    'price' => $medicine['price'] ?? 0,
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
                    if (StockItemModel::find($medicine['medicine_id'])) {
                        SalesItemModel::updateOrCreate(
                            [
                                'sale_id' => $sales->id,
                                'name' => $medicine['medicine_name'] ?? null, // unique keys
                            ],
                            [
                                'name' => $medicine['medicine_name'] ?? null, // notice key: medicine_name not medicineName
                                'stock_item_id' => $medicine['medicine_id'] ?? null,
                                'quantity' => $medicine['quantity'] ?? 0,
                                'price' => $medicine['price'] ?? 0,
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

    public static function insertIpdRoom($domain,$id,$data)
    {
        $date =  new \DateTime("now");
        $invoice = InvoiceModel::find($id);
        $items = $data;

        if (!empty($items) && is_array($items)) {
            $invoiceTransaction = self::create(
                [
                    'hms_invoice_id' => $invoice->id,
                    'created_by_id'    => $domain['user_id'],
                    'mode' => 'room',
                    'updated_at'    => $date,
                    'created_at'    => $date,
                ]
            );

            collect($items)->map(function ($item) use ($invoice,$invoiceTransaction,$date) {

                $particular = ParticularModel::find($item['id']);
                if($particular){
                    InvoiceParticularModel::updateOrCreate(
                        [
                            'hms_invoice_id'             => $invoice->id,
                            'invoice_transaction_id'     => $invoiceTransaction->id,
                            'particular_id'              => $particular->id,
                        ],
                        [
                            'name'          => $particular->name,
                            'quantity'      => $item['quantity'],
                            'start_date'    => new \DateTime($item['start_date']),
                            'price'         => $particular->price ?? 0,
                            'updated_at'    => $date,
                            'created_at'    => $date,
                        ]
                    );
                }
            })->toArray();
        }
    }

    public static function adviceIpdRoom($domain,$id,$data)
    {
        $date =  new \DateTime("now");
        $invoice = InvoiceModel::find($id);
        $items = $data;
        if (!empty($items) && is_array($items)) {
            $invoiceTransaction = self::create(
                [
                    'hms_invoice_id' => $invoice->id,
                    'created_by_id'    => $domain['user_id'],
                    'mode' => 'advice',
                    'updated_at'    => $date,
                    'created_at'    => $date,
                ]
            );

            collect($items)->map(function ($item) use ($invoice,$invoiceTransaction,$date) {
                InvoiceParticularModel::updateOrCreate(
                    [
                        'hms_invoice_id'             => $invoice->id,
                        'invoice_transaction_id'     => $invoiceTransaction->id,
                        'content'                     => $item['content'],
                    ],
                    [
                        'updated_at'    => $date,
                        'created_at'    => $date,
                    ]
                );
            })->toArray();
        }
    }



    public function sales()
    {
        return $this->belongsTo(SalesModel::class, 'sale_id');
    }

     public function invoiceParticular()
    {
        return $this->hasMany(InvoiceParticularModel::class, 'invoice_transaction_id');
    }

    public function salesItems()
    {
        return $this->hasManyThrough(
            SalesItemModel::class,
            SalesModel::class,
            'id',        // Foreign key on inv_sales (local key for InvoiceTransaction)
            'sale_id',   // Foreign key on inv_sales_item
            'sale_id',   // Local key on hms_invoice_transaction
            'id'         // Local key on inv_sales
        );
    }

    public static function getInvoiceParticulars($id,$mode)
    {
        $entity = self::where([
            'hms_invoice.id' => $id,
            'hms_invoice_transaction.mode' => $mode
        ])
            ->join('hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_transaction.hms_invoice_id')
            ->with(['invoiceParticular' => function ($query) {
                $query->select([
                    'hms_invoice_particular.id',
                    'hms_invoice_particular.invoice_transaction_id',
                    'hms_invoice_particular.name',
                    'hms_invoice_particular.content',
                    'hms_invoice_particular.price',
                ]);
            }])
            ->select(
                'hms_invoice_transaction.id','hms_invoice_transaction.sub_total','hms_invoice_transaction.total','hms_invoice_transaction.process',
                DB::raw('DATE_FORMAT(hms_invoice_transaction.updated_at, "%d-%m-%y") as created')
            )
            ->get();
        return $entity;
    }

    public static function getMedicine($id)
    {
        $entity = self::where([
            'hms_invoice.id' => $id,
            'hms_invoice_transaction.mode' => 'medicine'
        ])
            ->join('hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_transaction.hms_invoice_id')
            ->leftJoin('inv_sales', 'inv_sales.id', '=', 'hms_invoice_transaction.sale_id')
            ->with(['salesItems' => function ($query) {
                $query->select([
                    'inv_sales_item.id',
                    'inv_sales_item.sale_id',
                    'inv_sales_item.stock_item_id as product_id',
                    'inv_sales_item.unit_id',
                    'inv_sales_item.name as item_name',
                    'inv_sales_item.uom as uom',
                    'inv_sales_item.quantity',
                    'inv_sales_item.sales_price',
                    'inv_sales_item.purchase_price',
                    'inv_sales_item.price',
                    'inv_sales_item.sub_total',
                    'inv_sales_item.bonus_quantity'
                ]);
            }])
            ->select(
                'hms_invoice_transaction.id',
                'hms_invoice_transaction.sale_id',
                'inv_sales.id as inv_sales_id',
                'hms_invoice.id as hms_invoice_id',
                DB::raw('DATE_FORMAT(hms_invoice_transaction.updated_at, "%d-%m-%y") as created')
            )
            ->get();
        return $entity;
    }



}
