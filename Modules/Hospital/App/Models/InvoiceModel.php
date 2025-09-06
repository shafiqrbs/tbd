<?php

namespace Modules\Hospital\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\CustomerModel;


class InvoiceModel extends Model
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

     public function invoice_particular()
    {
        return $this->hasMany(InvoiceParticularModel::class, 'hms_invoice_id');
    }

    public function room()
    {
        return $this->hasOne(ParticularModel::class, 'id', 'room_id');
    }
    public function patient_payment_mode()
    {
        return $this->hasOne(OpdModel::class, 'id', 'patient_payment_mode_id');
    }
    public function patient_mode()
    {
        return $this->hasOne(OpdModel::class, 'id', 'patient_mode_id');
    }


    public static function getRecords($request,$domain)
    {
        $page =  isset($request['page']) && $request['page'] > 0?($request['page'] - 1 ) : 0;
        $perPage = isset($request['offset']) && $request['offset']!=''? (int)($request['offset']):50;
        $skip = isset($page) && $page!=''? (int)$page * $perPage:0;
        $entities = self::where([['hms_invoice.config_id',$domain['hms_config']]])
            ->join('inv_sales as inv_sales','inv_sales.id','=','hms_invoice.sales_id')
            ->leftjoin('hms_prescription as prescription','prescription.id','=','hms_invoice.id')
            ->leftjoin('users as doctor','doctor.id','=','prescription.created_by_id')
            ->leftjoin('hms_particular as vr','vr.id','=','hms_invoice.room_id')
            ->leftjoin('users as createdBy','createdBy.id','=','inv_sales.created_by_id')
            ->join('cor_customers as customer','customer.id','=','inv_sales.customer_id')
            ->join('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->join('hms_particular_mode as patient_payment_mode','patient_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                'hms_invoice.id',
                'prescription.id as prescription_id',
                'doctor.name as doctor_name',
                'customer.name',
                'customer.mobile',
                'customer.gender',
                'customer.customer_id as patient_id',
                'customer.health_id',
                DB::raw("CONCAT(UCASE(LEFT(customer.gender, 1)), LCASE(SUBSTRING(customer.gender, 2))) as gender"),
                DB::raw('DATE_FORMAT(hms_invoice.created_at, "%d-%m-%Y") as created_at'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%M-%Y") as appointment'),
                'hms_invoice.process as process',
                'vr.name as visiting_room',
                'inv_sales.invoice as invoice',
                'patient_mode.name as patient_mode_name',
                'patient_payment_mode.name as patient_payment_mode_name',
                'createdBy.name as created_by',
                'hms_invoice.sub_total as total',
            ]);

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
            ->orderBy('hms_invoice.updated_at','DESC')
            ->get();

        $data = array('count'=>$total,'entities'=>$entities);
        return $data;
    }

    public static function getShow($id)
    {
        $entity = self::where([
            ['hms_invoice.id', '=', $id]
        ])
            ->leftjoin('cor_customers','cor_customers.id','=','hms_invoice.customer_id')
            ->leftjoin('inv_sales','inv_sales.id','=','hms_invoice.sales_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->leftjoin('hms_particular as room','room.id','=','hms_invoice.room_id')
            ->leftjoin('hms_particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('hms_particular_mode as particular_payment_mode','particular_payment_mode.id','=','hms_invoice.patient_payment_mode_id')
            ->select([
                'hms_invoice.id',
                DB::raw('DATE_FORMAT(hms_invoice.updated_at, "%d-%m-%y") as created'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment_date, "%d-%m-%y") as appointment'),
                'inv_sales.invoice as invoice',
                'hms_invoice.total as total',
                'hms_invoice.comment',
                'cor_customers.name as name',
                'cor_customers.mobile as mobile',
                'hms_invoice.guardian_name as guardian_name',
                'hms_invoice.guardian_mobile as guardian_mobile',
                'cor_customers.customer_id as patient_id',
                'cor_customers.health_id as health_id',
                'cor_customers.gender as gender',
                'hms_invoice.year as year',
                'hms_invoice.month as month',
                'hms_invoice.day as day',
                DB::raw('DATE_FORMAT(cor_customers.dob, "%d-%m-%y") as dob'),
                'cor_customers.identity_mode as identity_mode',
                'cor_customers.nid as nid',
                'cor_customers.address as address',
                'createdBy.username as created_by_user_name',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'room.name as room_name',
                'patient_mode.name as mode_name',
                'particular_payment_mode.name as payment_mode_name',
                'hms_invoice.process as process',
            ])
            ->with(['invoice_particular' => function ($query) {
                $query->select([
                    'hms_invoice_particular.id',
                    'hms_invoice_particular.hms_invoice_id',
                    'hms_invoice_particular.name as item_name',
                    'hms_invoice_particular.quantity',
                    'hms_invoice_particular.price',
                ]);
            }])
            ->first();

        return $entity;
    }

    public static function getEditData($id,$domain)
    {
        $entity = self::where([
            ['hms_invoice.config_id', '=', $domain['config_id']],
            ['hms_invoice.id', '=', $id]
        ])
            ->leftjoin('cor_customers','cor_customers.id','=','hms_invoice.customer_id')
            ->leftjoin('inv_sales','inv_sales.id','=','hms_invoice.sales_id')
            ->leftjoin('users as createdBy','createdBy.id','=','hms_invoice.created_by_id')
            ->leftjoin('particular as room','room.id','=','hms_invoice.room_id')
            ->leftjoin('particular_mode as patient_mode','patient_mode.id','=','hms_invoice.patient_mode_id')
            ->leftjoin('particular_payment_mode as patient_mode','particular_payment_mode.id','=','hms_invoice.particular_payment_mode_id')
            ->select([
                'hms_invoice.id as id',
                'hms_invoice.id as invoice_id',
                DB::raw('DATE_FORMAT(hms_invoice.updated_at, "%d-%m-%Y") as created'),
                DB::raw('DATE_FORMAT(hms_invoice.appointment, "%d-%M-%Y") as created_date'),
                'inv_sales.invoice as invoice',
                'hms_invoice.total as total',
                'hms_invoice.guardian_name as guardian_name',
                'hms_invoice.guardian_mobile as guardian_mobile',
                'hms_invoice.comment',
                'cor_customers.name as name',
                'cor_customers.mobile as mobile',
                'cor_customers.customer_id as patient_id',
                'cor_customers.health_id as health_id',
                DB::raw('DATE_FORMAT(cor_customers.dob, "%d-%M-%Y") as dob'),
                'cor_customers.identity_mode as identity_mode',
                'cor_customers.address as address',
                'createdBy.username as created_by_user_name',
                'createdBy.name as created_by_name',
                'createdBy.id as created_by_id',
                'hms_invoice.process as process_id',
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

    public static function getVisitingRoomss($domain)
    {
        $entities = self::where([
            ['hms_invoice.config_id', $domain['hms_config']],
            ['hms_particular_master_type.slug', 'visiting-room']])
            ->leftJoin('hms_particular as vr', 'vr.id', '=', 'hms_invoice.room_id')
            ->leftJoin('hms_particular_type', 'hms_particular_type.id', '=', 'vr.particular_type_id')
            ->leftJoin('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->select([
                'vr.id as particular_id',
                'vr.name',
                DB::raw('COUNT(hms_invoice.id) as invoice_count')
            ])
            ->groupBy('vr.id', 'vr.name')
            ->get();

        return $entities;
    }

    public static function getVisitingRooms($domain)
    {
        $entities = ParticularModel::where([
            ['hms_particular.config_id', $domain['hms_config']],
            ['hms_particular_master_type.slug', 'opd-room']])
            ->leftJoin('hms_invoice', 'hms_invoice.room_id', '=', 'hms_particular.id')
            ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->select([
                'hms_particular.id as id',
                'hms_particular.name',
                DB::raw('COUNT(hms_invoice.id) as invoice_count')
            ])
            ->groupBy('hms_particular.id')
            ->get();

        $selected = ParticularModel::where([
            ['hms_particular.config_id', $domain['hms_config']],
            ['hms_particular_master_type.slug', 'opd-room']])
            ->leftJoin('hms_invoice', 'hms_invoice.room_id', '=', 'hms_particular.id')
            ->join('hms_particular_type', 'hms_particular_type.id', '=', 'hms_particular.particular_type_id')
            ->join('hms_particular_master_type', 'hms_particular_master_type.id', '=', 'hms_particular_type.particular_master_type_id')
            ->select([
                'hms_particular.id as id',
            ])
            ->groupBy('hms_particular.id')
            ->orderBy(DB::raw('COUNT(hms_invoice.id)'), 'ASC')
            ->limit(1)
            ->get()->first()->id;
        return array('ipdRooms' => $entities ,'selectedRoom' => $selected);
    }




}
