<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;
use Ramsey\Collection\Collection;

class OPDModel extends Model
{
    use HasFactory;


    protected $table = 'inv_sales';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $model->process = 'New';
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }


    public static function salesEventListener($config,$patient_mode_id)
    {
        $patientMode = ParticularModeModel::find($patient_mode_id);
        $mode = ($patientMode) ? $patientMode->short_code:'OPD';
        $patternCodeService = app(GeneratePatternCodeService::class);

        $params = [
            'config' => $config,
            'table' => 'hms_invoice',
            'prefix' => "{$mode}-",
        ];
        return $patternCodeService->invoiceNo($params);
    }

    public function customerDomain(){
        return $this->belongsTo(CustomerModel::class , 'customer_id');
    }

    public static function insertHmsInvoice($invConfig,$hmsConfig,$entity,$data)
    {

        $paymentMode = $data['patient_payment_mode_id'];
        $patientMod= $data['patient_mode']?? 'opd';
        $patientPaymentMode = ParticularModeModel::firstWhere([
            ['id', $paymentMode],
            ['particular_module_id', 11],
        ])->slug;
        $patient_mode_id = ParticularModeModel::firstWhere([
            ['slug', $patientMod],
            ['particular_module_id', 3],
        ])->id;
        $amount = 0;
        if($data['patient_mode'] == "opd" and $patientPaymentMode === 'general' ){
            $particular = ParticularModel::find($hmsConfig['opd_ticket_fee_id']);
            $amount = $particular->price;
        }elseif ($data['patient_mode'] == "ipd" and $patientPaymentMode === 'general' ){
            $particular = ParticularModel::find($hmsConfig['admission_fee_id']);
            $amount = $particular->price;
        }elseif ($data['patient_mode'] == "emergency" and $patientPaymentMode === 'general' ){
            $particular = ParticularModel::find($hmsConfig['emergency_fee_id']);
            $amount = $particular->price;
        }elseif ($data['patient_mode'] == "ot" and $patientPaymentMode === 'general' ){
            $particular = ParticularModel::find($hmsConfig['ot_fee_id']);
            $amount = $particular->price;
        }

        $sales = self::create(
            [
                'customer_id' => $entity->id,
                'config_id' => $invConfig,
                'sub_total' => $amount,
                'created_by_id' => $data['created_by_id'],
            ]
        );

        $invoice = self::salesEventListener($hmsConfig->id,$patient_mode_id)['generateId'];
        $code = self::salesEventListener($hmsConfig->id,$patient_mode_id)['code'];

        $invoice = InvoiceModel::updateOrCreate(
            ['sales_id' => $sales->id],
            [
                'config_id' => $hmsConfig->id,
                'invoice' => $invoice,
                'code' => $code,
                'customer_id' => $entity->id,
                'referred_doctor_id' => $data['referred_doctor_id'] ?? null,
                'assign_doctor_id' => $data['assign_doctor_id'] ?? null,
                'specialization_id' => $data['specialization_id'] ?? null,
                'disease_id' => $data['disease_id'] ?? null,
                'disease' => $data['disease'] ?? null,
                'room_id' => $data['room_id'] ?? null,
                'patient_mode_id' => $patient_mode_id ?? null,
                'patient_payment_mode_id' => $paymentMode ?? null,
                'free_identification' => $data['free_identification'] ?? null,
                'remark' => $data['remark'] ?? null,
                'appointment_date' =>  new \DateTime("now") ?? null,
                'day' => $data['day'] ?? null,
                'month' => $data['month'] ?? null,
                'year' => $data['year'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_mobile' => $data['guardian_mobile'] ?? null,
                'process' => 'New',
                'sub_total' => $amount,
                'total' => $amount,
                'amount' => $amount,
                'created_by_id' => $data['created_by_id'] ?? null,
            ]
        );
        $date =  new \DateTime("now");
        $invoiceTransaction = InvoiceTransactionModel::create(
                [
                    'hms_invoice_id'   => $invoice->id,
                    'created_by_id'    => $invoice->created_by_id,
                    'mode'    => $data['patient_mode'],
                    'updated_at'    => $date,
                    'created_at'    => $date,
                ]
            );

        InvoiceParticularModel::create(
            [
                'hms_invoice_id' => $invoice->id,
                'invoice_transaction_id' => $invoiceTransaction->id,
                'particular_id' => $particular->id ?? null,
                'mode' => $data['patient_mode'],
                'quantity' => 1,
                'status' => 1,
                'process' => 'Done',
                'price' => $amount,
                'sub_total' => $amount,
            ]
        );

        return $invoice->id;

    }


    public static function getShow($id,$domain)
    {
        $entity = self::where([
            ['inv_sales.config_id', '=', $domain['config_id']],
            ['inv_sales.id', '=', $id]
        ])
            ->leftjoin('cor_customers','cor_customers.id','=','inv_sales.customer_id')
            ->leftjoin('users as createdBy','createdBy.id','=','inv_sales.created_by_id')
            ->leftjoin('users as salesBy','salesBy.id','=','inv_sales.sales_by_id')
            ->leftjoin('acc_transaction_mode as transactionMode','transactionMode.id','=','inv_sales.transaction_mode_id')
//            ->leftjoin('uti_transaction_method as method','method.id','=','acc_transaction_mode.method_id')
            ->select([
                'inv_sales.id',
                DB::raw('DATE_FORMAT(inv_sales.updated_at, "%d-%m-%Y") as created'),
                'inv_sales.invoice as invoice',
                'inv_sales.sub_total as sub_total',
                'inv_sales.total as total',
                'inv_sales.payment as payment',
                'inv_sales.discount as discount',
                'inv_sales.discount_calculation as discount_calculation',
                'inv_sales.discount_type as discount_type',
                'cor_customers.id as customer_id',
                'cor_customers.name as customer_name',
                'cor_customers.mobile as customer_mobile',
                'createdBy.username as created_by_user_name',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'salesBy.id as sales_by_id',
                'salesBy.username as sales_by_username',
                'salesBy.name as sales_by_name',
                'transactionMode.name as mode_name',
                'inv_sales.transaction_mode_id as transaction_mode_id',
                'inv_sales.process as process_id',
            ])
            ->with(['salesItems' => function ($query) {
                $query->select([
                    'inv_sales_item.id',
                    'inv_sales_item.sale_id',
                    'inv_sales_item.stock_item_id as product_id',
                    'inv_sales_item.uom',
                    'inv_sales_item.name as item_name',
                    'inv_sales_item.quantity',
                    'inv_sales_item.sales_price',
                    'inv_sales_item.purchase_price',
                    'inv_sales_item.price',
                    'inv_sales_item.sub_total',
                ]);
            }])
            ->first();

        return $entity;
    }


    public static function getEditData($id,$domain)
    {
        $entity = self::where([
            ['inv_sales.config_id', '=', $domain['config_id']],
            ['inv_sales.id', '=', $id]
        ])
            ->leftjoin('cor_customers','cor_customers.id','=','inv_sales.customer_id')
            ->leftjoin('users as createdBy','createdBy.id','=','inv_sales.created_by_id')
            ->leftjoin('users as salesBy','salesBy.id','=','inv_sales.sales_by_id')
            ->leftjoin('acc_transaction_mode as transactionMode','transactionMode.id','=','inv_sales.transaction_mode_id')
            ->leftjoin('uti_settings','uti_settings.id','=','inv_sales.process')
            ->select([
                'inv_sales.id',
                DB::raw('DATE_FORMAT(inv_sales.updated_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(inv_sales.updated_at, "%d-%M-%Y") as created_date'),
                'inv_sales.invoice as invoice',
                'inv_sales.sub_total as sub_total',
                'inv_sales.total as total',
                'inv_sales.narration',
                'inv_sales.payment as payment',
                'inv_sales.discount as discount',
                'inv_sales.discount_calculation as discount_calculation',
                'inv_sales.discount_type as discount_type',
                'cor_customers.id as customer_id',
                'cor_customers.name as customer_name',
                'cor_customers.mobile as customer_mobile',
                'createdBy.username as created_by_user_name',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'salesBy.id as sales_by_id',
                'salesBy.username as sales_by_username',
                'salesBy.name as sales_by_name',
                'transactionMode.name as mode_name',
                'inv_sales.transaction_mode_id as transaction_mode_id',
                'inv_sales.process as process_id',
                'uti_settings.name as process_name',
                'cor_customers.address as customer_address',
            ])
            ->with(['salesItems' => function ($query) {
                $query->select([
                    'inv_sales_item.id',
                    'inv_sales_item.sale_id',
                    'inv_sales_item.stock_item_id as product_id',
                    'inv_sales_item.unit_id',
                    'inv_sales_item.name as item_name',
                    'inv_sales_item.name as name',
                    'inv_sales_item.uom as uom',
                    'inv_sales_item.quantity',
                    'inv_sales_item.sales_price',
                    'inv_sales_item.purchase_price',
                    'inv_sales_item.price',
                    'inv_sales_item.sub_total',
                    'inv_sales_item.bonus_quantity',
                    DB::raw("CONCAT(cor_warehouses.name, ' (', cor_warehouses.location, ')') as warehouse_name"),
                    'cor_warehouses.name as warehouse',
                    'cor_warehouses.location as warehouse_location',
                    'cor_warehouses.id as warehouse_id'
                ])->leftjoin('cor_warehouses','cor_warehouses.id','=','inv_sales_item.warehouse_id');
            }])
            ->first();

        return $entity;
    }



}
