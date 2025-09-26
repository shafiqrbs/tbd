<?php

namespace Modules\Hospital\App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\CustomerModel;
use Modules\Inventory\App\Models\SalesModel;


class EpharmaModel extends Model
{
    use HasFactory;

    protected $table = 'hms_invoice';
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

    public function sales()
    {
        return $this->belongsTo(SalesModel::class, 'sales_id');
    }

    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;

        $entities = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->leftjoin('hms_prescription as prescription','prescription.hms_invoice_id','=','hms_invoice.id')
            ->leftjoin('users as doctor','doctor.id','=','prescription.created_by_id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->join('cor_customers as customer','customer.id','=','hms_invoice.customer_id')
            ->select([
                'hms_invoice.id',
                'prescription.created_by_id as prescription_created_by_id',
                'hms_invoice.invoice as invoice',
                'customer.customer_id as patient_id',
                'doctor.name as doctor_name',
                'customer.name',
                'customer.mobile',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d-%m-%Y") as created_at'),
                'hms_invoice.process as process',
                'vr.name as visiting_room',
                'createdBy.name as created_by',
                'hms_invoice.sub_total as total',

            ])->whereHas('invoice_particular', function($query) {
                $query->join('hms_particular','hms_particular.id','=','hms_invoice_particular.particular_id')
                    ->join('hms_particular_type','hms_particular_type.id','=','hms_particular.particular_type_id')
                    ->join('hms_particular_master_type','hms_particular_master_type.id','=','hms_particular_type.particular_master_type_id')
                    ->where('hms_particular_master_type.slug','investigation')
                    ->where('hms_particular.is_available',1);
            });

        if (isset($request['term']) && !empty($request['term'])){
            $term = trim($request['term']);
            $entities = $entities->where(function ($q) use ($term) {
                $q->where('hms_invoice.invoice', 'LIKE', "%{$term}%")
                    ->orWhere('customer.customer_id', 'LIKE', "%{$term}%")
                    ->orWhere('customer.name', 'LIKE', "%{$term}%")
                    ->orWhere('customer.mobile', 'LIKE', "%{$term}%")
                    ->orWhere('customer.nid', 'LIKE', "%{$term}%")
                    ->orWhere('customer.health_id', 'LIKE', "%{$term}%");
            });
        }

        if (isset($request['process']) && !empty($request['process'])){
            $entities = $entities->where('hms_invoice.process',$request['process']);
        }
        if (isset($request['room_id']) && !empty($request['room_id'])){
            $entities = $entities->where('hms_invoice.room_id',$request['room_id']);
        }

        if (isset($request['process']) && !empty($request['process'])){
            $entities = $entities->where('hms_invoice.process',$request['process']);
        }
        if (isset($request['customer_id']) && !empty($request['customer_id'])){
            $entities = $entities->where('hms_invoice.customer_id',$request['customer_id']);
        }
        if (isset($request['created']) && !empty($request['created'])){
            $date = new \DateTime($request['created']);
            $start_date = $date->format('Y-m-d 00:00:00');
            $end_date = $date->format('Y-m-d 23:59:59');
            $entities = $entities->whereBetween('hms_invoice.created_at',[$start_date, $end_date]);
        }

        $total  = $entities->count();
        $entities = $entities->skip($skip)
            ->take($perPage)
            ->orderBy('hms_invoice.updated_at','DESC')
            ->get();
        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }

    public static function getShow($id)
    {
        $entity = self::with([
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
                ]);
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
            ->find($id);

        return $entity;
    }


}
