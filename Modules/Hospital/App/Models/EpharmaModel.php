<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\CustomerModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;


class EpharmaModel extends Model
{
    use HasFactory;

    protected $table = 'inv_sales';
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

    public function customer()
    {
        return $this->hasOne(CustomerModel::class, 'id', 'customer_id');
    }

    public function invoice()
    {
        return $this->hasOne(OpdModel::class, 'id', 'sales_id');
    }

    public function room()
    {
        return $this->hasOne(ParticularModel::class, 'id', 'room_id');
    }



    public function salesItems()
    {
        return $this->hasManyThrough(
            SalesItemModel::class,
            EpharmaModel::class,
            'id',       // Foreign key on SalesModel (inv_sales.id)
            'sale_id',  // Foreign key on SalesItemModel
            'sale_id',  // Local key on PrescriptionModel
            'id'        // Local key on SalesModel
        );
    }


    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;
        $entities = self::where([
            ['hms_invoice.config_id', $domain['hms_config']],
            ['hms_invoice.is_medicine_delivered',1],
            ])
            ->join('hms_prescription as hms_prescription','inv_sales.id','=','hms_prescription.sale_id')
            ->join('hms_invoice','hms_prescription.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->leftjoin('users as createdBy','createdBy.id','=','inv_sales.created_by_id')
            ->leftjoin('users as deliveredBy','deliveredBy.id','=','hms_invoice.medicine_delivered_by_id')
            ->join('cor_customers as customer','customer.id','=','inv_sales.customer_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->join('hms_particular_mode as patient_payment_mode','patient_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                'inv_sales.id as sale_id',
                'hms_prescription.id as prescription_id',
                'hms_invoice.id as id',
                'hms_invoice.uid as uid',
                'hms_invoice.barcode as barcode',
                'hms_invoice.is_medicine_delivered as is_medicine_delivered',
                'customer.name',
                'customer.mobile',
                'customer.gender',
                'customer.customer_id as patient_id',
                'customer.health_id',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d-%m-%Y") as created_at'),
                DB::raw('DATE_FORMAT(hms_invoice.medicine_delivered_date, "%d-%M-%Y") as delivered_date'),
                'hms_invoice.process as process',
                'hms_invoice.medicine_delivered_comment as comment',
                'vr.name as visiting_room',
                'vr.id as visiting_room_id',
                'inv_sales.invoice as invoice',
                'patient_mode.name as patient_mode_name',
                'patient_payment_mode.name as patient_payment_mode_name',
                'createdBy.name as created_by',
                'deliveredBy.name as delivered_by',
                'hms_invoice.sub_total as total',
            ])->with(['salesItems' => function ($query) {
                $query->select([
                    'inv_sales_item.id',
                    'inv_sales_item.name',
                    'inv_sales_item.quantity',
                    'inv_sales_item.price',
                ]);
            }]);

        if (isset($request['term']) && !empty($request['term'])){
            $entities = $entities->whereAny(['hms_invoice.invoice','cor_customers.name','cor_customers.mobile','salesBy.username','createdBy.username','acc_transaction_mode.name','hms_invoice.total'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['customer_id']) && !empty($request['customer_id'])){
            $entities = $entities->where('hms_invoice.customer_id',$request['customer_id']);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && empty($request['end_date'])){
            $start_date = $request['start_date'].' 00:00:00';
            $end_date = $request['start_date'].' 23:59:59';
            $entities = $entities->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }
        if (isset($request['start_date']) && !empty($request['start_date']) && isset($request['end_date']) && !empty($request['end_date'])){
            $start_date = $request['start_date'].' 00:00:00';
            $end_date = $request['end_date'].' 23:59:59';
            $entities = $entities->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }
        $total  = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('hms_prescription.updated_at','DESC')
            ->get();
        $data = array('count'=> $total, 'entities' => $entities);
        return $data;
    }


    public static function getShow($id)
    {
        $entity = InvoiceModel::with([
            'sales.salesItems' => function ($query) {
                $query->select([
                    'inv_sales_item.id',
                    'inv_sales_item.sale_id',
                    'inv_sales_item.stock_item_id as product_id',
                    'inv_sales_item.unit_id',
                    'inv_sales_item.name as name',
                    'inv_sales_item.uom',
                    'inv_sales_item.quantity',
                    'inv_sales_item.sales_price',
                    'inv_sales_item.purchase_price',
                    'inv_sales_item.price',
                    'inv_sales_item.sub_total',
                    'inv_sales_item.bonus_quantity',
                ])->where('inv_sales_item.quantity', '>', 0)
                    ->orderBy('inv_sales_item.name', 'ASC');
            }
        ])
            ->leftJoin('cor_customers','cor_customers.id','=','hms_invoice.customer_id')
            ->leftJoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->leftJoin('inv_sales','inv_sales.id','=','hms_invoice.sales_id')
            ->select([
                'hms_invoice.id',
                'hms_invoice.sales_id',
                'hms_invoice.id as invoice_id',
                DB::raw('DATE_FORMAT(hms_invoice.updated_at, "%d-%m-%Y") as created'),
                'inv_sales.invoice as invoice',
                'hms_invoice.total as total',
                'hms_invoice.guardian_name',
                'hms_invoice.guardian_mobile',
                'hms_invoice.comment',
                'cor_customers.name as name',
                'cor_customers.mobile',
                'cor_customers.customer_id as patient_id',
                'cor_customers.health_id',
                DB::raw('DATE_FORMAT(cor_customers.dob, "%d-%M-%Y") as dob'),
                'cor_customers.identity_mode',
                'cor_customers.address',
                'createdBy.username as created_by_user_name',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'hms_invoice.process as process_id',
            ])
            ->where('hms_invoice.barcode', $id)
            ->where('is_medicine_delivered', '!=', 1)
            ->first();

        return $entity;
    }


}
