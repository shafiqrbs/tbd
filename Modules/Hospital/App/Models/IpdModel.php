<?php

namespace Modules\Hospital\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\Core\App\Models\CustomerModel;


class IpdModel extends Model
{
    use HasFactory;

    protected $table = 'hms_invoice';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [];

    public static function boot() {
        parent::boot();
        self::creating(function ($model) {
            $model->process = 'New';
            $date =  new \DateTime("now");
            $model->created_at = $date;
            $model->process = 'New';
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }


    public static function salesEventListener($model)
    {
        $patientMode = ParticularModeModel::find($model->patinet_mode_id);
        $mode = ($patientMode) ? $patientMode->short_code:'IPD';
        $patternCodeService = app(GeneratePatternCodeService::class);
        $params = [
            'config' => $model->config_id,
            'table' => 'hms_invoice',
            'prefix' => "{$mode}-",
        ];
        return $patternCodeService->invoiceNo($params);
    }

    public static function insertHmsInvoice($domain,$parent,$entity,$data)
    {

        $config = HospitalConfigModel::find($domain['hms_config']);
        $payment_payment_mode_id = $parent->patient_payment_mode_id;

        $invoice = self::salesEventListener($entity)['generateId'];
        $code = self::salesEventListener($entity)['code'];
        $amount = self::insertInvoiceParticular($config,$entity);
        if ($entity) {
            $entity->update([
                'invoice' => $invoice,
                'code' => $code,
                'patient_payment_mode_id' => $payment_payment_mode_id ?? null,
                'free_identification' => $parent->free_identification ?? null,
                'day' => $parent->day ?? null,
                'month' => $parent->month ?? null,
                'year' => $parent->year ?? null,
                'guardian_name' => $parent->guardian_name ?? null,
                'guardian_mobile' => $parent->guardian_mobile ?? null,
                'admit_consultant_id' => $config->consultant_by_id ?? null,
                'process' => 'New',
                'sub_total' => $amount,
                'total' => $amount,
            ]);
            $parent->update([
                'process' => 'Closed',
            ]);
        }
        return $entity->id;
    }

    public static function updateIpdInvoice($id,$data)
    {
        $entity = self::find($id);
        if ($entity) {
            $entity->update([
                'day' => $data['day'] ?? null,
                'month' => $data['month'] ?? null,
                'year' => $data['year'] ?? null,
                'admit_doctor_id' => $data['admit_doctor_id'] ?? null,
                'admit_unit_id' => $data['admit_unit_id'] ?? null,
                'admit_department_id' => $data['admit_department_id'] ?? null,
                'bp' => $data['bp'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
                'guardian_mobile' => $data['guardian_mobile'] ?? null,
                'patient_relation' => $data['patient_relation'] ?? null,
                'height' => $data['height'] ?? null,
                'weight' => $data['weight'] ?? null,
                'comment' => $data['comment'] ?? null,
            ]);
            self::updateIpdPatient($id,$data);
        }
        return $entity;
    }

    public static function updateIpdPatient($id,$data)
    {

        $entity = self::find($id);
        $patient = PatientModel::find($entity->customer_id);
        if ($patient) {
            $patient->update([
                'name' => $data['name'] ?? null,
                'mobile' => $data['mobile'] ?? null,
                'gender' => $data['gender'] ?? null,
                'permanent_address' => $data['permanent_address'] ?? null,
                'address' => $data['address'] ?? null,
                'identity_mode' => $data['identity_mode'] ?? null,
                'nid' => $data['identity'] ?? null,
                'father_name' => $data['father_name'] ?? null,
                'mother_name' => $data['mother_name'] ?? null,
                'religion_id' => $data['religion_id'] ?? null,
                'country_id' => $data['country_id'] ?? null,
                'upazilla_id' => $data['upazilla_id'] ?? null,
                'profession' => $data['profession'] ?? null,
            ]);
        }
    }


    public static function insertInvoiceParticular($config,$entity)
    {
        $admissionFee = ParticularModel::find($config['admission_fee_id']);
        $minimumDaysRoomRent = ($config->minimum_days_room_rent)?$config->minimum_days_room_rent:0;
        $roomRent = ParticularModel::find($entity->room_id);
        InvoiceParticularModel::updateOrCreate(
            [
                'hms_invoice_id' => $entity->id,
                'particular_id'  => $roomRent->id ?? null,
            ],
            [
                'name'  => $roomRent->display_name,
                'quantity'  => $minimumDaysRoomRent,
                'price'     => $roomRent->price,
                'sub_total' => ($roomRent->price * $minimumDaysRoomRent),
            ]
        );

        InvoiceParticularModel::updateOrCreate(
            [
                'hms_invoice_id' => $entity->id,
                'particular_id'  => $admissionFee->id ?? null,
            ],
            [
                'name'  => $admissionFee->display_name,
                'quantity'  => 1,
                'price'     => $admissionFee->price,
                'sub_total' => $admissionFee->price,
            ]
        );

        $amount = InvoiceParticularModel::where('hms_invoice_id', $entity->id)
            ->sum('sub_total');
        return $amount;



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
            ->leftjoin('hms_prescription as prescription','prescription.hms_invoice_id','=','hms_invoice.id')
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
                'hms_invoice.referred_mode as referred_mode',
            ]);

        if (isset($request['term']) && !empty($request['term'])){
            $entities = $entities->whereAny(['inv_sales.invoice','customer.customer_id','customer.name','customer.mobile','createdBy.username','hms_invoice.total'],'LIKE','%'.$request['term'].'%');
        }

        if (isset($request['patient_mode']) && !empty($request['patient_mode'])){
            $entities = $entities->where('patient_mode.slug',$request['patient_mode']);
        }

        if (isset($request['referred_mode']) && !empty($request['referred_mode'])){
            $entities = $entities->where('hms_invoice.referred_mode',$request['referred_mode']);
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
                'hms_invoice.referred_mode as referred_mode',
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

    public static function getIpdShow($id)
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
                'hms_invoice.referred_mode as referred_mode',
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
