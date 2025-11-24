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
            if (empty($model->barcode)) {
                $model->uid = self::generateUniqueCode(12);
            }
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }

    public static function generateUniqueCode($length = 12)
    {
        do {
            // Generate a random 12-digit number
            $code = str_pad(random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
        } while (self::where('uid', $code)->exists());
        return $code;
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
        $uniqueId=  self::generateUniqueCode(12);
        $jsonData = json_decode($prescription['json_content']);
        $investigations = ($jsonData->patient_report->patient_examination->investigation ?? []);

        $news = [];
        foreach ($investigations as $investigation):
            $news[] = $investigation->id;
        endforeach;
        InvoiceParticularModel::where([
            'prescription_id' => $id,
            'mode' => 'investigation'
        ])->whereNotIn('particular_id', $news)->delete();

        if (!empty($investigations) && is_array($investigations)) {
            collect($investigations)->map(function ($investigation) use ($prescription,$uniqueId,$date) {
                $particular = ParticularModel::find($investigation->id);
                InvoiceParticularModel::updateOrCreate(
                    [
                        'hms_invoice_id'             => $prescription->hms_invoice_id,
                        'prescription_id'            => $prescription->id,
                        'particular_id'              => $investigation->id,
                    ],
                    [
                        'unique_id'      => $uniqueId,
                        'name'      => $particular->name,
                        'mode'      => 'investigation',
                        'quantity'      => 1,
                        'is_available'      => $particular->is_available,
                        'price'         => $particular->price ?? 0,
                        'estimate_price'         => $particular->price ?? 0,
                        'sub_total'         => $particular->price ?? 0,
                        'updated_at'    => $date,
                        'created_at'    => $date,
                    ]
                );
            })->toArray();
        }
    }

    public static function insertIpdInvestigations($domain,$id,$data)
    {

        $date =  new \DateTime("now");
        $invoice = InvoiceModel::findByIdOrUid($id);
        $investigations = $data;
        $uniqueId = time();
        if (!empty($investigations) && is_array($investigations)) {
            collect($investigations)->map(function ($investigation) use ($invoice,$date,$uniqueId) {

                $particular = ParticularModel::find($investigation['id']);
                if($particular){
                    InvoiceParticularModel::updateOrCreate(
                        [
                            'hms_invoice_id'             => $invoice->id,
                            'particular_id'              => $particular->id,
                        ],
                        [
                            'name'      => $particular->name,
                            'is_available'      => $particular->is_available,
                            'unique_id'      => $uniqueId,
                            'quantity'      => 1,
                            'mode' => 'investigation',
                            'price'         => $particular->price ?? 0,
                            'estimate_price'         => $particular->price ?? 0,
                            'sub_total'         => $particular->price ?? 0,
                            'updated_at'    => $date,
                            'created_at'    => $date,
                        ]
                    );
                }
            })->toArray();
         //   $amount = InvoiceParticularModel::where('invoice_transaction_id', $invoiceTransaction->id)->sum('sub_total');
        //    $invoiceTransaction->update(['sub_total' => $amount , 'total' => $amount]);
           // $amount = InvoiceParticularModel::where('hms_invoice_id', $invoice->id)->sum('sub_total');
          //  $invoice->update(['sub_total' => $amount , 'total' => $amount]);

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
                ->update([ 'json_content' => json_encode($medicines),'sale_id' => $sales->id]);
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
                            'name'          => $particular->display_name,
                            'quantity'      => $item['quantity'],
                            'start_date'    => new \DateTime($item['start_date']),
                            'price'         => $particular->price ?? 0,
                            'estimate_price'         => $particular->price ?? 0,
                            'sub_total'         => ($particular->price) ? ($item['quantity'] * $particular->price) : 0,
                            'updated_at'    => $date,
                            'created_at'    => $date,
                        ]
                    );
                }
            })->toArray();
            $amount = InvoiceParticularModel::where('invoice_transaction_id', $invoiceTransaction->id)->sum('sub_total');
            $invoiceTransaction->update(['sub_total' => $amount , 'total' => $amount]);
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
        $entity = self::where([ 'hms_invoice_transaction.mode' => $mode])
            ->where(function ($query) use ($id) {
                $query->where('hms_invoice.id', '=', $id)
                ->orWhere('hms_invoice.uid', '=', $id);
            })
            ->join('hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_transaction.hms_invoice_id')
            ->with(['invoiceParticular' => function ($query) {
                $query->select([
                    'hms_invoice_particular.id',
                    'hms_invoice_particular.invoice_transaction_id',
                    'hms_invoice_particular.name',
                    'hms_invoice_particular.content',
                    'hms_invoice_particular.price',
                    'hms_invoice_particular.quantity',
                ]);
            }])
            ->select(
                'hms_invoice_transaction.id','hms_invoice_transaction.sub_total','hms_invoice_transaction.total','hms_invoice_transaction.process',
                DB::raw('DATE_FORMAT(hms_invoice_transaction.updated_at, "%d-%m-%y") as created')
            )
            ->get();
        return $entity;
    }

    public static function getInvoiceRefundParticulars($id,$transaction)
    {
        $entity = self::where(
                ['hms_invoice_transaction.id' => $transaction]
            )->where(function ($query) use ($id) {
                $query->where('hms_invoice.id', '=', $id)
                    ->orWhere('hms_invoice.uid', '=', $id);
            })
            ->join('hms_invoice', 'hms_invoice.id', '=', 'hms_invoice_transaction.hms_invoice_id')
            ->with(['items' => function ($query) {
                $query->select([
                    'hms_invoice_particular.id',
                    'hms_invoice_particular.invoice_transaction_id',
                    'hms_invoice_particular.name',
                    'hms_invoice_particular.quantity',
                    'hms_invoice_particular.content',
                    'hms_invoice_particular.price',
                ])->where(['hms_invoice_particular.is_refund' => 0,'hms_invoice_particular.process' => "New"]);
            }])
            ->select(
                'hms_invoice_transaction.id','hms_invoice_transaction.sub_total','hms_invoice_transaction.total','hms_invoice_transaction.process',
                DB::raw('DATE_FORMAT(hms_invoice_transaction.updated_at, "%d-%m-%y") as created')
            )
            ->get()->first();
        return $entity;
    }

    public static function getMedicine($id)
    {
        $entity = self::where([
            'hms_invoice_transaction.mode' => 'medicine'
        ])->where(function ($query) use ($id) {
            $query->where('hms_invoice.id', '=', $id)
                ->orWhere('hms_invoice.uid', '=', $id);
        })
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
                'hms_invoice_transaction.json_content as json_content',
                DB::raw('DATE_FORMAT(hms_invoice_transaction.updated_at, "%d-%m-%y") as created')
            )
            ->get();
        return $entity;
    }

    public static function insertInvoiceTransaction($domain,$entity,$data)
    {

        $date =  new \DateTime("now");
        $hms_invoice_id =  $entity->id;
        $investigations = $data['json_content'];
        $total = $data['total'];
        if (!empty($investigations) && is_array($investigations)) {
            $invoiceTransaction = InvoiceTransactionModel::create([
                'hms_invoice_id'=> $entity->id,
                'created_by_id'=> $domain['user_id'],
                'approved_by_id'=> $domain['user_id'],
                'mode'    => 'investigation',
                'sub_total'    => $total,
                'total'    => $total,
                'amount'    => $total,
                'process'    => 'Done',
                'updated_at'    => $date,
                'created_at'    => $date,
            ]);
            if (!empty($investigations) && is_array($investigations)) {
                collect($investigations)->map(function ($investigation) use ($hms_invoice_id,$invoiceTransaction,$date) {
                    $particular = $investigation['particular_id'] ?? '';
                    if (
                        ($investigation['is_selected'] ?? false) == true &&
                        $particular &&
                        ($investigation['is_new'] ?? false) == false
                    ) {
                        InvoiceParticularModel::where('hms_invoice_id', $hms_invoice_id)
                            ->where('id', $investigation['particular_id'])
                            ->update([
                                'invoice_transaction_id' => $invoiceTransaction->id,
                                'status' => 1,
                                'is_invoice' => 1,
                            ]);
                    }elseif (
                        ($investigation['is_selected'] ?? false) == true &&
                        ($investigation['is_new'] ?? true) == true
                    ) {

                        $particular = ParticularModel::find($investigation['id']);
                        if($particular){
                            InvoiceParticularModel::updateOrCreate(
                                [
                                    'hms_invoice_id'             => $hms_invoice_id,
                                    'particular_id'              => $particular->id,
                                ],
                                [
                                    'invoice_transaction_id' => $invoiceTransaction->id,
                                    'name'      => $particular->name,
                                    'quantity'      => 1,
                                    'status'      => 1,
                                    'is_invoice' => 1,
                                    'mode' => 'investigation',
                                    'price'         => $particular->price ?? 0,
                                    'estimate_price'         => $particular->price ?? 0,
                                    'sub_total'         => $particular->price ?? 0,
                                    'updated_at'    => $date,
                                    'created_at'    => $date,
                                ]
                            );
                        }
                    }

                })->toArray();
            }
        }
        return $entity;
    }

    public static function insertAdmissionInvoiceTransaction($domain,$invoice,$data)
    {

        $amount = $data['total'];
        $date =  new \DateTime("now");
        $items = InvoiceTransactionModel::where(['hms_invoice_id' => $invoice->id,'process'=>'New'])->get();
        collect($items)->map(function ($item) use ($domain,$invoice,$amount,$date) {
            $transaction = InvoiceTransactionModel::find($item['id']);
            if($transaction){
                InvoiceTransactionModel::updateOrCreate(
                    [
                        'hms_invoice_id'             => $invoice->id,
                        'id'     => $transaction->id,
                    ],
                    [
                        'created_by_id'=> $domain['user_id'],
                        'approved_by_id'=> $domain['user_id'],
                        'mode'    => 'admission',
                        'sub_total'    => $amount,
                        'total'    => $amount,
                        'amount'    => $amount,
                        'process'    => 'Done',
                        'updated_at'    => $date,
                        'created_at'    => $date,
                    ]
                );
            }
        })->toArray();
        $invoice->update(['sub_total' => $amount , 'total' => $amount, 'amount' => $amount, 'process' => 'admitted']);
    }


    public static function showInvoiceData($id)
    {

        $entity = self::join('hms_invoice as hms_invoice', 'hms_invoice_transaction.hms_invoice_id', '=', 'hms_invoice.id')
            ->leftjoin('cor_customers','cor_customers.id','=','hms_invoice.customer_id')
            ->leftjoin('cor_setting as religion','religion.id','=','cor_customers.religion_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->leftjoin('hms_prescription as prescription','prescription.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('hms_admission_patient_details as admission_patient','admission_patient.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('users as prescription_doctor','prescription_doctor.id','=','prescription.created_by_id')
            ->leftjoin('hms_particular as room','room.id','=','hms_invoice.room_id')
            ->leftjoin('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('hms_particular as admit_consultant','admit_consultant.id','=','hms_invoice.admit_consultant_id')
            ->leftjoin('hms_particular as admit_doctor','admit_doctor.id','=','hms_invoice.admit_doctor_id')
            ->leftjoin('hms_particular_mode as admit_unit','admit_unit.id','=','hms_invoice.admit_unit_id')
            ->leftjoin('hms_particular_mode as admit_department','admit_department.id','=','hms_invoice.admit_department_id')
            ->leftjoin('hms_particular_mode as particular_payment_mode','particular_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->leftjoin('hms_invoice as invoice_parent','invoice_parent.id','=','hms_invoice.parent_id')
            ->leftjoin('hms_particular_mode as parent_patient_mode','parent_patient_mode.id','=','invoice_parent.patient_mode_id')
            ->where('hms_invoice_transaction.id', $id)
            ->select([
                'hms_invoice_transaction.*',
                'parent_patient_mode.name as parent_patient_mode_name',
                'parent_patient_mode.slug as parent_patient_mode_slug',
                DB::raw('DATE_FORMAT(hms_invoice.updated_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%m-%Y") as appointment'),
                'hms_invoice.invoice as invoice',
                'hms_invoice.comment',
                'hms_invoice.guardian_name as guardian_name',
                'hms_invoice.guardian_mobile as guardian_mobile',
                'cor_customers.name as name',
                'cor_customers.mobile as mobile',
                'cor_customers.id as customer_id',
                'cor_customers.customer_id as patient_id',
                'cor_customers.health_id as health_id',
                'cor_customers.gender as gender',
                'cor_customers.father_name',
                'cor_customers.mother_name',
                'cor_customers.upazilla_id',
                'cor_customers.country_id',
                'cor_customers.profession',
                'cor_customers.religion_id',
                'cor_customers.nid',
                'cor_customers.identity_mode',
                'cor_customers.address',
                'religion.name as religion_name',
                'cor_customers.permanent_address',
                DB::raw('DATE_FORMAT(cor_customers.dob, "%m-%d-%Y") as dob'),
                'cor_customers.identity_mode as identity_mode',
                'createdBy.username as created_by_user_name',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'room.display_name as room_name',
                'patient_mode.name as mode_name',
                'particular_payment_mode.name as payment_mode_name',
                'hms_invoice.process as process',
                'admit_consultant.name as admit_consultant_name',
                'admit_unit.name as admit_unit_name',
                'admit_department.name as admit_department_name',
                'admit_doctor.name as admit_doctor_name',
            ])
            ->with(['items' => function ($query) {
                $query->select([
                    'hms_invoice_particular.id',
                    'hms_invoice_particular.invoice_transaction_id',
                    'hms_invoice_particular.name as item_name',
                    'hms_invoice_particular.quantity',
                    'hms_invoice_particular.price',
                    'hms_invoice_particular.sub_total',
                    'hms_invoice_particular.process',
                    'diagnostic_room.name as diagnostic_room_name',
                ])->leftjoin('hms_particular as hms_particular','hms_particular.id','=','hms_invoice_particular.particular_id')
                    ->leftjoin('hms_particular_mode as diagnostic_room','diagnostic_room.id','=','hms_particular.diagnostic_room_id')
                    ->where('hms_invoice_particular.mode','investigation');
            }])->first();
        return $entity;
    }



}
